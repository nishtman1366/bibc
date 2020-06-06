<?php

#This file edited by Seyyed Amir 1396.01.28
#changed sendEmeSms function

/*to clean function */
function clean($str)
{
    $str = trim($str);
//    $str = mysql_real_escape_string($str);
    $str = htmlspecialchars($str);
    $str = strip_tags($str);
    return ($str);
}

/* get vLangCode as per member or if member not found check lcode and then defualt take lang code set at $lang_label */
function getLanguageCode($memberId = '', $lcode = '')
{
    global $lang_label, $lang_code, $obj;
    /* find vLanguageCode using member id */
    if ($memberId != '') {

        $sql = "SELECT  `vLanguageCode` FROM  `member` WHERE iMemberId = '" . $memberId . "' AND `eStatus` = 'Active' ";
        $get_vLanguageCode = $obj->MySQLSelect($sql);

        if (count($get_vLanguageCode) > 0)
            $lcode = (isset($get_vLanguageCode[0]['vLanguageCode']) && $get_vLanguageCode[0]['vLanguageCode'] != '') ? $get_vLanguageCode[0]['vLanguageCode'] : '';
    }

    /* find default language of website set by admin */
    if ($lcode == '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);

        $lcode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }

    $lang_code = $lcode;
    $sql = "SELECT  `vLabel` ,  `vValue`  FROM  `language_label`  WHERE  `vCode` = '" . $lcode . "' ";
    $all_label = $obj->MySQLSelect($sql);

    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $lang_label[$vLabel] = $vValue;
    }
    //echo "<pre>"; print_R($lang_label); echo "</pre>";
}

#function to get value from table can be use for any table - create to get value from configuration
#$check_phone = get_value('configurations', 'vValue', 'vName', 'PHONE_VERIFICATION_REQUIRED');
function get_value($table, $field_name, $condition_field = '', $condition_value = '', $setParams = '', $directValue = '')
{
    global $obj;
    $returnValue = [];

    $where = ($condition_field != '') ? ' WHERE ' . clean($condition_field) : '';
    $where .= ($where != '' && $condition_value != '') ? ' = "' . clean($condition_value) . '"' : '';

    if ($table != '' && $field_name != '' && $where != '') {
        $sql = "SELECT $field_name FROM  $table $where";
        if ($setParams != '') {
            $sql .= $setParams;
        }
        $returnValue = $obj->MySQLSelect($sql);
    } else if ($table != '' && $field_name != '') {
        $sql = "SELECT $field_name FROM  $table";
        if ($setParams != '') {
            $sql .= $setParams;
        }
        $returnValue = $obj->MySQLSelect($sql);
    }
    if ($directValue == '') {
        return $returnValue;
    } else {
        $temp = $returnValue[0][$field_name];
        return $temp;
    }

}

function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function createUserLog($userType, $eAutoLogin, $iMemberId, $deviceType)
{
    global $generalobj, $obj;

    if (SITE_TYPE != "Demo") {
        return "";
    }
    $data['iMemberId'] = $iMemberId;
    $data['eMemberType'] = $userType;
    $data['eMemberLoginType'] = "AppLogin";
    $data['eDeviceType'] = $deviceType;
    $data['eAutoLogin'] = $eAutoLogin;
    $data['vIP'] = get_client_ip();

    $id = $obj->MySQLQueryPerform("member_log", $data, 'insert');
}

function dateDifference($date_1, $date_2, $differenceFormat = '%a')
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);

    $interval = date_diff($datetime1, $datetime2);

    return $interval->format($differenceFormat);

}

function getVehicleTypes($cityName = "")
{
    global $obj;
    $sql_vehicle_type = "SELECT * FROM vehicle_type";

    $row_result_vehivle_type = $obj->MySQLSelect($sql_vehicle_type);
    return $row_result_vehivle_type;
}

function paymentimg($paymentm)
{
    global $tconfig;
    if ($paymentm == "Card") {
        // return "webimages/icons/payment_images/ic_payment_type_card.png";
        return $tconfig["tsite_url"] . "webimages/icons/payment_images/ic_payment_type_card.png";
    } else {
        // return "webimages/icons/payment_images/ic_payment_type_cash.png";
        return $tconfig["tsite_url"] . "webimages/icons/payment_images/ic_payment_type_cash.png";
    }
}

function ratingmark($ratingval)
{
    global $tconfig;
    $a = $ratingval;
    $b = explode('.', $a);
    $c = $b[0];

    $str = "";
    $count = 0;
    for ($i = 0; $i < 5; $i++) {
        if ($c > $i) {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" align="left" >';
        } elseif ($a > $c && $count == 0) {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Half-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" align="left" >';
            $count = 1;
        } else {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-blank.png" style="outline:none;text-decoration:none;width:20px;border:none" align="left" >';
        }
    }
    return $str;

}

