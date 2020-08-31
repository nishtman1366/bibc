<?php


namespace App\Controllers;


use App\Models\Area;
use App\Models\Trip;
use Carbon\Carbon;
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
            $this->driver = $_SESSION['user']['driver'];
        }
        $this->company = false;
        if (count($this->user) !== 0 && url()->contains('company')) {
            $this->company = $_SESSION['user']['company'];
        }
    }

    /*
     * نمایش لیست سفرها در داشبورد
     * مدیریت
     */
    public function index($paymentType = null, $driverRequest = null)
    {
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
        $trips = Trip::with('passenger')
            ->with('driver')
            ->with('driver.company')
            ->with('driver.company.area')
            ->with('vehicle')
            ->with('vehicleType')
            ->where(function ($query) use ($paymentType, $driverRequest) {
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
            })
            ->orderBy('iTripId', 'DESC')
            ->get();
        return view('pages.dashboard.payments.trips.list', compact('trips', 'active'));
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
                ->where(function ($query) use ($paymentType, $driverRequest, $fromDate, $toDate) {
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
        return view('pages.frontend.panel.company.payments.index', compact('trips', 'active', 'fromDate', 'toDate'));
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
}