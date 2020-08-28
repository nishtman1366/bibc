<?php


namespace App\Controllers;


use App\Models\Area;
use App\Models\AuthenticationsToken;
use App\Models\Driver;
use App\Models\Trip;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

class PaymentController extends Controller
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

    public function index($paymentType = 'notRequested')
    {
        $driver = $this->driver;
        if ($paymentType === 'notRequested') {
            $active = 'notRequested';
        } elseif ($paymentType === 'notSettled') {
            $active = 'notSettled';
        } elseif ($paymentType === 'settled') {
            $active = 'settled';
        }
        $trips = Trip::with('passenger')
            ->with('driver')
            ->with('driver.company')
            ->with('vehicle')
            ->with('vehicleType')
            ->where(function ($query) use ($driver, $paymentType) {
                if ($driver) {
                    $query->where('iDriverId', $driver['iDriverId']);
                }
                if ($paymentType === 'notRequested') {
                    $query->where('ePayment_request', 'No');
                } elseif ($paymentType === 'notSettled') {
                    $query->where('ePayment_request', 'Yes')
                        ->where('eDriverPaymentStatus', 'Unsettelled');
                } elseif ($paymentType === 'settled') {
                    $query->where('ePayment_request', 'Yes')
                        ->where('eDriverPaymentStatus', 'Settelled');
                }
            })
            ->orderBy('iTripId', 'DESC')
            ->get();
        if ($driver) {
            return view('pages.frontend.panel.driver.payments.list', compact('trips', 'active'));
        }
        return view('pages.payments.list', compact('trips'));
    }

    public function requestPayment()
    {
        $apiToken = $this->request->header('driver-api-token');
        if (is_null($apiToken)) throw new NotFoundHttpException('اطلاعات ورود ناقص است');
        $authenticatedUser = AuthenticationsToken::where('token', $apiToken)->get()->first();
        if (is_null($authenticatedUser)) throw new NotFoundHttpException('توکن امنیتی اشتباه است');
        $driver = Driver::with('company')
//            ->with('company.area')
            ->where('iDriverId', $authenticatedUser->user_id)
            ->get()
            ->first();
        if (is_null($driver)) throw new NotFoundHttpException('اطلاعات ورود اشتباه است');

        $trips = $this->request->get('trips');
        $area = Area::where('aId', $driver->company->iAreaId)->get()->first();
        if (is_null($area)) {
            $minimumAmountForDriver = 2000;
        } else {
            $minimumAmountForDriver = $area->priceDetails->minimumAmountForDriver;
        }
        $totalFare = 0;
        $totalCommission = 0;
        $totalPayment = 0;
        $tripsIdList = [];
        foreach ($trips as $item) {
            $trip = Trip::where('iTripId', $item['tripId'])->get()->first();
            $totalFare += $trip->iFare;
            $totalCommission += $trip->fCommision;
            $totalPayment += $trip->iFare - $trip->fCommision;
            $tripsIdList[] = $trip->iTripId;
        }

        if ($totalPayment >= $minimumAmountForDriver) {
            Trip::whereIn('iTripId', $tripsIdList)->update(['ePayment_request' => 'Yes']);
        } else {
            return response()->httpCode(403)->json([
                'message' => 'جمع مبلغ انتخاب شده از حداقل مورد نیاز کمتر است'
            ]);
        }

        return response()->json(['message' => 'درخواست شما با موفقیت انجام شد.']);

    }
}