function getTripFare($Fare_data, $surgePrice)
{

    // this surge for price zone
    // disabled base time in this
    if ($surgePrice > 0) {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $surgePrice;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $surgePrice;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $surgePrice;
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $surgePrice;
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'];
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
    }

    $Minute_Fare = round($Fare_data[0]['fPricePerMin'] * $Fare_data[0]['TripTimeMinutes'], 0);
    /// add by seyyed amir
    $WatingTimeMinute_Fare = round($Fare_data[0]['fWaitingPricePerMin'] * $Fare_data[0]['WaitingTimeInTrip'], 0);
    ######################
    $Minute_Fare += $WatingTimeMinute_Fare;
    ///////
    $Distance_Fare = round($Fare_data[0]['fPricePerKM'] * $Fare_data[0]['TripDistance'], 0);
    $iBaseFare = round($Fare_data[0]['iBaseFare'], 0);

    $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;


    $Commision_Fare = round((($total_fare * $Fare_data[0]['fCommision']) / 100), 0);

    $result['FareOfMinutes'] = $Minute_Fare;
    $result['FareOfWaitingTime'] = $WatingTimeMinute_Fare;
    $result['FareOfDistance'] = $Distance_Fare;
    $result['FareOfCommision'] = $Commision_Fare;
    // $result['iBaseFare']     = $iBaseFare;
    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['fCommision'] = $Fare_data[0]['fCommision'];
    $result['FinalFare'] = $total_fare;
    $result['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['iMinFare'] = $Fare_data[0]['iMinFare'];

    return $result;

}

function calculateFare($tReturn, $delayId, $tSecDestination, $totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $couponCode = "", $tripId, $waitingTimeInTrip = 0)
{
    global $generalobj, $obj;
    $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);

    $ePriceZoon = $Fare_data[0]['ePriceZone'];
    $priceZoneArray = @unserialize($Fare_data[0]['tPriceZoneSerialize']);
    $priceZoneRatio = 1;

    if ($priceZoneArray === false)
        $priceZoneArray = array();
    else
        $priceZoneArray = array_reverse($priceZoneArray);

    require_once(TPATH_CLASS . 'savar/class.telegrambot.php');
    $tgb = new TelegramBot();
    //$tgb->sendMessage($Fare_data[0]['tPriceZoneSerialize'] . print_r($priceZoneArray,true));

    if ($ePriceZoon == "Active" && count($priceZoneArray) > 0) {
        foreach ($priceZoneArray as $zone) {
            if (isset($zone['zoneDistance']) == false)
                continue;

            if ($tripDistance > $zone['zoneDistance']) {
                // disable dy seyyed amir $priceRatio *= $zone['zoneSurcharge'];
                $priceZoneRatio = $zone['zoneSurcharge'];
                #$tgb->sendMessage("Price Ration for zone : " . print_r($Fare_data,true) . "\n" .  print_r($zone,true));
                break;
            }
        }
    }

    if ($priceZoneRatio > 0) {
        //$Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $priceZoneRatio;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $priceZoneRatio;
        //$Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $priceZoneRatio;
        //$Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $priceZoneRatio;
    }

    ///////////////////////////////////////////////////////


    // $defaultCurrency = ($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $fPickUpPrice = get_value('trips', 'fPickUpPrice', 'iTripId', $tripId, '', 'true');
    $fNightPrice = get_value('trips', 'fNightPrice', 'iTripId', $tripId, '', 'true');
    $vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $tripId, '', 'true');
    $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);

    $tripTimeInMinutes = ($startDate != '' && $endDate != '') ? (@round(abs(strtotime($startDate) - strtotime($endDate)) / 60, 2)) : 0;

    /////////////////////////////////////
    // add by seyyed amir for fixed fare
    $typeOfFare = $generalobj->getConfigurations("configurations", "TYPE_OF_FARE_CALCULATION");
    if ($typeOfFare == "Fixed") {
        $tripTimeInMinutes = $totalTimeInMinutes_trip;
    }

    $Fare_data[0]['TripTimeMinutes'] = $tripTimeInMinutes;
    $Fare_data[0]['TripDistance'] = $tripDistance;
    $Fare_data[0]['WaitingTimeInTrip'] = $waitingTimeInTrip;

    // هزینه های سفر بر اساس زمان و مسافت و ... محاسبه می شود

    $result = getTripFare($Fare_data, $priceRatio);
    #############################################################################
    #if(function_exists('Logger')) Logger($result);
    //$resultArr_Orig = getTripFare($Fare_data,"1");


    $total_fare = $result['FinalFare'];
    $fTripGenerateFare = $result['FinalFare'];
    $iMinFare = $result['iMinFare'];

    // مبلغ کل سفر با حداقل کرایه مقایسه میشود
    // اگر مبلغ کل از حداقل کرایه کمتر بود مبلغ کل برابر حد اقل میشود
    // و مقدار اضافه شده در متغیری ذخیره می شود تا نمایش داده شود
    if ($iMinFare > $fTripGenerateFare) {
        $MinFareDiff = $iMinFare - $total_fare;
        $total_fare = $iMinFare;
        $fTripGenerateFare = $iMinFare;
    } else {
        $MinFareDiff = "0";
    }

    // در این قسمت ضریب کرایه شبانه یا پیک تایم در جمع هزینه ضرب میشود
    $fSurgePriceDiff = round(($fTripGenerateFare * $surgePrice) - $fTripGenerateFare, 0);
    $total_fare = $total_fare + $fSurgePriceDiff;
    $fTripGenerateFare = $fTripGenerateFare + $fSurgePriceDiff;


    /*  in ghesmat be round up pansadtoman tabdil shod
	// Added By SeyyedAmir For round Fare Up 500
	// رند آپ مبلغ با افزایش کرایه مسیر
	$result['FareOfDistance'];
	$kasreHezarToman = $total_fare % 1000;

	if($kasreHezarToman != 0)
	{
		if($kasreHezarToman <= 500)
			$faselePansadToman = 500;
		else
			$faselePansadToman = 1000;

		// ابتدا مبلغ جهت رند کردن به سمت بالا تا 500 تومان به دست آمد
		// بعد فاصله تا آن مبلغ را اضافه میکنیم
		$roundUpFare = 	$faselePansadToman - $kasreHezarToman;

		// افزودن مبلغ رند آپ به جمع قابل پرداخت
		$total_fare += $roundUpFare;
		$fTripGenerateFare = $total_fare;

		// افزودن مبلغ اضافه شده به هزینه مسیر
		$result['FareOfDistance'] += $roundUpFare;
	}

	#if(function_exists('Logger')) Logger('RoundUp : '.$total_fare. ' -- ' . $fTripGenerateFare);
	#if(function_exists('Logger')) Logger($result);
	////////////////////////////////////////////
	// رند آپ مبلغ با افزایش کرایه مسیر
	// END ROUND UP
*/

    // Added By SeyyedAmir For round Fare Up 500
    // رند آپ مبلغ با افزایش کرایه مسیر
    $tbl_name = "SnapSettings";
    $sql = "SELECT * FROM SnapSettings WHERE 1 ORDER BY `SnapSettings`.`id` ASC";
    $db_data1 = $obj->MySQLSelect($sql);
    $returnRate = $db_data1[0]['setting_value'];
    $secRate = $db_data1[15]['setting_value'];
    if ($tSecDestination == 'true') {
        $total_fare = $total_fare * $secRate;
    }
    if ($tReturn == 'true') {
        $total_fare = $total_fare * $returnRate;
    }
    if ($delayId > 0) {
        $total_fare = $total_fare + ($db_data1[$delayId - 1]['setting_value']);
    }
    $kasrePansadToman = SavarRoundedOff($total_fare);

    if ($kasrePansadToman != 0) {
        $total_fare += $kasrePansadToman;
        $fTripGenerateFare = $total_fare;

        if ($kasrePansadToman < 0) {
            // اضافه کردن مقدار تخفیف سوار
            $result['SavarCustomOff'] = -1 * $kasrePansadToman;
        } else {
            // افزودن مبلغ اضافه شده به هزینه مسیر
            $result['FareOfDistance'] += $kasrePansadToman;
        }

    }

    #if(function_exists('Logger')) Logger('RoundUp : '.$total_fare. ' -- ' . $fTripGenerateFare);
    #if(function_exists('Logger')) Logger($result);
    // END ROUND UP


    //if(function_exists('Logger')) Logger('Pick : '.$total_fare. ' -- ' . $fTripGenerateFare);
    // در این قسمت کوپن تخفیف در صورت وجود اعمال می شود
    /*Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
        $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($total_fare * $discountValue), 0) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $total_fare) {
                $vDiscount = round($total_fare, 0) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 0) . ' ' . $curr_sym;
            }
        }
        $fare = $total_fare - $discountValue;
        if ($fare < 0) {
            $fare = 0;
            $discountValue = $total_fare;
        }
        $total_fare = $fare;
        $Fare_data[0]['fDiscount'] = $discountValue;
        $Fare_data[0]['vDiscount'] = $vDiscount;
    }
    /*Check Coupon Code Total Fare  End*/


    #if(function_exists('Logger')) Logger('coupon : ' . $total_fare. ' -- ' . $fTripGenerateFare);
    // کسر اجباری مبلغ از کیف پول !!!!
    /*Check debit wallet For Count Total Fare  Start*/
    if ($vTripPaymentMode == 'Card') {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        $user_wallet_debit_amount = 0;
        if ($total_fare > $user_available_balance) {
            $total_fare = $total_fare - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
        } else {
            $user_wallet_debit_amount = $total_fare;
            $total_fare = 0;
        }

        #if(function_exists('Logger')) Logger('Wallet : ' . $total_fare. ' -- ' . $fTripGenerateFare);

        // Update User Wallet
        if ($user_wallet_debit_amount > 0) {
            $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $tripId, '', 'true');
            $data_wallet['iUserId'] = $iUserId;
            $data_wallet['eUserType'] = "Rider";
            $data_wallet['iBalance'] = $user_wallet_debit_amount;
            $data_wallet['eType'] = "Debit";
            $data_wallet['dDate'] = date("Y-m-d H:i:s");
            $data_wallet['iTripId'] = $tripId;
            $data_wallet['eFor'] = "Booking";
            $data_wallet['ePaymentStatus'] = "Unsettelled";
            $data_wallet['tDescription'] = "مقدار " . $user_wallet_debit_amount . " کسر شده از حساب شما برای سفر با شماره #" . $vRideNo;

            $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
            //$obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
        }
    }
    /*Check debit wallet For Count Total Fare  End*/


    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = 0;
    } else {
        $Fare_data[0]['iBaseFare'] = $result['iBaseFare'];
    }

    $finalFareData['total_fare'] = $total_fare;
    $finalFareData['iBaseFare'] = $result['iBaseFare'];
    $finalFareData['fPricePerMin'] = $result['FareOfMinutes'];
    $finalFareData['fPricePerKM'] = $result['FareOfDistance'];
    //$finalFareData['fCommision'] = $result['FareOfCommision'];
    $finalFareData['fCommision'] = round((($fTripGenerateFare * $result['fCommision']) / 100), 0);
    $finalFareData['fDiscount'] = $Fare_data[0]['fDiscount'];
    $finalFareData['WaitingTimeInTrip'] = $waitingTimeInTrip;
    $finalFareData['FareOfWaitingTime'] = $result['FareOfWaitingTime'];
    $finalFareData['vDiscount'] = $Fare_data[0]['vDiscount'];
    $finalFareData['MinFareDiff'] = $MinFareDiff;
    $finalFareData['fSurgePriceDiff'] = $fSurgePriceDiff;
    $finalFareData['user_wallet_debit_amount'] = $user_wallet_debit_amount;
    $finalFareData['fTripGenerateFare'] = $fTripGenerateFare;
    $finalFareData['SurgePriceFactor'] = $surgePrice;
    $finalFareData['priceZoneRatio'] = $priceZoneRatio;

    return $finalFareData;
}


function calculateFareEstimate($hasSecDst, $hasReturn, $delayId, $totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $surgePrice = 1)
{
    global $generalobj, $obj;
    $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);

    /// added for price zone by seyyed amir
    $ePriceZoon = $Fare_data[0]['ePriceZone'];
    $priceZoneArray = @unserialize($Fare_data[0]['tPriceZoneSerialize']);

    if ($priceZoneArray === false)
        $priceZoneArray = array();
    else
        $priceZoneArray = array_reverse($priceZoneArray);

    require_once(TPATH_CLASS . 'savar/class.telegrambot.php');
    $tgb = new TelegramBot();
    //$tgb->sendMessage($Fare_data[0]['tPriceZoneSerialize'] . print_r($priceZoneArray,true));

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

    ///////////////////////////////////////////////////////


    // $defaultCurrency = ($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

    //if(function_exists("TLOG")) TLOG($defaultCurrency);

    if ($surgePrice > 1) {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $surgePrice;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $surgePrice;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $surgePrice;
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $surgePrice;
    }

    // add by seyyed amir
    // price zone ration only chnge price per KM and Base Fare
    if ($priceZoneRatio > 0) {
        //$Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $priceZoneRatio;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $priceZoneRatio;
        //$Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $priceZoneRatio;
        //$Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $priceZoneRatio;
    }

    ///////////////////////////

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'];
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
    }

    //if(function_exists("TLOG")) TLOG($Fare_data);

    $resultArr = $generalobj->getFinalFare($Fare_data[0]['iBaseFare'], $Fare_data[0]['fPricePerMin'], $totalTimeInMinutes_trip, $Fare_data[0]['fPricePerKM'], $tripDistance, $Fare_data[0]['fCommision'], $priceRatio, $defaultCurrency, $startDate, $endDate);
    $resultArr['FinalFare'] = $resultArr['FinalFare'] - $resultArr['FareOfCommision']; // Temporary set: Remove addition of commision from above function

    $Fare_data[0]['total_fare'] = $resultArr['FinalFare'];

    if ($Fare_data[0]['iMinFare'] > $Fare_data[0]['total_fare']) {
        $Fare_data[0]['MinFareDiff'] = $Fare_data[0]['iMinFare'] - $Fare_data[0]['total_fare'];
        $Fare_data[0]['total_fare'] = $Fare_data[0]['iMinFare'];
    } else {
        $Fare_data[0]['MinFareDiff'] = "0";
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = 0;
    } else {
        $Fare_data[0]['iBaseFare'] = $resultArr['iBaseFare'];
    }


    $total_fare = $Fare_data[0]['total_fare'];

    $tbl_name = "SnapSettings";
    $sql = "SELECT * FROM SnapSettings WHERE 1 ORDER BY `SnapSettings`.`id` ASC";
    $db_data1 = $obj->MySQLSelect($sql);
    $returnRate = $db_data1[0]['setting_value'];
    $secRate = $db_data1[15]['setting_value'];
    if ($hasSecDst == 'true') {
        $total_fare = $total_fare * $secRate;
    }
    if ($hasReturn == 'true') {
        $total_fare = $total_fare * $returnRate;
    }
    if ($delayId > 0) {
        $total_fare = $total_fare + ($db_data1[$delayId - 1]['setting_value']);
    }
    $kasrePansadToman = SavarRoundedOff($total_fare);

    if ($kasrePansadToman != 0) {
        $total_fare += $kasrePansadToman;
        $fTripGenerateFare = $total_fare;

        if ($kasrePansadToman < 0) {
            // اضافه کردن مقدار تخفیف سوار
            $result['SavarCustomOff'] = -1 * $kasrePansadToman;
        } else {
            // افزودن مبلغ اضافه شده به هزینه مسیر
            $result['FareOfDistance'] += $kasrePansadToman;
        }

    }

    if ($total_fare % 10 > 0) {
        $total_fare -= $total_fare % 10;
    }

    $Fare_data[0]['total_fare'] = $total_fare;
    $Fare_data[0]['fPricePerMin'] = $resultArr['FareOfMinutes'];
    $Fare_data[0]['fPricePerKM'] = $resultArr['FareOfDistance'];
    $Fare_data[0]['fCommision'] = $resultArr['FareOfCommision'];
    return $Fare_data;
}


