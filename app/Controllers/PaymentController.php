<?php


namespace App\Controllers;


use App\Models\Area;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Morilog\Jalali\Jalalian;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

class PaymentController extends Controller
{
    public $driver = false;
    public $company = false;

    /**
     * VehicleController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->driver = false;
        if (count($this->user) !== 0 && url()->contains('driver')) {
            $this->driver = $this->user['driver'];
        }
        $this->company = false;
        if (count($this->user) !== 0 && url()->contains('company')) {
            $this->company = $this->user['company'];
        }
    }

    /*
     * نمایش لیست سفرها در داشبورد
     * مدیریت
     */
    public function index($paymentStatus = 'unSettled')
    {
        $date = $this->request->query->get('gDate', null);
        $list = [];
        $tripsCount = 0;
        if ($paymentStatus == 'unSettled') {
            $paymentStatus = 'Unsettelled';
            $active = 1;
        } else {
            $paymentStatus = 'Settelled';
            $active = 2;
        }
        if (!is_null($date)) {
//            Manager::connection('default')->enableQueryLog();
            $areas = Area::where('sActive', 'Yes')
                ->orderBy('sAreaNamePersian', 'ASC')
                ->get();
//            dd(Manager::getQueryLog());

            foreach ($areas as $area) {
                $areaName = $area->sAreaNamePersian;
                $companies = [];
                $companyList = Company::where('iAreaId', $area->aId)->get();
                $areaTripsCount = 0;
                foreach ($companyList as $company) {
                    $trips = $this->selectUnsettledTrips($date, $company->iCompanyId, $paymentStatus);
                    $companyTripsCount = count($trips);
                    $areaTripsCount += $companyTripsCount;
                    $tripsCount += $companyTripsCount;
                    if ($companyTripsCount > 0) {
                        $totalFTripGenerateFare = $totalIFare = $totalFCommission = $totalDriverRefund = 0;
                        $totalWalletDebit = $totalAreaCommission = $totalPlatformCommission = 0;
                        $totalDiscount = 0;
                        foreach ($trips as $trip) {
                            $driverRefund = $trip->fTripGenerateFare - $trip->fDiscount - $trip->fCommision;
                            $totalDriverRefund += $driverRefund;
                            $totalFTripGenerateFare += $trip->fTripGenerateFare;
                            $totalIFare += $trip->iFare;
                            $totalWalletDebit += $trip->fWalletDebit;
                            $totalFCommission += $trip->fCommision;
                            $totalAreaCommission += $trip->area_commission;
                            $totalPlatformCommission += $trip->platform_commission;
                            $totalDiscount += $trip->fDiscount;
                        }
                        $companies[] = [
                            'name' => $company->vCompany,
                            'tripsCount' => $companyTripsCount,
                            'totalDriverRefund' => $totalDriverRefund,
                            'totalFTripGenerateFare' => $totalFTripGenerateFare,
                            'totalWalletDebit' => $totalWalletDebit,
                            'totalIFare' => $totalIFare,
                            'totalFCommission' => $totalFCommission,
                            'totalAreaCommission' => $totalAreaCommission,
                            'totalPlatformCommission' => $totalPlatformCommission,
                            'totalDiscount' => $totalDiscount,
                        ];
                    }
                }
                if (count($companies) > 0) {
                    $list[] = [
                        'areaName' => $areaName,
                        'tripsCount' => $areaTripsCount,
                        'companies' => $companies
                    ];
                }
            }
        }
        return view('pages.dashboard.payments.trips.list', compact('list', 'tripsCount', 'date', 'active'));
    }

