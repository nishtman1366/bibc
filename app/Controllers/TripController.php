<?php


namespace App\Controllers;


use App\Models\Trip;

class TripController extends Controller
{
    public $driver = false;

    /**
     * VehicleController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->driver = false;
        if (count($this->user) !== 0 && url()->contains('driver')) {
            $this->driver = $_SESSION['user']['driver'];
        }
    }

    public function index()
    {
        $driver = $this->driver;
        $trips = Trip::with('passenger')
            ->with('driver')
            ->with('driver.company')
            ->with('vehicle')
            ->with('vehicleType')
            ->where(function ($query) use ($driver) {
                if ($driver) {
                    $query->where('iDriverId', $driver['iDriverId']);
                }
            })
            ->orderBy('iTripId', 'DESC')
            ->get();
        if ($driver) {
            return view('pages.frontend.panel.driver.trips.list', compact('trips'));
        }
        return view('pages.trips.list', compact('trips'));
    }

    public static function getUserCreditForUnsettledTrips(int $userId, string $userType)
    {
        if ($userType === 'driver') {
            $fieldName = 'iDriverId';
            $userType = 'Driver';
        } elseif ($userType === 'passenger') {
            $fieldName = 'iUserId';
            $userType = 'Rider';
        }
        $tripsDebitsAmount = Trip::where($fieldName, $userId)->where('eDriverPaymentStatus', 'Unsettelled')->sum('fWalletDebit');
        $tripsDiscountsAmount = Trip::where($fieldName, $userId)->where('eDriverPaymentStatus', 'Unsettelled')->sum('fDiscount');
        $tripsCommissionsAmount = Trip::where($fieldName, $userId)->where('eDriverPaymentStatus', 'Unsettelled')->sum('fCommision');

        return $tripsDebitsAmount + $tripsDiscountsAmount - $tripsCommissionsAmount;
    }
}