// added by seyyed amir
// daryafte meghdare kasr ya afzayesh baraye round kardan
function SavarRoundedOff($total_fare)
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


function getVehicleFareConfig($tabelName, $vehicleTypeID)
{
    global $obj;
    $sql = "SELECT * FROM `" . $tabelName . "` WHERE iVehicleTypeId='$vehicleTypeID'";
    $Data_fare = $obj->MySQLSelect($sql);

    return $Data_fare;

}

function processTripsLocations($tripId, $latitudes, $longitudes)
{
    global $obj;
    $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
    $DataExist = $obj->MySQLSelect($sql);

    if (count($DataExist) > 0) {

        $latitudeList = $DataExist[0]['tPlatitudes'];
        $longitudeList = $DataExist[0]['tPlongitudes'];

        if ($latitudeList != '') {
            $data_latitudes = $latitudeList . ',' . $latitudes;
        } else {
            $data_latitudes = $latitudes;
        }

        if ($longitudeList != '') {
            $data_longitudes = $longitudeList . ',' . $longitudes;
        } else {
            $data_longitudes = $longitudes;
        }

        $where = " iTripId = '" . $tripId . "'";
        $Data_tripsLocations['tPlatitudes'] = $data_latitudes;
        $Data_tripsLocations['tPlongitudes'] = $data_longitudes;
        $id = $obj->MySQLQueryPerform("trips_locations", $Data_tripsLocations, 'update', $where);


    } else {

        $Data_trips_locations['iTripId'] = $tripId;
        $Data_trips_locations['tPlatitudes'] = $latitudes;
        $Data_trips_locations['tPlongitudes'] = $longitudes;

        $id = $obj->MySQLQueryPerform("trips_locations", $Data_trips_locations, 'insert');

    }
    return $id;
}

function calcluateTripDistance($tripId)
{
    global $obj;
    $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
    $Data_tripsLocations = $obj->MySQLSelect($sql);

    $TotalDistance = 0;
    if (count($Data_tripsLocations) > 0) {
        $trip_path_latitudes = $Data_tripsLocations[0]['tPlatitudes'];
        $trip_path_longitudes = $Data_tripsLocations[0]['tPlongitudes'];

        $trip_path_latitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_latitudes);
        $trip_path_longitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_longitudes);

        $TripPathLatitudes = explode(",", $trip_path_latitudes);

        $TripPathLongitudes = explode(",", $trip_path_longitudes);

        for ($i = 0; $i < count($TripPathLatitudes) - 1; $i++) {
            $tempLat_current = $TripPathLatitudes[$i];
            $tempLon_current = $TripPathLongitudes[$i];
            $tempLat_next = $TripPathLatitudes[$i + 1];
            $tempLon_next = $TripPathLongitudes[$i + 1];

            if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_next == '0.0' || $tempLon_next == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0' || $tempLat_next == '-180.0' || $tempLon_next == '-180.0') {
                continue;
            }

            $TempDistance = distanceByLocation($tempLat_current, $tempLon_current, $tempLat_next, $tempLon_next, "K");

            if (is_nan($TempDistance)) {
                $TempDistance = 0;
            }
            $TotalDistance += $TempDistance;
        }

    }

    return round($TotalDistance, 2);
}

function checkDistanceWithGoogleDirections1($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "")
{
    /*global $generalobj,$obj;

		if($vGMapLangCode == "" || $vGMapLangCode == NULL){
			 $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault','Yes');
			$vGMapLangCode=$vLangCodeData[0]['vGMapLangCode'];
		}

		$GOOGLE_API_KEY=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$startLatitude.",".$startLongitude."&destination=".$endLatitude.",".$endLongitude."&sensor=false&key=".$GOOGLE_API_KEY."&language=".$vGMapLangCode;

				try {
						$jsonfile = file_get_contents($url);
			} catch (ErrorException $ex) {
						// return $tripDistance;

						$returnArr['Action'] = "0";
						echo json_encode($returnArr);
						exit;
			}
			$jsondata = json_decode($jsonfile);

			$distance_google_directions=($jsondata->routes[0]->legs[0]->distance->value)/1000;
			$duration_google_directions=($jsondata->routes[0]->legs[0]->duration->value)/60;

			$returnArr['Time'] =$duration_google_directions;
			$returnArr['Distance'] =$distance_google_directions;
			return $returnArr;*/

    $origin = $startLongitude . "," . $startLatitude;
    $destination = $endLongitude . "," . $endLatitude;
    $authToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkxNDY2NTQ5ZTI1ZGYxNzlhMGM1YjUwZGUxMzM0ODQxNGVhNDBkNjI4YTdhNDE4Mzg3NDIxNzNiYjRhYzg2NjlhNzg3YzQ3MmFmMjRmMjgwIn0.eyJhdWQiOiJteWF3ZXNvbWVhcHAiLCJqdGkiOiI5MTQ2NjU0OWUyNWRmMTc5YTBjNWI1MGRlMTMzNDg0MTRlYTQwZDYyOGE3YTQxODM4NzQyMTczYmI0YWM4NjY5YTc4N2M0NzJhZjI0ZjI4MCIsImlhdCI6MTU0NDM2MTUzMywibmJmIjoxNTQ0MzYxNTMzLCJleHAiOjE1NDQzNjUxMzMsInN1YiI6IiIsInNjb3BlcyI6WyJiYXNpYyIsImVtYWlsIl19.Wct5e7Ph3TXCZcyDXzBjN0UEFxNcmzO0BTOTq7CZu_8QBTdB3ysqm0meXHavWT8OhZ3449wb-oIJDDgrtizt88vtZjuQwRM66COqIJ7SMI15eFRopuoNd8Vgzcri4CMb6sSc399ckCPKYplOg-iV-qaCXwdWroRGN_-bJH2c8jkgBD35jZQgJgglX0qMHUQAWLIHxdCX4cZLBBB3bmJ713J6S9FDnl-ozuqPDYw2mSdGzf7xgrwQWJ3j78HiMrKymMwiJPAn__axVBUfm2sR7UQFqpVBdwLcmI4PRAM-fGbRnNlrJOW3dqfZ81Svgl21_jMKWaYTaHAlw79TN0OMJw';
    $url = 'https://map.ir/routes/route/v1/driving/' . $origin . ";" . $destination . "?alternatives=false&steps=false";
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'header' => "x-api-key: {$authToken}\r\n"
        )));

    $response = file_get_contents($url, FALSE, $context);
    $responseData = json_decode($response, TRUE);

    if ($response == null || $responseData == null
        || $responseData['code'] == null
        || $responseData['code'] != "Ok") {
        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
    }
    $returnArr['Time'] = $responseData['routes'][0]['duration'] / 60;
    $returnArr['Distance'] = $responseData['routes'][0]['distance'] / 1000;
    return $returnArr;
}

function checkDistanceWithGoogleDirections($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "")
{
    global $generalobj, $obj;

    if ($vGMapLangCode == "" || $vGMapLangCode == NULL) {
        $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
        $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];
    }

    $GOOGLE_API_KEY = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_API_KEY_WEB");
    $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=false&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode;

    try {
        $jsonfile = file_get_contents($url);
    } catch (ErrorException $ex) {
        // return $tripDistance;

        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
        // echo 'Site not reachable (' . $ex->getMessage() . ')';
    }

    $jsondata = json_decode($jsonfile);
    $distance_google_directions = ($jsondata->routes[0]->legs[0]->distance->value) / 1000;

    if ($isFareEstimate == "0") {
        $comparedDist = ($distance_google_directions * 85) / 100;

        if ($tripDistance > $comparedDist) {
            return $tripDistance;
        } else {
            return round($distance_google_directions, 2);
        }
    } else {
        $duration_google_directions = ($jsondata->routes[0]->legs[0]->duration->value) / 60;
        $sAddress = ($jsondata->routes[0]->legs[0]->start_address);
        $dAddress = ($jsondata->routes[0]->legs[0]->end_address);
        $steps = ($jsondata->routes[0]->legs[0]->steps);

        $returnArr['Time'] = $duration_google_directions;
        $returnArr['Distance'] = $distance_google_directions;
        $returnArr['SAddress'] = $sAddress;
        $returnArr['DAddress'] = $dAddress;
        $returnArr['steps'] = $steps;

        return $returnArr;
    }

}

