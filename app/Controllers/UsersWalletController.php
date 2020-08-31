<?php


namespace App\Controllers;


use App\Models\Trip;
use App\Models\UsersWallet;

class UsersWalletController extends Controller
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

    /**
     * @param int $userId
     * @param string $userType
     * @param int $amount
     * @param string $paymentType
     * @param int $tripId
     * @param string $eFor
     * @param string $description
     * @param string $status
     * @param string $date
     * @throws \Exception
     */
    public static function addRecordToUserWallet(int $userId, string $userType, int $amount, string $paymentType, int $tripId, string $eFor, string $description, string $status, string $date)
    {
        try {
            UsersWallet::create([
                'iUserId' => $userId,
                'referenceId' => null,
                'eUserType' => $userType,
                'iBalance' => $amount,
                'eType' => $paymentType,
                'iTripId' => $tripId,
                'eFor' => $eFor,
                'tDescription' => $description,
                'ePaymentStatus' => $status,
                'dDate' => $date,
            ]);
        } catch (\Exception $exception) {
            throw new \Exception();
        }
    }

    public function index()
    {
        $driver = $this->driver;
        $walletAmount = $this->getUserWalletAmount($this->driver->iDriverId, 'driver');
        $driverCreditForUnsettledTrips = TripController::getUserCreditForUnsettledTrips($driver->iDriverId, 'driver');
        $wallets = UsersWallet::where('iUserId', $driver->iDriverId)->where('eUserType', 'Driver')->get();
        $prevalence = 0;
        foreach ($wallets as $wallet) {
            if ($wallet->eType == "Credit") {
                $wallet->balance = $prevalence + $wallet->iBalance;
            } else {
                $wallet->balance = $prevalence - $wallet->iBalance;
            }
            $prevalence = $wallet->balance;
        }
        return view('pages.frontend.panel.driver.usersWallet.list', compact('walletAmount', 'driverCreditForUnsettledTrips', 'wallets'));
    }

    public function getUserWalletAmount(int $userId, string $userType)
    {
        if ($userType === 'driver') {
            $fieldName = 'iDriverId';
            $userType = 'Driver';
        } elseif ($userType === 'passenger') {
            $fieldName = 'iUserId';
            $userType = 'Rider';
        }

        $walletDebitsAmount = UsersWallet::where('iUserId', $userId)->where('eUserType', $userType)->where('eType', 'Debit')->sum('iBalance');
        $walletCreditsAmount = UsersWallet::where('iUserId', $userId)->where('eUserType', $userType)->where('eType', 'Credit')->sum('iBalance');
        return ($walletCreditsAmount - $walletDebitsAmount);
    }

    public function addUserWalletAmount()
    {

    }
}