    public function settlement()
    {
        $date = $this->request->get('date');
        /*
         * دریافت لیست سفرهای تسویه نشده در
         * تاریخ مورد نظر از پایگاه داده
         */
        $trips = $this->selectUnsettledTrips($date);

        $jDate = Jalalian::forge($date)->format('Y/m/d');
        /*
         * محاسبه درآمد راننده با استفاده از
         * اطلاعات سفرهای بدست آمده
         */
        $driverPayments = $this->driversSettlement($trips, $jDate);
        foreach ($driverPayments as $driverPayment) {
            try {
                UsersWalletController::addRecordToUserWallet($driverPayment['id'], 'Driver', $driverPayment['amount'], $driverPayment['paymentType'], 0, 'Withdrawl', $driverPayment['description'], 'Unsettelled', $driverPayment['date']);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
        /*
         * محاسبه درآمد شرکتها با استفاده از
         * اطلاعات سفرهای بدست آمده
         */
        $companyPayments = $this->companiesSettlement($trips, $jDate);
        foreach ($companyPayments as $companyPayment) {
            try {
                UsersWalletController::addRecordToUserWallet($companyPayment['id'], 'Company', $companyPayment['amount'], $companyPayment['paymentType'], 0, 'Withdrawl', $companyPayment['description'], 'Unsettelled', $companyPayment['date']);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
        /*
         * محاسبه درآمد ناحیه ها با استفاده از
         * اطلاعات سفرهای بدست آمده
         */
        $areaPayments = $this->areasSettlement($trips, $jDate);
        foreach ($areaPayments as $areaPayment) {
            try {
                UsersWalletController::addRecordToUserWallet($areaPayment['id'], 'Area', $areaPayment['amount'], $areaPayment['paymentType'], 0, 'Withdrawl', $areaPayment['description'], 'Unsettelled', $areaPayment['date']);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }

        dd([$driverPayments, $companyPayments, $areaPayments]);
    }

    public function driversSettlement($trips, $jDate)
    {
        $drivers = collect([]);
        foreach ($trips as $trip) {
            $driverId = $trip->iDriverId;
            $driverAmount = $trip->fWalletDebit + $trip->fDiscount - $trip->fCommision;
            $driver = $drivers->where('id', $trip->iDriverId)->first();
            if (is_null($driver)) {
                $drivers->push([
                    'id' => $trip->iDriverId,
                    'amount' => $driverAmount
                ]);
            } else {
                $drivers->transform(function ($driver) use ($driverId, $driverAmount) {
                    if ($driver['id'] == $driverId) {
                        $driver['amount'] = $driver['amount'] + $driverAmount;
                    }
                    return $driver;
                });
            }
        }

        $date = Date('Y-m-d H:i:s');

        $driverPayments = [];
        foreach ($drivers as $driver) {
            $driverAmount = $driver['amount'];
            if ($driverAmount != 0) {
                if ($driverAmount < 0) {
                    $paymentType = 'Debit';
                } else {
                    $paymentType = 'Credit';
                }
                $driverAmount = abs($driverAmount);
                $description = sprintf('تسویه سفرهای انجام شده در تاریخ %s', $jDate);
                $driverPayments[] = [
                    'id' => $driver['id'],
                    'amount' => $driverAmount,
                    'paymentType' => $paymentType,
                    'description' => $description,
                    'date' => $date
                ];
            }
        }

        return $driverPayments;
    }

    public function companiesSettlement($trips, $jDate)
    {
        $companies = collect([]);
        foreach ($trips as $trip) {
            $companyId = $trip->iCompanyId;
            $companyAmount = $trip->fCommision - $trip->area_commission - $trip->platform_commission;
            $company = $companies->where('id', $trip->iCompanyId)->first();
            if (is_null($company)) {
                $companies->push([
                    'id' => $trip->iCompanyId,
                    'amount' => $companyAmount
                ]);
            } else {
                $companies->transform(function ($company) use ($companyId, $companyAmount) {
                    if ($company['id'] == $companyId) {
                        $company['amount'] = $company['amount'] + $companyAmount;
                    }
                    return $company;
                });
            }
        }

        $date = Date('Y-m-d H:i:s');


        $companyPayments = [];
        foreach ($companies as $company) {
            $companyAmount = $company['amount'];
            if ($companyAmount != 0) {
                if ($companyAmount < 0) {
                    $paymentType = 'Debit';
                } else {
                    $paymentType = 'Credit';
                }
                $companyAmount = abs($companyAmount);
                $description = sprintf('تسویه کمیسیون سفرهای انجام شده در تاریخ %s', $jDate);
                $companyPayments[] = [
                    'id' => $company['id'],
                    'amount' => $companyAmount,
                    'paymentType' => $paymentType,
                    'description' => $description,
                    'date' => $date
                ];
            }
        }

        return $companyPayments;
    }

    public function areasSettlement($trips, $jDate)
    {
        $areas = collect([]);
        foreach ($trips as $trip) {
            $areaId = $trip->iAreaId;
            $areaAmount = $trip->area_commission - $trip->fDiscount;
            $area = $areas->where('id', $areaId)->first();
            if (is_null($area)) {
                $areas->push([
                    'id' => $areaId,
                    'amount' => $areaAmount
                ]);
            } else {
                $areas->transform(function ($area) use ($areaId, $areaAmount) {
                    if ($area['id'] == $areaId) {
                        $area['amount'] = $area['amount'] + $areaAmount;
                    }
                    return $area;
                });
            }
        }

        $date = Date('Y-m-d H:i:s');

        $areaPayments = [];
        foreach ($areas as $area) {
            $areaAmount = $area['amount'];
            if ($areaAmount != 0) {
                if ($areaAmount < 0) {
                    $paymentType = 'Debit';
                } else {
                    $paymentType = 'Credit';
                }
                $areaAmount = abs($areaAmount);
                $description = sprintf('تسویه کمیسیون و تخفیف سفرهای انجام شده در تاریخ %s', $jDate);
                $areaPayments[] = [
                    'id' => $area['id'],
                    'amount' => $areaAmount,
                    'paymentType' => $paymentType,
                    'description' => $description,
                    'date' => $date
                ];
            }
        }

        return $areaPayments;
    }

    public function selectUnsettledTrips($date, $companyId = null, $paymentStatus = 'Unsettelled')
    {
        return Trip::where('eDriverPaymentStatus', $paymentStatus)
            ->where('iActive', 'Finished')
            ->where(function ($query) use ($date, $companyId) {
                if (!is_null($companyId)) {
                    $query->where('iCompanyId', $companyId);
                }
                if (!is_null($date)) {
                    $fromDate = Carbon::createFromDate($date)->hour(0)->minute(0)->second(0)->format('Y-m-d H:i:s');
                    $toDate = Carbon::createFromDate($date)->hour(23)->minute(59)->second(59)->format('Y-m-d H:i:s');
                    $query->where('tEndDate', '>=', $fromDate)
                        ->where('tEndDate', '<', $toDate);
                }
            })
            ->orderBy('iTripId', 'DESC')
            ->get();
    }

    public function indexForCompanies($paymentType = null, $driverRequest = null)
    {
        $company = $this->company;
        if (!$company) throw new NotFoundHttpException('اطلاعات ورود یافت نشد.');
        $companyId = $company->iCompanyId;
        $active = null;
        if (!is_null($paymentType) && !is_null($driverRequest)) {
            if ($paymentType === 'all' && $driverRequest === 'all') {
                $active = 'allRecords';
            } elseif ($paymentType === 'unSettled' && $driverRequest === 'all') {
                $active = 'allUnsettledRecords';
            } elseif ($paymentType === 'unSettled' && $driverRequest === 'requested') {
                $active = 'requestedUnsettledRecords';
            } elseif ($paymentType === 'settled' && $driverRequest === 'all') {
                $active = 'allSettledRecords';
            }
        }
        $trips = [];
        $fromDate = $this->request->query->get('gFromDate', null);
        $toDate = $this->request->query->get('gToDate', null);

        $driverId = $this->request->query->get('driverId', null);
        if (!is_null($fromDate) && !is_null($toDate)) {
            $trips = Trip::with('passenger')
                ->with('driver')
                ->with('driver.company')
                ->with('driver.company.area')
                ->with('vehicle')
                ->with('vehicleType')
                ->wherehas('driver', function ($query) use ($companyId) {
                    $query->where('iCompanyId', $companyId);
                })
                ->where(function ($query) use ($paymentType, $driverRequest, $fromDate, $toDate, $driverId) {
                    if (!is_null($paymentType)) {
                        if ($paymentType === 'unSettled') {
                            $query->where('eDriverPaymentStatus', 'Unsettelled');
                        } elseif ($paymentType === 'settled') {
                            $query->where('eDriverPaymentStatus', 'Settelled');
                        }
                    }
                    if (!is_null($driverRequest)) {
                        if ($driverRequest === 'requested') {
                            $query->where('ePayment_request', 'Yes');
                        }
                    }
                    if (!is_null($driverId)) {
                        $query->where('iDriverId', $driverId);
                    }
                    if (!is_null($fromDate)) {
                        $fromDate = Carbon::createFromDate($fromDate)->hour(0)->minute(0)->second(0)->format('Y-m-d H:i:s');
                        $query->where('tEndDate', '>=', $fromDate);
                    }
                    if (!is_null($toDate)) {
                        $toDate = Carbon::createFromDate($toDate)->hour(23)->minute(59)->second(59)->format('Y-m-d H:i:s');
                        $query->where('tEndDate', '<=', $toDate);
                    }
                })
                ->where('iActive', 'Finished')
                ->orderBy('iTripId', 'DESC')
                ->get();
        }
        $drivers = Driver::where('iCompanyId', $company->iCompanyId)->orderBy('vLastName', 'ASC')->get();
        $drivers->each(function ($driver) {
            $tripUnsettledAmount = TripController::getUserCreditForUnsettledTrips($driver->iDriverId, 'driver');
            $userWalletAmount = UsersWalletController::getUserWalletAmount($driver->iDriverId, 'driver');
            $driver->credit = $tripUnsettledAmount + $userWalletAmount;
        });
        return view('pages.frontend.panel.company.payments.index', compact('trips', 'active', 'fromDate', 'toDate', 'driverId', 'drivers'));
    }

    /*
     * نمایش لیست سفرها در پنل کاربری برای راننده
     */
    public function indexForDrivers($paymentType = 'notRequested')
    {
        $driver = $this->driver;
        $driverId = $driver['iDriverId'];
        if ($driver) {
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
                ->with('driver.company.area')
                ->with('vehicle')
                ->with('vehicleType')
                ->where(function ($query) use ($driverId, $paymentType) {
                    $query->where('iDriverId', $driverId);
                    if ($paymentType === 'notRequested') {
                        $query->where('ePayment_request', 'No')
                            ->where('eDriverPaymentStatus', 'Unsettelled');
                    } elseif ($paymentType === 'notSettled') {
                        $query->where('ePayment_request', 'Yes')
                            ->where('eDriverPaymentStatus', 'Unsettelled');
                    } elseif ($paymentType === 'settled') {
                        $query->where('eDriverPaymentStatus', 'Settelled');
                    }
                })
                ->orderBy('iTripId', 'DESC')
                ->get();
            return view('pages.frontend.panel.driver.payments.list', compact('trips', 'active'));
        }
    }

    /*
     * تسویه حساب با رانندگان توسط شرکت
     * این بخش شامل دریافت اطلاعات سفرهای انتخاب شده توسط شرکت می باشد.
     * سپس اطلاعات جمع آوری شده به تفکیک شیوه پرداخت دسته بندی می شوند.
     * اگر پرداخت هزینه سفر به صورت نقدی باشد مبلغ کمیسیون به صورت عدد منفی محاسبه می شود.
     * اگر پرداخت سفر از کیف پول مسافر صورت گرفته باشد مبلغ صفر منهای کمیسیون به صورت عدد مثبت محاسبه می شود.
     * در نهایت جمع همه اعداد به دست آمده به صورت یک رکود طلبکاری (credit) در کیف پول راننده ثبت می شود.
     */
    public function tripsSettlement()
    {
        $apiToken = $this->request->header('company-api-token');
        try {
            $this->authenticateApiUser($apiToken, 'company');
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
        $tripIds = $this->request->get('trips');
        $drivers = [];
        $trips = Trip::whereIn('iTripId', $tripIds)
            ->groupBy('iDriverId')
            ->get();
        foreach ($trips as $trip) {
            $drivers[] = $trip->iDriverId;
        }

        try {
            $this->settlements($drivers);
        } catch (\Exception $e) {
            return response()->httpCode(500)->json(['message' => $e->getMessage()]);
        }
        return response()->json([]);
    }

    /**
     * @param array $drivers
     * @throws \Exception
     */
    public function settlements(array $drivers)
    {
        foreach ($drivers as $driver) {
            $driverTrips = Trip::where('iDriverId', $driver)
                ->where('eDriverPaymentStatus', 'Unsettelled')
                ->get();
            $commission = 0;
            $cardPayment = 0;
            foreach ($driverTrips as $trip) {
                if ($trip->fWalletDebit == 0) {
                    $commission += -($trip->fCommision);
                } else {
                    $cardPayment += $trip->fWalletDebit;
                }
                $trip->update([
                    'eDriverPaymentStatus' => 'Settelled',
                    'ePayment_request' => 'Yes'
                ]);
            }
            $amount = $cardPayment + $commission;
            if ($amount < 0) {
                $paymentType = 'Debit';
            } else {
                $paymentType = 'Credit';
            }
            $amount = abs($amount);
            $description = sprintf('تسویه سفرهای انجام شده در تاریخ %s و ساعت %s', Jalalian::now()->format('Y/m/d'), Date('H:i:s'));
            $date = Date('Y-m-d H:i:s');
            try {
                UsersWalletController::addRecordToUserWallet($driver, 'Driver', $amount, $paymentType, 0, 'Withdrawl', $description, 'Unsettelled', $date);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /*
     * ثبت درخواست راننده برای واریز وجه
     */
    public function requestPayment()
    {
        $apiToken = $this->request->header('driver-api-token');
        try {
            $driver = $this->authenticateApiUser($apiToken, 'driver');
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => 'اطلاعات ورود ناقص است']);
        }
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

    public function updateTripsTable()
    {
        $i = 0;
        for ($j = 12000; $j < 26000; $j += 100) {
            $trips = Trip::with('driver')
                ->orderBy('iTripId', 'ASC')
                ->offset($j)
                ->limit($j + 100)
                ->get();
            foreach ($trips as $trip) {
                if (!is_null($trip->driver)) {
                    $trip->update(['iCompanyId' => $trip->driver->iCompanyId]);
                    $i++;
                }
            }
            echo $j . ' trip updated<br>';
        }

        echo $i . ' Trip updated in total';
    }
}