function distanceByLocation($lat1, $lon1, $lat2, $lon2, $unit)
{
    if ((($lat1 == $lat2) && ($lon1 == $lon2)) || ($lat1 == '' || $lon1 == '' || $lat2 == '' || $lon2 == '')) {
        return 0;
    }

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function getLanguageLabelsArr($lCode = '', $directValue = "")
{
    global $obj;

    /* find default language of website set by admin */
    $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
    $default_label = $obj->MySQLSelect($sql);

    if ($lCode == '') {
        $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }


    $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label`  WHERE  `vCode` = '" . $lCode . "' ";
    $all_label = $obj->MySQLSelect($sql);

    $x = array();
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }


    $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_other`  WHERE  `vCode` = '" . $lCode . "' ";
    $all_label = $obj->MySQLSelect($sql);

    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];

        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }

    $x['vCode'] = $lCode; // to check in which languge code it is loading

    if ($directValue == "") {
        $returnArr['Action'] = "1";
        $returnArr['LanguageLabels'] = $x;

        return $returnArr;
    } else {
        return $x;
    }

}

/*
	function sendEmeSms($toMobileNum,$message){
	global  $generalobj;
		$account_sid = $generalobj->getConfigurations("configurations","MOBILE_VERIFY_SID_TWILIO");
		$auth_token = $generalobj->getConfigurations("configurations","MOBILE_VERIFY_TOKEN_TWILIO");
		$twilioMobileNum= $generalobj->getConfigurations("configurations","MOBILE_NO_TWILIO");

		$client = new Services_Twilio($account_sid, $auth_token);
		try{
			$sms = $client->account->messages->sendMessage($twilioMobileNum,$toMobileNum,$message);
			return 1;
		} catch (Services_Twilio_RestException $e) {
			return 0;
		}
	}
	*/

// Edit By Seyyed.AMir ,savar
function sendEmeSms($toMobileNum, $message)
{
    global $generalobj;
    require_once(TPATH_CLASS . 'savar/class.sms.php');
    $account_sid = $generalobj->getConfigurations("configurations", "MOBILE_VERIFY_SID_TWILIO");
    $auth_token = $generalobj->getConfigurations("configurations", "MOBILE_VERIFY_TOKEN_TWILIO");
    $MobileNum = $generalobj->getConfigurations("configurations", "MOBILE_NO_TWILIO");
    $smsObj = new SMS($account_sid, $auth_token, $MobileNum);
    $ret = $smsObj->SendSMS($toMobileNum, $message);
    return $ret;
}

function converToTz($time, $toTz, $fromTz)
{
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format('Y-m-d H:i:s');
    return $time;
}

/**
 * Sending Push Notification
 */
function send_notification($registatoin_ids, $message, $filterMsg = 0)
{
    // include config
    // include_once './config.php';
    global $generalobj, $obj;
    $GOOGLE_API_KEY = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_GCM_API_KEY");
    // Set POST variables
    //$url = 'https://android.googleapis.com/gcm/send';
    $url = 'https://fcm.googleapis.com/fcm/send';

    $fields = array(
        'registration_ids' => $registatoin_ids,
        'priority' => 'high',
        'data' => $message,
    );

    $headers = array(
        'Authorization: key=' . $GOOGLE_API_KEY,
        'Content-Type: application/json'
    );
    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    $finalFields = json_encode($fields, JSON_UNESCAPED_UNICODE);


    if ($filterMsg == 1) {
        $finalFields = stripslashes(preg_replace("/[\n\r]/", "", $finalFields));
    }


    curl_setopt($ch, CURLOPT_POSTFIELDS, $finalFields);

    require_once(TPATH_CLASS . 'savar/class.telegrambot.php');

    #$tgb = new TelegramBot();
    //$tgb->sendMessage(print_r($fields,true));
    #$tgb->sendMessage(($finalFields));

    // Execute post
    $result = curl_exec($ch);

    #$tgb->sendMessage(print_r($result,true));

    if ($result === FALSE) {
        // die('Curl failed: ' . curl_error($ch));
        $returnArr['Action'] = "0";
        $returnArr['message'] = "GCM_FAILED";
        $returnArr['ERROR'] = curl_error($ch);
        echo json_encode($returnArr);
        exit;
    }


    // Close connection
    curl_close($ch);
    return $result;
}

