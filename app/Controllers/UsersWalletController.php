<?php


namespace App\Controllers;


use App\Models\Area;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Passenger;
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

    public function index($userType = 'area')
    {
        switch ($userType) {
            case 'area':
                $walletType = 'Area';
                $users = Area::orderBy('aId', 'ASC')->get();
                $active = 3;
                break;
            case 'company':
                $walletType = 'Company';
                $users = Company::orderBy('iCompanyId', 'ASC')->get();
                $active = 4;
                break;
            case 'driver':
                $walletType = 'Driver';
                $users = Driver::orderBy('iDriverId', 'ASC')->get();
                $active = 5;
                break;
            case 'passenger':
                $walletType = 'Rider';
                $users = Passenger::orderBy('iUserId', 'ASC')->get();
                $active = 6;
                break;
        }
        $list = collect([]);
        foreach ($users as $user) {
            $walletAmount = $this->getUserWalletAmount($user->userId, $userType);
            $list->push([
                'id' => $user->userId,
                'name' => $user->fullName,
                'amount' => $walletAmount,
                'type' => $userType,
            ]);
        }
        $list = $list->sortByDesc('amount');

        return view('pages.dashboard.payments.wallets.list', compact('list', 'active'));
    }

    public function viewUserWallet($userType, $userId)
    {
        switch ($userType) {
            case 'area':
                $walletType = 'Area';
                $user = Area::where('aId', $userId)->orderBy('aId', 'ASC')->get()->first();
                $active = 3;
                break;
            case 'company':
                $walletType = 'Company';
                $user = Company::where('iCompanyId', $userId)->orderBy('iCompanyId', 'ASC')->get()->first();
                $active = 4;
                break;
            case 'driver':
                $walletType = 'Driver';
                $user = Driver::where('iDriverId', $userId)->orderBy('iDriverId', 'ASC')->get()->first();
                $active = 5;
                break;
            case 'passenger':
                $walletType = 'Rider';
                $user = Passenger::where('iUserId', $userId)->orderBy('iUserId', 'ASC')->get()->first();
                $active = 6;
                break;
        }
        $wallets = UsersWallet::where('iUserId', $userId)->where('eUserType', $walletType)->get();
        $prevalence = 0;
        foreach ($wallets as $wallet) {
            if ($wallet->eType == "Credit") {
                $wallet->balance = $prevalence + $wallet->iBalance;
            } else {
                $wallet->balance = $prevalence - $wallet->iBalance;
            }
            $prevalence = $wallet->balance;
        }

        return view('pages.dashboard.payments.wallets.view', compact('wallets', 'user', 'prevalence', 'active'));
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

    public function indexForDrivers()
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

    public static function getUserWalletAmount(int $userId, string $userType)
    {
        if ($userType === 'area') {
            $userType = 'Area';
        } elseif ($userType === 'company') {
            $userType = 'Company';
        } elseif ($userType === 'driver') {
            $userType = 'Driver';
        } elseif ($userType === 'passenger') {
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