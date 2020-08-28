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

    public function index()
    {
        $driver = $this->driver;
        $walletAmount = $this->getUserWalletAmount($this->driver->iDriverId, 'driver');

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
        return view('pages.frontend.panel.driver.usersWallet.list', compact('walletAmount', 'wallets'));
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

        $tripsDebitsAmount = Trip::where($fieldName, $userId)->where('eDriverPaymentStatus', 'Unsettelled')->sum('fWalletDebit');
        $tripsDiscountsAmount = Trip::where($fieldName, $userId)->where('eDriverPaymentStatus', 'Unsettelled')->sum('fDiscount');
        $tripsCommissionsAmount = Trip::where($fieldName, $userId)->where('eDriverPaymentStatus', 'Unsettelled')->sum('fCommision');

        $walletDebitsAmount = UsersWallet::where('iUserId', $userId)->where('eUserType', $userType)->where('eType', 'Debit')->sum('iBalance');
        $walletCreditsAmount = UsersWallet::where('iUserId', $userId)->where('eUserType', $userType)->where('eType', 'Credit')->sum('iBalance');
        return ($tripsDebitsAmount + $tripsDiscountsAmount - $tripsCommissionsAmount) + ($walletCreditsAmount - $walletDebitsAmount);
    }
}