function sendApplePushNotification($PassengerToDriver = 0, $deviceTokens, $message, $alertMsg, $filterMsg)
{

    Logger($deviceTokens);

    global $generalobj, $obj;

    $passphrase = $generalobj->getConfigurations("configurations", "IPHONE_PEM_FILE_PASSPHRASE");
    $APP_MODE = $generalobj->getConfigurations("configurations", "APP_MODE");

    $prefix = "";
    $url_apns = 'ssl://gateway.sandbox.push.apple.com:2195';
    if ($APP_MODE == "Production") {
        $prefix = "PRO_";
        $url_apns = 'ssl://gateway.push.apple.com:2195';
    }

    if ($PassengerToDriver == 1) {
        $name = $generalobj->getConfigurations("configurations", $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME");
    } else {
        $name = $generalobj->getConfigurations("configurations", $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME");
    }

    $ctx = stream_context_create();

    stream_context_set_option($ctx, 'ssl', 'local_cert', $name);

    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client(
        $url_apns, $err,
        $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    Logger($errstr);
    Logger($err);

    $LogMessage = array('code' => "APPLE PUSH NOTIFICATION");
    $LogMessage = array('TOKEN' => $deviceTokens);

    if (!$fp) {
        Logger("Failed");

        $returnArr['Action'] = "0";
        $returnArr['message'] = "APNS_FAILED";
        $returnArr['ERROR'] = PHP_EOL;

        echo json_encode($returnArr);

        $LogMessage["RETURN"] = $returnArr;
        //if(function_exists('TLOG')) TLOG($LogMessage);

        exit;
        // exit("Failed to connect: $err $errstr" . PHP_EOL);
    }


    // Create the payload body
    $body['aps'] = array(
        'alert' => $alertMsg,
        'content-available' => 1,
        'body' => $message,
        'sound' => 'notification.mp3'

    );

    // Encode the payload as JSON
    $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
//        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
    if ($filterMsg == 1) {
        $payload = stripslashes(preg_replace("/[\n\r]/", "", $payload));
    }

    for ($device = 0; $device < count($deviceTokens); $device++) {
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        Logger($result);
//            print_r($result);

    }


    $LogMessage["BODY"] = $body['aps'];
    $LogMessage["RES"] = $result;

    //if(function_exists('TLOG')) TLOG($LogMessage);


    // Close the connection to the server
    fclose($fp);
}


function getOnlineDriverArr($sourceLat, $sourceLon)
{
    global $generalobj, $obj, $ISDEBUG;

    // by seyyed amir
    //$str_date = @date('Y-m-d H:i:s', strtotime('-1440 minutes'));
    $str_date = @date('Y-m-d H:i:s', strtotime('-15 minutes'));
    $LIST_DRIVER_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_DRIVER_LIMIT_BY_DISTANCE");
    $DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");

    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    // if($DRIVER_REQUEST_METHOD == "Time"){
    // $param = " ORDER BY `register_driver`.`tOnline` ASC";
    // }else{
    // $param = " ORDER BY `register_driver`.`tLastOnline` ASC";
    // }

    $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(" . $sourceLon . ") )
		+ sin( radians(" . $sourceLat . ") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver`
					WHERE (vLatitude != '' AND vLongitude != '' AND vAvailability = 'Available' AND vTripStatus != 'Active' AND eStatus='active' AND tLastOnline > '$str_date')
					HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY `register_driver`.`" . $param . "` ASC";

    /* $sql = "SELECT ROUND(( 3959 * acos( cos( radians(".$sourceLat.") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(".$sourceLon.") )
		+ sin( radians(".$sourceLat.") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver`
					WHERE (vLatitude != '' AND vLongitude != '' AND vAvailability = 'Available' AND vTripStatus != 'Active' AND eStatus='active')
					HAVING distance < ".$LIST_DRIVER_LIMIT_BY_DISTANCE." ORDER BY `register_driver`.`".$param."` ASC"; */


    //if($ISDEBUG)
    //    die($sql);
    $Data = $obj->MySQLSelect($sql);

    return $Data;
}

function getOnlineDriverArrMulti($sourceLat, $sourceLon, $seats)
{
    global $generalobj, $obj, $ISDEBUG;

    // by seyyed amir
    //$str_date = @date('Y-m-d H:i:s', strtotime('-1440 minutes'));
    $str_date = @date('Y-m-d H:i:s', strtotime('-60 minutes'));
    $LIST_DRIVER_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_DRIVER_LIMIT_BY_DISTANCE");
    $DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");

    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    // if($DRIVER_REQUEST_METHOD == "Time"){
    // $param = " ORDER BY `register_driver`.`tOnline` ASC";
    // }else{
    // $param = " ORDER BY `register_driver`.`tLastOnline` ASC";
    // }

    $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(" . $sourceLon . ") )
		+ sin( radians(" . $sourceLat . ") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver`
					WHERE (vLatitude != '' AND vLongitude != ''AND vTripStatus = 'On Going Trip' AND vStatusML != 'Ready' AND eStatus='active' AND tLastOnline > '$str_date' AND eMultiPassenger = 'Yes')
					HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY `distance` ASC";


    /*$sql = "SELECT ROUND(( 3959 * acos( cos( radians(".$sourceLat.") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(".$sourceLon.") )
		+ sin( radians(".$sourceLat.") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver`
					WHERE (vLatitude != '' AND vLongitude != '' AND vAvailability = 'Available' AND vTripStatus != 'Active' AND eStatus='active' AND tLastOnline > '$str_date' AND eMultiPassenger = 'Yes')
					HAVING distance < ".$LIST_DRIVER_LIMIT_BY_DISTANCE." ORDER BY `distance` ASC";

		/* $sql = "SELECT ROUND(( 3959 * acos( cos( radians(".$sourceLat.") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(".$sourceLon.") )
		+ sin( radians(".$sourceLat.") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver`
					WHERE (vLatitude != '' AND vLongitude != '' AND vAvailability = 'Available' AND vTripStatus != 'Active' AND eStatus='active')
					HAVING distance < ".$LIST_DRIVER_LIMIT_BY_DISTANCE." ORDER BY `register_driver`.`".$param."` ASC"; */


    //if($ISDEBUG)
    //    die($sql);
    $Data = $obj->MySQLSelect($sql);

    return $Data;
}

function getAddressFromLocation($latitude, $longitude, $Google_Server_key)
{
    $location_Address = "";

    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude . "&key=" . $Google_Server_key;

    try {

        $jsonfile = file_get_contents($url);
        $jsondata = json_decode($jsonfile);
        $address = $jsondata->results[0]->formatted_address;

        $location_Address = $address;

    } catch (ErrorException $ex) {

        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
        // echo 'Site not reachable (' . $ex->getMessage() . ')';
    }

    if ($location_Address == "") {
        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
    }

    return $location_Address;
}

function getLanguageTitle($vLangCode)
{
    global $obj;

    $sql = "SELECT vTitle FROM language_master WHERE vCode = '" . $vLangCode . "' ";
    $db_title = $obj->MySQLSelect($sql);

    return $db_title[0]['vTitle'];
}

function checkSurgePrice($vehicleTypeID, $selectedDateTime = "")
{
    $ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');

    $fPickUpPrice = 1;
    $fNightPrice = 1;

    if ($selectedDateTime == "") {
        // $currentTime = @date("Y-m-d H:i:s");
        $currentTime = @date("H:i:s");
        $currentDay = @date("D");
    } else {
        // $currentTime = $selectedDateTime;
        $currentTime = @date("H:i:s", strtotime($selectedDateTime));
        $currentDay = @date("D", strtotime($selectedDateTime));
    }

    if ($ePickStatus == "Active" || $eNightStatus == "Active") {

        $startTime_str = "t" . $currentDay . "PickStartTime";
        $endTime_str = "t" . $currentDay . "PickEndTime";
        $price_str = "f" . $currentDay . "PickUpPrice";

        $pickStartTime = get_value('vehicle_type', $startTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $pickEndTime = get_value('vehicle_type', $endTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $fPickUpPrice = get_value('vehicle_type', $price_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');

        $nightStartTime = get_value('vehicle_type', 'tNightStartTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $nightEndTime = get_value('vehicle_type', 'tNightEndTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $fNightPrice = get_value('vehicle_type', 'fNightPrice', 'iVehicleTypeId', $vehicleTypeID, '', 'true');

        if ($currentTime > $pickStartTime && $currentTime < $pickEndTime && $ePickStatus == "Active") {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PICK_SURGE_NOTE";
            $returnArr['SurgePrice'] = (($fPickUpPrice - 1) * 10) . "%";
            $returnArr['SurgePriceValue'] = $fPickUpPrice;

        } else if ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $eNightStatus == "Active") {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
            $returnArr['SurgePrice'] = (($fNightPrice - 1) * 10) . "%";
            $returnArr['SurgePriceValue'] = $fNightPrice;

        } else {
            $returnArr['Action'] = "1";
        }

    } else {
        $returnArr['Action'] = "1";
    }

    return $returnArr;
}

function check_email_send($iDriverId, $tablename, $field)
{
    global $obj, $generalobj;
    $sql = "SELECT * FROM " . $tablename . " WHERE " . $field . "= '" . $iDriverId . "'";
    $db_data = $obj->MySQLSelect($sql);
    //print_r($db_data);//exit;
    //$valid=0;
    if ($tablename == 'register_driver') {
        //echo "hi";exit;
        if ($db_data[0]['vNoc'] != NULL && $db_data[0]['vLicence'] != NULL && $db_data[0]['vCerti'] != NULL) {
            //global $generalobj;
            $maildata['USER'] = "Driver";
            $maildata['NAME'] = $db_data[0]['vName'];
            $maildata['EMAIL'] = $db_data[0]['vEmail'];
            $generalobj->send_email_user("PROFILE_UPLOAD", $maildata);
            //header("location:profile.php?success=1&var_msg=" . $var_msg);
            //return;
        }
    } else {
        if ($db_data[0]['vNoc'] != NULL && $db_data[0]['vCerti'] != NULL) {
            $maildata['USER'] = "Company";
            $maildata['NAME'] = $db_data[0]['vName'];
            $maildata['EMAIL'] = $db_data[0]['vEmail'];
            //var_dump($maildata);
            //var_dump(($generalobj));
            $generalobj->send_email_user("PROFILE_UPLOAD", $maildata);
        }
    }
    return true;
}

function checkmemberemailphoneverification($iMemberId, $user_type = "Passenger")
{
    global $obj;
    if ($user_type == "Driver") {
        $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_EMAIL_VERIFICATION', '', 'true');
        $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_PHONE_VERIFICATION', '', 'true');
        $eEmailVerified = get_value('register_driver', 'eEmailVerified', 'iDriverId', $iMemberId, '', 'true');
        $ePhoneVerified = get_value('register_driver', 'ePhoneVerified', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_EMAIL_VERIFICATION', '', 'true');
        $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_PHONE_VERIFICATION', '', 'true');
        $eEmailVerified = get_value('register_user', 'eEmailVerified', 'iUserId', $iMemberId, '', 'true');
        $ePhoneVerified = get_value('register_user', 'ePhoneVerified', 'iUserId', $iMemberId, '', 'true');
    }

    $email = $EMAIL_VERIFICATION == "Yes" ? ($eEmailVerified == "Yes" ? "true" : "false") : "true";
    $phone = $PHONE_VERIFICATION == "Yes" ? ($ePhoneVerified == "Yes" ? "true" : "false") : "true";

    if ($email == "false" && $phone == "false") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_EMAIL_PHONE_VERIFY";
        echo json_encode($returnArr);
        exit;
    } else if ($email == "true" && $phone == "false") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_PHONE_VERIFY";
        echo json_encode($returnArr);
        exit;
    } else if ($email == "false" && $phone == "true") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_EMAIL_VERIFY";
        echo json_encode($returnArr);
        exit;
    }
}

function sendemailphoneverificationcode($iMemberId, $user_type = "Passenger", $VerifyType)
{
    global $generalobj, $obj;
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
        $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];

    $emailmessage = "";
    $phonemessage = "";
    if ($VerifyType == "email" || $VerifyType == "both") {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);

        $Data_Mail['vEmailVarificationCode'] = $random = substr(number_format(time() * rand(), 0, '', ''), 0, 4);
        $Data_Mail['vEmail'] = isset($db_member[0]['vEmail']) ? $db_member[0]['vEmail'] : '';
        $vFirstName = isset($db_member[0]['vName']) ? $db_member[0]['vName'] : '';
        $vLastName = isset($db_member[0]['vLastName']) ? $db_member[0]['vLastName'] : '';
        $Data_Mail['vName'] = $vFirstName . " " . $vLastName;
        $Data_Mail['CODE'] = $Data_Mail['vEmailVarificationCode'];

        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail) {
            $emailmessage = $Data_Mail['vEmailVarificationCode'];
        } else {
            $emailmessage = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
        }
    }

    if ($VerifyType == "phone" || $VerifyType == "both") {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);

        $mobileNo = $db_member[0]['vPhoneCode'] . $db_member[0]['vPhone'];
        $toMobileNum = "+" . $mobileNo;
        $verificationCode = mt_rand(1000, 9999);
        $message = $prefix . ' ' . $verificationCode;
        $result = sendEmeSms($toMobileNum, $message);
        if ($result == 0) {
            $phonemessage = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
        } else {
            $phonemessage = $verificationCode;
        }
    }

    $returnArr['emailmessage'] = $emailmessage;
    $returnArr['phonemessage'] = $phonemessage;
    return $returnArr;
}

