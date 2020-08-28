<?php


namespace App\Controllers;


use App\Models\FeeSetting;
use App\Models\VehicleType;

class FeeController extends Controller
{
    public function index()
    {
        $items = FeeSetting::orderBy('id', 'ASC')->get();

        return view('pages.feeSettings.index', compact('items'));
    }

    public function update()
    {
        $items = input()->all();
        $items = $items['settings'];
        foreach ($items as $id => $value) {
            FeeSetting::find($id)->update(['setting_value' => $value]);
        }
        return redirect(url('feeSettings'));
    }

    public static function calculateFareEstimate($hasSecDst, $hasReturn, $delayId, $totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $defaultCurrency, $startDate = "", $endDate = "", $surgePrice = 1)
    {
        $fareData = VehicleType::where('iVehicleTypeId', $vehicleTypeID)->get()->first();
        $ePriceZoon = $fareData->ePriceZone;

        $priceZoneArray = @unserialize($fareData->tPriceZoneSerialize);
        if ($priceZoneArray === false)
            $priceZoneArray = [];
        else {
            $priceZoneArray = array_reverse($priceZoneArray);
        }

        $priceZoneRatio = 1;

        if ($ePriceZoon == "Active" && count($priceZoneArray) > 0) {
            foreach ($priceZoneArray as $zone) {
                if (isset($zone['zoneDistance']) == false)
                    continue;

                if ($tripDistance > $zone['zoneDistance']) {
                    $priceZoneRatio *= $zone['zoneSurcharge'];
                    break;
                }
            }
        }

        if ($surgePrice > 1) {
            $fareData->iBaseFare = $fareData->iBaseFare * $surgePrice;
            $fareData->fPricePerMin = $fareData->fPricePerMin * $surgePrice;
            $fareData->fPricePerKM = $fareData->fPricePerKM * $surgePrice;
            $fareData->iMinFare = $fareData->iMinFare * $surgePrice;
        }

        if ($priceZoneRatio > 0) {
            $fareData->fPricePerKM = $fareData->fPricePerKM * $priceZoneRatio;
        }

        if ($fareData->eFareType == 'Fixed') {
            $fareData->iBaseFare = $fareData->fFixedFare;
            $fareData->fPricePerMin = 0;
            $fareData->fPricePerKM = 0;
        }

        $finalFare = static::calculateFinalFare($fareData->iBaseFare, $fareData->fPricePerMin, $totalTimeInMinutes_trip, $fareData->fPricePerKM, $tripDistance, $fareData->fCommision, $priceRatio, $defaultCurrency, $startDate, $endDate);

        $finalFare['FinalFare'] = $finalFare['FinalFare'] - $finalFare['FareOfCommision']; // Temporary set: Remove addition of commision from above function

        $fareData->total_fare = $finalFare['FinalFare'];

        if ($fareData->iMinFare > $fareData->total_fare) {
            $fareData->MinFareDiff = $fareData->iMinFare - $fareData->total_fare;
            $fareData->total_fare = $fareData->iMinFare;
        } else {
            $fareData->MinFareDiff = "0";
        }

        if ($fareData->eFareType == 'Fixed') {
            $fareData->iBaseFare = 0;
        } else {
            $fareData->iBaseFare = $finalFare['iBaseFare'];
        }

        $total_fare = $fareData->total_fare;

        $feeSettings = FeeSetting::orderBy('id', 'ASC')->get();
        $returnRate = $feeSettings->where('id', 1)->first()->setting_value;
        $secRate = $feeSettings->where('id', 1)->first()->setting_value;
        if ($hasSecDst == 'true') {
            $total_fare = $total_fare * $secRate;
        }
        if ($hasReturn == 'true') {
            $total_fare = $total_fare * $returnRate;
        }
        if ($delayId > 0) {
            $total_fare = $total_fare + ($feeSettings->where('id', $delayId)->first()->setting_value);
        }

        $kasrePansadToman = static::roundOfForPassenger($total_fare);
        if ($kasrePansadToman != 0) {
            $total_fare += $kasrePansadToman;
//            $fTripGenerateFare = $total_fare;
//            if ($kasrePansadToman < 0) {
//                // اضافه کردن مقدار تخفیف سوار
//                $result['SavarCustomOff'] = -1 * $kasrePansadToman;
//            } else {
//                // افزودن مبلغ اضافه شده به هزینه مسیر
//                $result['FareOfDistance'] += $kasrePansadToman;
//            }
        }

        if ($total_fare % 10 > 0) {
            $total_fare -= $total_fare % 10;
        }

        $fareData->total_fare = $total_fare;
        $fareData->fPricePerMin = $finalFare['FareOfMinutes'];
        $fareData->fPricePerKM = $finalFare['FareOfDistance'];
        $fareData->fCommision = $finalFare['FareOfCommision'];
        return $fareData;
    }

    public static function calculateFinalFare($iBaseFare, $priceParMin, $tripTimeInMinutes, $priceParKM, $distance, $siteCommision, $priceRatio, $vCurrencyCode, $startDate, $endDate)
    {

        if ($startDate != '' && $endDate != '') {
            $tripTimeInMinutes = @round(abs(strtotime($startDate) - strtotime($endDate)) / 60, 2);
        }

        $Minute_Fare = round($priceParMin * $tripTimeInMinutes, 2) * $priceRatio;
        $Distance_Fare = round($priceParKM * $distance, 2) * $priceRatio;
        $iBaseFare = round($iBaseFare, 2) * $priceRatio;

        $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;

        $Commision_Fare = round((($total_fare * $siteCommision) / 100), 2) * $priceRatio;

        $total_fare = $total_fare + $Commision_Fare;

        $result['FareOfMinutes'] = $Minute_Fare;
        $result['FareOfDistance'] = $Distance_Fare;
        $result['FareOfCommision'] = $Commision_Fare;
        $result['iBaseFare'] = $iBaseFare;
        $result['fPricePerMin'] = $priceParMin * $priceRatio;
        $result['fPricePerKM'] = $priceParKM * $priceRatio;
        $result['fCommision'] = $siteCommision * $priceRatio;
        $result['FinalFare'] = $total_fare;

        return $result;
    }

    public static function roundOfForPassenger($total_fare)
    {
        $fixValue = 500;
        $roundHalf = 200;
        // Added By SeyyedAmir For round Fare Up 500
        // رند آپ مبلغ با افزایش کرایه مسیر
        $spaceValue = $total_fare % $fixValue;

        if ($spaceValue != 0) {
            if ($spaceValue <= $roundHalf)
                return -1 * $spaceValue;           // 1200 -> -1 * 200 = -200 -> 1000
            else
                return $fixValue - $spaceValue; // 1300 -> 500 - 300 = 200 -> 1500
        }

        return 0;
    }
}