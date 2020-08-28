<?php


namespace App\Controllers;


use App\Models\Area;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Currency;
use App\Models\VehicleType;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    const BOOKING_PENDING_STATUS = 'Pending';//default
    const BOOKING_CANCEL_STATUS = 'cancel';  //if user want to cancel
    const BOOKING_ASSIGN_STATUS = 'Assign';  //driver select ride
    const BOOKING_FAILED_STATUS = 'Failed';  //no one available/accept

    public function index()
    {
        $bookings = Booking::with('passenger')
            ->with('company')
            ->with('driver')
            ->with('vehicleType')
            ->with('trip')
            ->orderBy('iCabBookingId', 'DESC')
            ->get();

        return view('pages.bookings.list', compact('bookings'));
    }

    public function form()
    {
        $booking = null;
        $aAreaId = null;
        if (key_exists('aAreaId', $_GET)) {
            $aAreaId = $_GET['aAreaId'];
        }
        $areas = Area::orderBy('aId', 'ASC')->get();
        $vehicleTypes = VehicleType::orderBy('iVehicleTypeId', 'asc')->get();
        $companies = Company::orderBy('iCompanyId', 'ASC')->get();
        return view('pages.bookings.form', compact('booking', 'areas', 'aAreaId', 'vehicleTypes', 'companies'));
    }

    public function create()
    {
        $input = input()->all();
        $input['vBookingNo'] = $this->createBookingNo();
        $input['eStatus'] = $this::BOOKING_ASSIGN_STATUS;
        $input['eCancelBy'] = '';
        $booking = Booking::create($input);

        //TODO send sms|notification to passenger

        //TODO send email to driver && passenger

        //TODO send sms|notification to driver
        return redirect(url('bookings'));
    }

    public function getBookingByUserId(int $userId)
    {
        $booking = Booking::where('iUserId', $userId)
            ->orderBy('dBooking_date', 'DESC')
            ->get()
            ->first();
        if (is_null($booking)) return response()->httpCode(404)->json([]);
        return response()->json($booking);
    }

    public function calculateDistanceAndFare()
    {
        $mapIrApiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5In0.eyJhdWQiOiIxMDIxMyIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5IiwiaWF0IjoxNTk1NjYxMTc0LCJuYmYiOjE1OTU2NjExNzQsImV4cCI6MTU5ODI1MzE3NCwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.s0WWrZ2u-B4R9QaI5fxCozQWCP5QFQScUU2bhIt017_Bbpg0ZrnvA_4Ze1ebbSrVAdbGezuyf37ny3ux7Sg4ZO5rttA4EoF1VndtkVsR-br2G7p7FYMKb_e5adZwDrKos8n2mtS6Cytg3ebphaOSUy1GBNS-8rXSU3CsuUgC9AxXsjAIySS5APVoJaBQ9aj3tfO83rY0f1Is34D4emtC39bpw8ZGuj5U9yp4gQrbh7AgWqf217OFE3Od85n8Q8fOEvSN-XrFxE_Rr_dxTes8okfdsnUEiJ-Ha7LIO7lH4efHX1J6SiuwlOrfjasZRexNa1s3Jll3rS-MOP-oOHXKFQ';
        $input = input()->all();
        $client = new \GuzzleHttp\Client();
        $origin = $input['origin'];
        $destination = $input['destination'];
        $fromCoordinates = $origin['lng'] . ',' . $origin['lat'];
        $toCoordinates = $destination['lng'] . ',' . $destination['lat'];
        $vehicleTypeId = $input['vehicleTypeId'];
        /*
         * ارسال اطلاعات برای اراپه دهنده خدمات نقشه
         */
        try {
            $response = $client->get('https://map.ir/routes/route/v1/driving/' . $fromCoordinates . ';' . $toCoordinates . '?alternatives=true&steps=true',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'x-api-key' => $mapIrApiKey
                    ]
                ]);
            /*
             * محاسبه قیمت بوسیله اطلاعات بدست آمده
             */
            $responseData = json_decode($response->getBody(), true);
            $tripDistance = round($responseData['routes'][0]['distance'] / 1000, 2);
            $tripDuration = round($responseData['routes'][0]['duration'] / 60, 2);
            $priceRatio = 1;
            $defaultCurrency = Currency::where('eDefault', 'Yes')->get()->first();
            if (!is_null($defaultCurrency)) {
                $priceRatio = $defaultCurrency->Ratio;
            }
            $fareData = FeeController::calculateFareEstimate('false', 'false', 0, $tripDuration, $tripDistance, $vehicleTypeId, 1, $priceRatio, $defaultCurrency);

            $fareData->tripDistance = $tripDistance;
            $fareData->tripDuration = $tripDuration;
            $fareData->total_fare = intval($fareData->total_fare);

            return response()->json($fareData);
        } catch (GuzzleException $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    private function createBookingNo()
    {
        $code = Str::uuid()->toString();
        $codeExistence = Booking::where('vBookingNo', $code)->exists();
        if ($codeExistence) return $this->createBookingNo();
        return $code;
    }
}