function getTripPriceDetails($iTripId, $iMemberId, $eUserType = "Passenger")
{
    global $obj, $generalobj;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $tblname = "register_user";
        $vLang = "vLang";
        $iUserId = "iUserId";
        $vCurrency = "vCurrencyPassenger";

        $currencycode = get_value("trips", $vCurrency, "iTripId", $iTripId, '', 'true');
    } else {
        $tblname = "register_driver";
        $vLang = "vLang";
        $iUserId = "iDriverId";
        $vCurrency = "vCurrencyDriver";

        $currencycode = get_value($tblname, $vCurrency, $iUserId, $iMemberId, '', 'true');
    }
    $userlangcode = get_value($tblname, $vLang, $iUserId, $iMemberId, '', 'true');
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    if ($currencycode == "" || $currencycode == NULL) {
        $currencycode = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }

    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $currencycode, '', 'true');

    $sql = "SELECT * from trips WHERE iTripId = '" . $iTripId . "'";
    $tripData = $obj->MySQLSelect($sql);

    $priceRatio = $tripData[0]['fRatio_' . $currencycode];

    $returnArr = array_merge($tripData[0], $returnArr);

    if ($tripData[0]['iUserPetId'] > 0) {
        $petDetails_arr = get_value('user_pets', 'iPetTypeId,vTitle as PetName,vWeight as PetWeight, tBreed as PetBreed, tDescription as PetDescription', 'iUserPetId', $tripData[0]['iUserPetId'], '', '');
    } else {
        $petDetails_arr = array();
    }

    if (count($petDetails_arr) > 0) {
        $petTypeName = get_value('pet_type', 'vTitle_' . $userlangcode, 'iPetTypeId', $petDetails_arr[0]['iPetTypeId'], '', 'true');
        $returnArr['PetDetails']['PetName'] = $petDetails_arr[0]['PetName'];
        $returnArr['PetDetails']['PetWeight'] = $petDetails_arr[0]['PetWeight'];
        $returnArr['PetDetails']['PetBreed'] = $petDetails_arr[0]['PetBreed'];
        $returnArr['PetDetails']['PetDescription'] = $petDetails_arr[0]['PetDescription'];
        $returnArr['PetDetails']['PetTypeName'] = $petTypeName;
    } else {
        $returnArr['PetDetails']['PetName'] = '';
        $returnArr['PetDetails']['PetWeight'] = '';
        $returnArr['PetDetails']['PetBreed'] = '';
        $returnArr['PetDetails']['PetDescription'] = '';
        $returnArr['PetDetails']['PetTypeName'] = '';
    }

    /* User Wallet Information */
    $returnArr['UserDebitAmount'] = strval($tripData[0]['fWalletDebit']);
    /* User Wallet Information */

    $vVehicleType = get_value('vehicle_type', "vVehicleType_" . $userlangcode, 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
    $vVehicleTypeLogo = get_value('vehicle_type', "vLogo", 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
    $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
    $vVehicleCategory = get_value('vehicle_category', 'vCategory_' . $userlangcode, 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');


    $TripTime = date('h:iA', strtotime($tripData[0]['tTripRequestDate']));
    $tTripRequestDate = date('dS M \a\t h:i a', strtotime($tripData[0]['tTripRequestDate']));
    $tStartDate = $tripData[0]['tStartDate'];
    $tEndDate = $tripData[0]['tEndDate'];

    $totalTime = 0;
    $hours = dateDifference($tStartDate, $tEndDate, '%h');
    $minutes = dateDifference($tStartDate, $tEndDate, '%i');
    $seconds = dateDifference($tStartDate, $tEndDate, '%s');
    if ($hours > 0) {
        $totalTime = $hours * 60;
    }
    if ($minutes > 0) {
        $totalTime = $totalTime + $minutes;
    }
    $totalTime = $totalTime . ":" . $seconds . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
    if ($totalTime < 1) {
        $totalTime = $seconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
    }


    /////////////////////////////////////
    // add by seyyed amir for fixed fare
    $typeOfFare = $generalobj->getConfigurations("configurations", "TYPE_OF_FARE_CALCULATION");
    if ($typeOfFare == "Fixed" && $tripData[0]['fGDtime'] != '0') {
        $fGDtime = round($tripData[0]['fGDtime'], 2);
        $totalTime = $fGDtime . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
    }

    //if(function_exists("TLOG")) TLOG("TT:" . $totalTime);

    if ($eUserType == "Passenger") {
        $TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Driver"', 'true');
        $returnArr['vDriverImage'] = get_value('register_driver', 'vImage', 'iTripId', $tripData[0]['iDriverId'], '', 'true');
        $returnArr['carTypeName'] = $vVehicleType;
        $returnArr['carImageLogo'] = $vVehicleTypeLogo;
        $driverDetailArr = get_value('register_driver', '*', 'iDriverId', $tripData[0]['iDriverId']);
    } else {
        $TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Passenger"', 'true');
        $passgengerDetailArr = get_value('register_user', '*', 'iUserId', $tripData[0]['iUserId']);
    }

    if ($TripRating == "" || $TripRating == NULL) {
        $TripRating = "0";
    }

    $iFare = $tripData[0]['iFare'];
    $fPricePerKM = $tripData[0]['fPricePerKM'] * $priceRatio;
    $iBaseFare = $tripData[0]['iBaseFare'] * $priceRatio;
    $fPricePerMin = $tripData[0]['fPricePerMin'] * $priceRatio;
    $fCommision = $tripData[0]['fCommision'];
    $fDistance = $tripData[0]['fDistance'];
    $vDiscount = $tripData[0]['vDiscount']; // 50 $
    $fDiscount = $tripData[0]['fDiscount']; // 50
    $fMinFareDiff = $tripData[0]['fMinFareDiff'] * $priceRatio;
    $fWalletDebit = $tripData[0]['fWalletDebit'];
    $fSurgePriceDiff = $tripData[0]['fSurgePriceDiff'] * $priceRatio;
    $fTripGenerateFare = $tripData[0]['fTripGenerateFare'] * $priceRatio;
    $fPickUpPrice = $tripData[0]['fPickUpPrice'];
    $fNightPrice = $tripData[0]['fNightPrice'];
    $fTipPrice = $tripData[0]['fTipPrice'] * $priceRatio;

    $returnArr['vVehicleType'] = $vVehicleType;
    $returnArr['vVehicleCategory'] = $vVehicleCategory;
    $returnArr['TripTime'] = $TripTime;
    $returnArr['ConvertedTripRequestDate'] = $tTripRequestDate;
    $returnArr['FormattedTripDate'] = $tTripRequestDate;
    $returnArr['tTripRequestDate'] = $tTripRequestDate;
    $returnArr['TripTimeInMinutes'] = $totalTime;
    $returnArr['TripRating'] = $TripRating;
    $returnArr['CurrencySymbol'] = $currencySymbol;
    $returnArr['TripFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iTripId'] = $tripData[0]['iTripId'];
    $returnArr['vTripPaymentMode'] = $tripData[0]['vTripPaymentMode'];

    $originalFare = $fTripGenerateFare;

    if ($eUserType == "Passenger") {
        $iFare = $iFare;
    } else {
        $iFare = $tripData[0]['fTripGenerateFare'] - $fCommision;
    }
    $surgePrice = 1;
    if ($tripData[0]['fPickUpPrice'] > 1) {
        $surgePrice = $tripData[0]['fPickUpPrice'];
    } else {
        $surgePrice = $tripData[0]['fNightPrice'];
    }
    $SurgePriceFactor = strval($surgePrice);

    $returnArr['TripFareOfMinutes'] = formatNum($tripData[0]['fPricePerMin'] * $priceRatio);
    $returnArr['TripFareOfDistance'] = formatNum($tripData[0]['fPricePerKM'] * $priceRatio);
    $returnArr['iFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iOriginalFare'] = formatNum($originalFare * $priceRatio);
    // print_r($priceRatio); exit;
    $returnArr['TotalFare'] = formatNum($iFare * $priceRatio);
    $returnArr['fPricePerKM'] = formatNum($fPricePerKM);
    $returnArr['iBaseFare'] = formatNum($iBaseFare);
    $returnArr['fPricePerMin'] = formatNum($fPricePerMin);
    $returnArr['fCommision'] = formatNum($fCommision * $priceRatio);
    $returnArr['fDistance'] = formatNum($fDistance);
    $returnArr['fDiscount'] = formatNum($fDiscount * $priceRatio);
    $returnArr['fMinFareDiff'] = formatNum($fMinFareDiff);
    $returnArr['fWalletDebit'] = formatNum($fWalletDebit * $priceRatio);
    $returnArr['fSurgePriceDiff'] = formatNum($fSurgePriceDiff);
    $returnArr['fTripGenerateFare'] = formatNum($fTripGenerateFare);
    $returnArr['fTipPrice'] = formatNum($fTipPrice);
    $returnArr['SurgePriceFactor'] = $SurgePriceFactor;


    $iDriverId = $tripData[0]['iDriverId'];
    $driverDetails = get_value('register_driver', '*', 'iDriverId', $iDriverId);
    $driverDetails[0]['vImage'] = ($driverDetails[0]['vImage'] != "" && $driverDetails[0]['vImage'] != "NONE") ? "3_" . $driverDetails[0]['vImage'] : "";
    $returnArr['DriverDetails'] = $driverDetails[0];
    //  print_r($returnArr['iOriginalFare']);  exit;

    $iUserId = $tripData[0]['iUserId'];
    $passengerDetails = get_value('register_user', '*', 'iUserId', $iUserId);
    $passengerDetails[0]['vImgName'] = ($passengerDetails[0]['vImgName'] != "" && $passengerDetails[0]['vImgName'] != "NONE") ? "3_" . $passengerDetails[0]['vImgName'] : "";
    $returnArr['PassengerDetails'] = $passengerDetails[0];

    $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
    $sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverVehicleId='" . $iDriverVehicleId . "' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId`";
    $vehicleDetailsArr = $obj->MySQLSelect($sql);
    $vehicleDetailsArr[0]['vModel'] = $vehicleDetailsArr[0]['vTitle'];
    $returnArr['DriverCarDetails'] = $vehicleDetailsArr[0];

    if ($eUserType == "Passenger") {
        $tripFareDetailsArr = array();
        $tripFareDetailsArr[0][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $returnArr['iBaseFare'] . ' ' . $currencySymbol;

        //Added by seyyed amir
        // اگر فاصله 0 کیلومتر بود  نوشته شود
        // کمتر از1
        $fDistanceTxt = $returnArr['fDistance'] == 0 ? $languageLabelsArr['LBL_LESS_THAN_TXT'] . " 1" : $returnArr['fDistance'];
        /////////////////////////////////////
        $tripFareDetailsArr[1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $fDistanceTxt . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = $returnArr['TripFareOfDistance'] . ' ' . $currencySymbol;
        $tripFareDetailsArr[2][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = $returnArr['TripFareOfMinutes'] . ' ' . $currencySymbol;
        $i = 2;
        if ($fMinFareDiff > 0) {
            $minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
            $minimamfare = formatNum($minimamfare);
            $tripFareDetailsArr[$i + 1][$minimamfare . ' ' . $currencySymbol . " " . $languageLabelsArr['LBL_MINIMUM']] = $returnArr['fMinFareDiff'] . ' ' . $currencySymbol;
            $returnArr['TotalMinFare'] = $minimamfare;
            $i++;
        }
        if ($fSurgePriceDiff > 0) {
            $normalfare = $fTripGenerateFare - $fSurgePriceDiff;
            $normalfare = formatNum($normalfare);
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = $normalfare . ' ' . $currencySymbol;
            $i++;
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $returnArr['fSurgePriceDiff'] . ' ' . $currencySymbol;
            $i++;
        }
        if ($fDiscount > 0) {
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = $returnArr['fDiscount'] . "- " . ' ' . $currencySymbol;
            $i++;
        }
        if ($fWalletDebit > 0) {
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = $returnArr['fWalletDebit'] . "- " . ' ' . $currencySymbol;
            $i++;
        }

        if ($fTipPrice > 0) {
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIP_AMOUNT']] = $returnArr['fTipPrice'] . ' ' . $currencySymbol;
            $i++;
        }


        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $returnArr['iFare'] . ' ' . $currencySymbol;
        $i++;


        // added by seyyed amir
        // total amount
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TOTAL_TXT']] = $returnArr['fTripGenerateFare'] . ' ' . $currencySymbol;
        $i++;

        $returnArr['FareSubTotal'] = $returnArr['TotalFare'] . ' ' . $currencySymbol;
        $returnArr['FareDetailsNewArr'] = $tripFareDetailsArr;
        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }
        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;
    } else {

        //Added by seyyed amir
        // اگر فاصله 0 کیلومتر بود  نوشته شود
        // کمتر از1
        $fDistanceTxt = $returnArr['fDistance'] == 0 ? $languageLabelsArr['LBL_LESS_THAN_TXT'] . " 1" : $returnArr['fDistance'];
        /////////////////////////////////////

        $tripFareDetailsArr = array();
        $tripFareDetailsArr[0][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $returnArr['iBaseFare'] . ' ' . $currencySymbol;
        $tripFareDetailsArr[1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $fDistanceTxt . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = $returnArr['TripFareOfDistance'] . ' ' . $currencySymbol;
        $tripFareDetailsArr[2][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = $returnArr['TripFareOfMinutes'] . ' ' . $currencySymbol;
        $i = 2;
        if ($fMinFareDiff > 0) {
            $minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
            $minimamfare = formatNum($minimamfare);
            $tripFareDetailsArr[$i + 1][$minimamfare . ' ' . $currencySymbol . " " . $languageLabelsArr['LBL_MINIMUM']] = $returnArr['fMinFareDiff'] . ' ' . $currencySymbol;
            $returnArr['TotalMinFare'] = $minimamfare;
            $i++;
        }
        if ($fSurgePriceDiff > 0) {
            $normalfare = $fTripGenerateFare - $fSurgePriceDiff;
            $normalfare = formatNum($normalfare);
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = $normalfare . ' ' . $currencySymbol;
            $i++;
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $returnArr['fSurgePriceDiff'] . ' ' . $currencySymbol;
            $i++;
        }
        if ($fDiscount > 0) {
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = $returnArr['fDiscount'] . "- " . ' ' . $currencySymbol;
            $i++;
        }
        if ($fWalletDebit > 0) {
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = $returnArr['fWalletDebit'] . "- " . ' ' . $currencySymbol;
            $i++;
        }
        if ($fTipPrice > 0) {
            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIP_AMOUNT']] = $returnArr['fTipPrice'] . ' ' . $currencySymbol;
            $i++;
        }

        // added by seyyed amir
        // total amount
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TOTAL_TXT']] = $returnArr['fTripGenerateFare'] . ' ' . $currencySymbol;
        $i++;

        $returnArr['FareSubTotal'] = $returnArr['TripFare'] . ' ' . $currencySymbol;
        $returnArr['FareDetailsNewArr'] = $tripFareDetailsArr;
        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }
        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_Commision']] = "-" . $returnArr['fCommision'] . $currencySymbol;
        $i++;
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_EARNED_AMOUNT']] = $returnArr['iFare'] . $currencySymbol;
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;
    }
    $returnArr['FareSubTotal'] = $returnArr['TripFare'] . ' ' . $currencySymbol;
    //passengertripfaredetails

    $HistoryFareDetailsArr = array();
    foreach ($tripFareDetailsArr as $inner) {
        $HistoryFareDetailsArr = array_merge($HistoryFareDetailsArr, $inner);
    }
    $returnArr['HistoryFareDetailsArr'] = $HistoryFareDetailsArr;


    //drivertripfarehistorydetails
    //echo "<pre>";print_r($returnArr);echo "<pre>";print_r($tripData);exit;
    #Logger($returnArr);
    return $returnArr;
}

function formatNum($number)
{
    return strval(number_format($number, 0));
}

function getUserRatingAverage($iMemberId, $eUserType = "Passenger")
{
    global $obj, $generalobj;
    if ($eUserType == "Passenger") {
        $iUserId = "iDriverId";
        $checkusertype = "Passenger";
    } else {
        $iUserId = "iUserId";
        $checkusertype = "Driver";
    }

    $usertotaltrips = get_value("trips", "iTripId", $iUserId, $iMemberId);
    if (count($usertotaltrips) > 0) {
        for ($i = 0; $i < count($usertotaltrips); $i++) {
            $iTripId .= $usertotaltrips[$i]['iTripId'] . ",";
        }

        $iTripId_str = substr($iTripId, 0, -1);
        //echo  $iTripId_str;exit;
        $sql = "SELECT count(iRatingId) as ToTalTrips, SUM(vRating1) as ToTalRatings from ratings_user_driver WHERE iTripId IN (" . $iTripId_str . ") AND eUserType = '" . $checkusertype . "'";
        $result_ratings = $obj->MySQLSelect($sql);
        $ToTalTrips = $result_ratings[0]['ToTalTrips'];
        $ToTalRatings = $result_ratings[0]['ToTalRatings'];
        $average_rating = round($ToTalRatings / $ToTalTrips, 2);
    } else {
        $average_rating = 0;
    }
    return $average_rating;
}

function deliverySmsToReceiver($iTripId)
{
    global $obj, $generalobj, $tconfig;

    $sql = "SELECT * from trips WHERE iTripId = '" . $iTripId . "'";
    $tripData = $obj->MySQLSelect($sql);

    $SenderName = get_value("register_user", "vName,vLastName", "iUserId", $tripData[0]['iUserId']);
    $SenderName = $SenderName[0]['vName'] . " " . $SenderName[0]['vLastName'];
    $delivery_address = $tripData[0]['tDaddress'];
    $vDeliveryConfirmCode = $tripData[0]['vDeliveryConfirmCode'];
    $page_link = $tconfig['tsite_url'] . "trip_tracking.php?iTripId=" . $iTripId;
    $page_link = get_tiny_url($page_link);

    $message_deliver = $SenderName . " برای شما بسته ای به آدرس زیر ارسال کرده است." . $delivery_address . ". به محض دریافت بسته کد زیر را به ارسال کننده ارایه کنید. کد تایید:" . $vDeliveryConfirmCode . ". برای رهگیری بسته لینک زیر را کلیک کنید" . $page_link;

    //echo $message_deliver;exit;
    return $message_deliver;
}

function get_tiny_url($url)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function addToUserRequest($iUserId, $iDriverId, $message, $iMsgCode)
{
    global $obj;
    $data['iUserId'] = $iUserId;
    $data['iDriverId'] = $iDriverId;
    $data['tMessage'] = $message;
    $data['iMsgCode'] = $iMsgCode;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");

    $dataId = $obj->MySQLQueryPerform("passenger_requests", $data, 'insert');

    return $dataId;
}

function addToDriverRequest($iDriverId, $iUserId, $iTripId, $eStatus, $iMsgCode = 0)
{
    global $obj;
    $data['iDriverId'] = $iDriverId;
    $data['iUserId'] = $iUserId;
    $data['iTripId'] = $iTripId;
    $data['iMsgCode'] = $iMsgCode;
    $data['eStatus'] = $eStatus;
    $data['tDate'] = @date("Y-m-d H:i:s");

    $id = $obj->MySQLQueryPerform("driver_request", $data, 'insert');

    return $id;
}

function UpdateDriverRequest($iDriverId, $iUserId, $iTripId, $eStatus)
{
    global $obj;

    $sql = "SELECT * FROM `driver_request` WHERE iDriverId = '" . $iDriverId . "' AND iUserId = '" . $iUserId . "' AND iTripId = '0' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_sql = $obj->MySQLSelect($sql);
    $request_count = count($db_sql);

    if ($request_count > 0) {
        $where = " iDriverRequestId = '" . $db_sql[0]['iDriverRequestId'] . "'";
        $Data_Update['eStatus'] = $eStatus;
        $Data_Update['tDate'] = @date("Y-m-d H:i:s");
        $Data_Update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("driver_request", $Data_Update, 'update', $where);
    }

    return $request_count;
}

function UpdateDriverRequestByMsgCode($iMsgCode, $eStatus)
{
    global $obj;

    $sql = "SELECT * FROM `driver_request` WHERE `iMsgCode` = '" . $iMsgCode . "' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_sql = $obj->MySQLSelect($sql);
    $request_count = count($db_sql);

    if ($request_count > 0) {
        $where = " iDriverRequestId = '" . $db_sql[0]['iDriverRequestId'] . "'";
        $Data_Update['eStatus'] = $eStatus;
        $id = $obj->MySQLQueryPerform("driver_request", $Data_Update, 'update', $where);
    }

    return $request_count;
}

function UpdateDriverRequestByMsgAndDriver($iMsgCode, $iDriverId, $eStatus)
{
    global $obj;

    $sql = "SELECT * FROM `driver_request` WHERE `iDriverId` = '{$iDriverId}' AND  `iMsgCode` = '{$iMsgCode}' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_sql = $obj->MySQLSelect($sql);
    //TLOG($sql);
    $request_count = count($db_sql);

    if ($request_count > 0) {
        $where = " iDriverRequestId = '" . $db_sql[0]['iDriverRequestId'] . "'";
        $Data_Update['eStatus'] = $eStatus;
        $id = $obj->MySQLQueryPerform("driver_request", $Data_Update, 'update', $where);
    }

    return $request_count;
}

function SavarProcessReferrals($iTripId)
{
    global $obj;

    //if(function_exists('Logger')) Logger("SavarProcessReferrals TripId = " . $iTripId);

    //if(function_exists('TLOG')) TLOG("SavarProcessReferrals TripId = " . $iTripId);
    // گرفتن اطلاعات مورد نیاز سفر
    $sql = "SELECT 	iTripId, vRideNo, iUserId, iDriverId, tTripRequestDate,	iVehicleTypeId,	iAreaId, eCancelled FROM `trips` WHERE iTripId = $iTripId AND eCancelled = 'NO' AND fDiscount = 0";

    // add fDiscount != 0 baraye nadide gereftana safarhaye ba code takhfif

    $res = $obj->MySQLSelect($sql);
    $request_count = count($res);

    //if(function_exists('TLOG')) TLOG($res);

    if ($request_count > 0) {
        $trip = $res[0];

        $iUserId = $trip['iUserId'];
        $iDriverId = $trip['iDriverId'];
        $tTripRequestDate = reset(explode(' ', $trip['tTripRequestDate']));
        $iVehicleTypeId = $trip['iVehicleTypeId'];
        $iAreaId = $trip['iAreaId'];


        // Process for Rider

        // گرفتن لیست هدایای فعال برای این سفر
        $sql = "SELECT 	* FROM `savar_referrals` WHERE sForUserType = 'Rider' AND sActive = 'Yes' AND sAreaId IN (0,$iAreaId) AND sVehicleTypeId IN (0,$iVehicleTypeId) AND sStartDate <= '$tTripRequestDate'  AND sExpireDate >= '$tTripRequestDate' ORDER BY sAreaId DESC";

        $refs = $obj->MySQLSelect($sql);
        $refs_count = count($refs);


        //if(function_exists('TLOG')) TLOG($refs);

        //if(function_exists('Logger')) Logger($sql);
        //if(function_exists('Logger')) Logger($refs);


        // گرفتن اطلاعات مسافر
        $followerUserId = $iUserId;
        $userData = get_value('register_user', 'iRefUserId,eRefProcess,tRegistrationDate', 'iUserId', $iUserId, " AND eRefType = 'Rider' ", '');

        $followingUserId = isset($userData[0]['iRefUserId']) ? $userData[0]['iRefUserId'] : 0;
        $refProcess = isset($userData[0]['eRefProcess']) ? $userData[0]['eRefProcess'] : '';
        $tRegistrationDate = isset($userData[0]['tRegistrationDate']) ? reset(explode(' ', $userData[0]['tRegistrationDate'])) : '';


        #if(function_exists('TLOG')) TLOG($userData);

        if ($refs_count > 0 && $followingUserId != '' && intval($followingUserId) > 0
            && $refProcess != '' && $refProcess == 'No' && $tRegistrationDate != '') {
            // در صورتی که هدیه فعالی وجود داشته باشد
            // این مسافر با کد دعوت وارد شده باشد
            // و قبلا پردازش هدیه برایش انجام نشده باشد

            #if(function_exists('TLOG')) TLOG("بررسی هدایا");
            // اکنون تمام هدایای فعال بررسی می شوند
            for ($ri = 0; $ri < $refs_count; $ri++) {
                $refs_proc = $refs[$ri];

                $_sAreaId = $refs_proc['sAreaId'];
                $_sVehcleTypeId = $refs_proc['sVehicleTypeId'];
                $_sTripCount = $refs_proc['sTripCount'];
                $_sLimitDay = $refs_proc['sLimitDay'];
                $_sStartDate = $refs_proc['sStartDate'];

                $sql = "SELECT iTripId FROM trips WHERE iUserId = $iUserId ";

                if ($_sAreaId != 0) {
                    $sql .= " AND iAreaId = $_sAreaId ";
                }

                if ($_sVehcleTypeId != 0) {
                    $sql .= " AND iVehicleTypeId = $_sVehcleTypeId ";
                }

                if ($_sLimitDay > 0) {
                    $_startDate = @date('Y-m-d 00:00:00', strtotime("-$_sLimitDay days"));
                    $sql .= " AND tTripRequestDate >= '$_startDate' ";
                } else {

                    $sql .= " AND tTripRequestDate >= '$_sStartDate 00:00:00' ";
                }


                $user_trips = $obj->MySQLSelect($sql);

                //if(function_exists('TLOG')) TLOG($sql);
                //if(function_exists('TLOG')) TLOG($user_trips);

                if (count($user_trips) >= $_sTripCount) {

                    $_sFollowingAmount = $refs_proc['sFollowingAmount'];
                    $_sFollowerAmount = $refs_proc['sFollowerAmount'];

                    if (function_exists('Logger')) Logger("Referred : $followerUserId Amount: $_sFollowerAmount AND Referral : $followingUserId  Amount: $_sFollowingAmount");


                    if ($_sFollowerAmount > 0)
                        AddReferralAmount('Rider', $followerUserId, $_sFollowerAmount, $iTripId);

                    if ($_sFollowingAmount > 0)
                        AddReferralAmount('Rider', $followingUserId, $_sFollowingAmount, $iTripId);

                    $sql_update = "UPDATE `register_user` SET `eRefProcess` = 'Yes' WHERE `iUserId` = $followerUserId;";

                    $obj->sql_query($sql_update);
                }
            }
        }


        //////////////////////////////////////////////////
        //////////////////////////////////////////////////
        // Process for Driver

        // گرفتن لیست هدایای فعال برای این سفر
        $sql = "SELECT 	* FROM `savar_referrals` WHERE sForUserType = 'Driver' AND sActive = 'Yes' AND sAreaId IN (0,$iAreaId) AND sVehicleTypeId IN (0,$iVehicleTypeId) AND sExpireDate >= $tTripRequestDate ORDER BY sAreaId DESC";

        $refs = $obj->MySQLSelect($sql);
        $refs_count = count($refs);

        // گرفتن اطلاعات راننده
        $followerDriverId = $iDriverId;
        $userData = get_value('register_driver', 'iRefUserId,eRefProcess,tRegistrationDate', 'iDriverId', $iDriverId, " AND eRefType = 'Driver' ", '');

        $followingDriverId = isset($userData[0]['iRefUserId']) ? $userData[0]['iRefUserId'] : 0;
        $refProcess = isset($userData[0]['eRefProcess']) ? $userData[0]['eRefProcess'] : '';
        $tRegistrationDate = isset($userData[0]['tRegistrationDate']) ? reset(explode(' ', $userData[0]['tRegistrationDate'])) : '';

        if ($refs_count > 0 && $followingDriverId != '' && intval($followingDriverId) > 0
            && $refProcess != '' && $refProcess == 'No' && $tRegistrationDate != '') {
            // در صورتی که هدیه فعالی وجود داشته باشد
            // این مسافر با کد دعوت وارد شده باشد
            // و قبلا پردازش هدیه برایش انجام نشده باشد


            // اکنون تمام هدایای فعال بررسی می شوند
            for ($ri = 0; $ri < $refs_count; $ri++) {
                $refs_proc = $refs[$ri];

                $_sAreaId = $refs_proc['sAreaId'];
                $_sVehcleTypeId = $refs_proc['sVehicleTypeId'];
                $_sTripCount = $refs_proc['sTripCount'];
                $_sLimitDay = $refs_proc['sLimitDay'];

                $sql = "SELECT iTripId FROM trips WHERE iDriverId = $iDriverId ";

                if ($_sAreaId != 0) {
                    $sql .= " AND iAreaId = $_sAreaId ";
                }

                if ($_sVehcleTypeId != 0) {
                    $sql .= " AND iVehicleTypeId = $_sVehcleTypeId ";
                }

                if ($_sLimitDay > 0) {
                    $_startDate = @date('Y-m-d 00:00:00', strtotime("-$_sLimitDay days"));
                    $sql .= " AND tTripRequestDate >= '$_startDate' ";
                }


                $user_trips = $obj->MySQLSelect($sql);

                //if(function_exists('TLOG')) TLOG($sql);
                //if(function_exists('TLOG')) TLOG($user_trips);

                if (count($user_trips) >= $_sTripCount) {
                    $_sFollowingAmount = $refs_proc['sFollowingAmount'];
                    $_sFollowerAmount = $refs_proc['sFollowerAmount'];

                    if (function_exists('Logger')) Logger("Referred : $followerDriverId Amount: $_sFollowerAmount AND Referral : $followingDriverId  Amount: $_sFollowingAmount");

                    if ($_sFollowerAmount > 0)
                        AddReferralAmount('Driver', $followerDriverId, $_sFollowerAmount, $iTripId);
                    if ($_sFollowingAmount)
                        AddReferralAmount('Driver', $followingDriverId, $_sFollowingAmount, $iTripId);


                    $sql_update = "UPDATE `register_driver` SET `eRefProcess` = 'Yes' WHERE `iDriverId` = $followerDriverId;";

                    $obj->sql_query($sql_update);
                }
            }
        }


    }

}

function AddReferralAmount($type, $uid, $amount, $iTripId = 0)
{
    global $generalobj;

    $REFERRAL_AMOUNT = $amount;
    $eFor = "Referrer";
    $tDescription = "شارژ هدیه برای معرفی سوار";
    $dDate = Date('Y-m-d H:i:s');
    $ePaymentStatus = "Unsettelled";
    $generalobj->InsertIntoUserWallet($uid, $type, $REFERRAL_AMOUNT, 'Credit', $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);

}


?>
