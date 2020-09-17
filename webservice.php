<?php

error_reporting(0);

ob_start("ob_gzhandler");

//$start = microtime(true);
include_once('common.php');

include_once(TPATH_CLASS . 'configuration.php');

//require_once('assets/libraries/stripe/config.php');
//require_once('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
require_once('assets/libraries/pubnub/autoloader.php');
include_once(TPATH_CLASS . 'Imagecrop.class.php');
include_once(TPATH_CLASS . 'twilio/Services/Twilio.php');
require_once(TPATH_CLASS . 'savar/jalali_date.php');
require_once(TPATH_CLASS . 'savar/class.telegrambot.php');
include_once('generalFunctions.php');
include_once('send_invoice_receipt.php');

//var_dump(cab_bookink_exec(51));


//$returnArr['Action'] ="0";
//$returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
//echo json_encode($returnArr);exit;


/* add Logget By Seyyed AMir */
//Logger($_REQUEST);
/* creating objects */
$thumb = new thumbnail;

/* Get variables */
$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

/* Paypal supported Currency Codes */
$currency_supported_paypal = ['AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'TRY', 'USD'];

$demo_site_msg = "Edit / Delete Record Feature has been disabled on the Demo Application. This feature will be enabled on the main script we will provide you.";

if ($type == '') {
    $type = isset($_REQUEST['function']) ? trim($_REQUEST['function']) : '';
}
$lang_label = [];
$lang_code = '';
/* general fucntions */

/* To Check App Version */
$appVersion = isset($_REQUEST['AppVersion']) ? trim($_REQUEST['AppVersion']) : '';

if ($appVersion != "") {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if ($UserType == "Passenger") {
        $newAppVersion = $generalobj->getConfigurations("configurations", "PASSENGER_ANDROID_APP_VERSION");
    } else {
        $newAppVersion = $generalobj->getConfigurations("configurations", "DRIVER_ANDROID_APP_VERSION");
    }
    if ($newAppVersion != $appVersion && $newAppVersion > $appVersion) {
        $returnArr['Action'] = "0";
        $returnArr['isAppUpdate'] = "true";
        $returnArr['message'] = "LBL_NEW_UPDATE_MSG";
        echo json_encode($returnArr);
        exit;
    }
}

if ($type == "checkGetValue") {
    $check_payment = get_value('vehicle_type', '*', '', '');
    // print_r($check_payment);

    $row[0]['VehicleTypes'] = $check_payment;
    echo json_encode($row[0]);
}


if ($type == 'startBooking') {
    if (isset($_GET["debug"])) $booking_test = true;
    else $booking_test = false;

    $cabBookingId = isset($_REQUEST['cabBookingId']) ? $_REQUEST['cabBookingId'] : 0;
    if (intval($cabBookingId) > 0)
        cab_bookink_exec($cabBookingId);
    $type = $_REQUEST['type'];
}

function getPassengerDetailInfo($passengerID, $cityName)
{
    global $generalobj, $obj, $demo_site_msg;

    $where = " iUserId = '" . $passengerID . "'";
    $data_version['iAppVersion'] = "2";
    $obj->MySQLQueryPerform("register_user", $data_version, 'update', $where);


    $sql = "SELECT * FROM `register_user` WHERE iUserId='$passengerID'";
    $row = $obj->MySQLSelect($sql);

    if (count($row) > 0) {
        if ($row[0]['vImgName'] != "" && $row[0]['vImgName'] != "NONE") {
            $row[0]['vImgName'] = "3_" . $row[0]['vImgName'];
        }
        $row[0]['Passenger_Password_decrypt'] = $generalobj->decrypt($row[0]['vPassword']);

        if ($row[0]['eStatus'] != "Active") {
            $returnArr['Action'] = "0";

            if ($row[0]['eStatus'] != "Deleted") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            } else {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            }
            echo json_encode($returnArr);
            exit;
        }

        $TripStatus = $row[0]['vTripStatus'];
        $TripID = $row[0]['iTripId'];

        if ($TripStatus != "NONE") {
            $TripID = $row[0]['iTripId'];
            $row_result_trips = getTripPriceDetails($TripID, $passengerID, "Passenger");

            $row[0]['TripDetails'] = $row_result_trips;

            $row[0]['DriverDetails'] = $row_result_trips['DriverDetails'];


            $row_result_trips['DriverCarDetails']['make_title'] = $row_result_trips['DriverCarDetails']['vMake'];
            $row_result_trips['DriverCarDetails']['model_title'] = $row_result_trips['DriverCarDetails']['vTitle'];
            $row[0]['DriverCarDetails'] = $row_result_trips['DriverCarDetails'];

            $sql = "SELECT vPaymentUserStatus FROM `payments` WHERE iTripId='$TripID'";
            $row_result_payments = $obj->MySQLSelect($sql);

            if (count($row_result_payments) > 0) {

                if ($row_result_payments[0]['vPaymentUserStatus'] != 'approved') {
                    $row[0]['PaymentStatus_From_Passenger'] = "Not Approved";
                } else {
                    $row[0]['PaymentStatus_From_Passenger'] = "Approved";
                }

            } else {

                $row[0]['PaymentStatus_From_Passenger'] = "No Entry";
            }

            $sql = "SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='$TripID'";
            $row_result_ratings = $obj->MySQLSelect($sql);

            if (count($row_result_ratings) > 0) {

                $count_row_rating = 0;
                $ContentWritten = "false";
                while (count($row_result_ratings) > $count_row_rating) {

                    $UserType = $row_result_ratings[$count_row_rating]['eUserType'];

                    if ($UserType == "Passenger") {
                        $ContentWritten = "true";
                        $row[0]['Ratings_From_Passenger'] = "Done";
                    } else if ($ContentWritten == "false") {
                        $row[0]['Ratings_From_Passenger'] = "Not Done";
                    }

                    $count_row_rating++;
                }
            } else {

                $row[0]['Ratings_From_Passenger'] = "No Entry";
            }
        }

        /*if ($TripStatus == "Active" || $TripStatus == "On Going Trip") {

					$TripID = $row[0]['iTripId'];

					$sql = "SELECT * FROM `trips` WHERE iTripId='$TripID'";
					$row_result_trips = $obj->MySQLSelect($sql);

					$vehicleType=$row_result_trips[0]['iVehicleTypeId'];
					$sql = "SELECT vVehicleType FROM `vehicle_type`  WHERE  iVehicleTypeId='$vehicleType'";
					$vehicleType_data = $obj->MySQLSelect($sql);

					$row_result_trips[0]['vVehicleType']=$vehicleType_data[0]['vVehicleType'];

					$Assigned_driverID        = $row_result_trips[0]['iDriverId'];
					$Assigned_driverVehicleID = $row_result_trips[0]['iDriverVehicleId'];
					$row[0]['TripDetails']       = $row_result_trips[0];

					$sql = "SELECT * FROM `register_driver` WHERE iDriverId='$Assigned_driverID'";
					$row_result_driver = $obj->MySQLSelect($sql);

					if($row_result_driver[0]['vImage']!="" && $row_result_driver[0]['vImage']!="NONE"){
						$row_result_driver[0]['vImage']="3_".$row_result_driver[0]['vImage'];
					}

					$row[0]['DriverDetails'] = $row_result_driver[0];


					$sql = "SELECT dv.*, make.vMake AS make_title, model.vTitle model_title FROM `driver_vehicle` dv, make, model
					WHERE dv.iMakeId = make.iMakeId
					AND dv.iModelId = model.iModelId
					AND iDriverVehicleId='$Assigned_driverVehicleID'";
					$row_result_driver_vehicel = $obj->MySQLSelect($sql);


					$row[0]['DriverCarDetails'] = $row_result_driver_vehicel[0];


				}else if($TripStatus == "Not Active"){

					 $sql = "SELECT register_driver.vImage AS driver_img_lastTrip, trips.* FROM `trips` JOIN register_driver ON trips.iDriverId = register_driver.iDriverId  WHERE trips.iTripId='$TripID'";

					$row_result_trip = $obj->MySQLSelect($sql);

					if($row_result_trip[0]['driver_img_lastTrip']!="" && $row_result_trip[0]['driver_img_lastTrip']!="NONE"){
						$row_result_trip[0]['driver_img_lastTrip']="3_".$row_result_trip[0]['driver_img_lastTrip'];
					}

					$row[0]['Last_trip_data']=$row_result_trip[0];

					$sql = "SELECT vPaymentUserStatus FROM `payments` WHERE iTripId='$TripID'";
					$row_result_payments = $obj->MySQLSelect($sql);

					if(count($row_result_payments)>0){

						if($row_result_payments[0]['vPaymentUserStatus']!='approved'){
							$row[0]['PaymentStatus_From_Passenger']="Not Approved";
						}else{
							$row[0]['PaymentStatus_From_Passenger']="Approved";
						}

					}else{

						$row[0]['PaymentStatus_From_Passenger']="No Entry";
					}

					$sql = "SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='$TripID'";
					$row_result_ratings = $obj->MySQLSelect($sql);

					if(count($row_result_ratings)>0){

					 $count_row_rating=0;
					 $ContentWritten="false";
					 while(count($row_result_ratings) > $count_row_rating){

						$UserType=$row_result_ratings[$count_row_rating]['eUserType'];

						if($UserType=="Passenger"){
							$ContentWritten="true";
							$row[0]['Ratings_From_Passenger']="Done";
						}else if($ContentWritten=="false"){
							$row[0]['Ratings_From_Passenger']="Not Done";
						}

						 $count_row_rating++;
					}

					}else{

						$row[0]['Ratings_From_Passenger']="No Entry";
					}
				}   */

        // $vehicleTypes=($obj->MySQLSelect("SELECT * FROM vehicle_type"));
        //$vehicleTypes = get_value('vehicle_type', '*', '', '');

        // vSavarArea = -1 for disable load vehicle in this time
        $vehicleTypes = get_value('vehicle_type', '*', 'vSavarArea', '-1', ' ORDER BY iVehicleTypeId ASC');
        //$vehicleTypes = get_value('vehicle_type', '*', '', '',' ORDER BY iVehicleTypeId ASC');
        //$vehicleTypes = array();

        $vehicle_category = get_value('vehicle_category', 'iVehicleCategoryId, vLogo,vCategory_' . $row[0]['vLang'] . ' as vCategory', 'eStatus', 'Active');

        // $priceRatio=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$row[0]['vCurrencyPassenger']."' ")[0]['Ratio']);
        $priceRatio = get_value('currency', 'Ratio', 'vName', $row[0]['vCurrencyPassenger'], '', 'true');

        for ($i = 0; $i < count($vehicleTypes); $i++) {
            $vehicleTypes[$i]['fPricePerKM'] = round($vehicleTypes[$i]['fPricePerKM'] * $priceRatio, 0);
            $vehicleTypes[$i]['fPricePerMin'] = round($vehicleTypes[$i]['fPricePerMin'] * $priceRatio, 0);
            $vehicleTypes[$i]['iBaseFare'] = round($vehicleTypes[$i]['iBaseFare'] * $priceRatio, 0);
            $vehicleTypes[$i]['fCommision'] = round($vehicleTypes[$i]['fCommision'] * $priceRatio, 0);
            $vehicleTypes[$i]['iMinFare'] = round($vehicleTypes[$i]['iMinFare'] * $priceRatio, 0);
            $vehicleTypes[$i]['FareValue'] = round($vehicleTypes[$i]['fFixedFare'] * $priceRatio, 0);
            $vehicleTypes[$i]['vVehicleType'] = $vehicleTypes[$i]["vVehicleType_" . $row[0]['vLang']];
        }
        $row[0]['VehicleTypes'] = $vehicleTypes;
        $row[0]['VehicleCategory'] = $vehicle_category;

        // $row[0]['PayPalConfiguration']=$generalobj->getConfigurations("configurations","PAYMENT_ENABLED");
        $row[0]['DefaultCurrencySign'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_SIGN");
        $row[0]['DefaultCurrencyCode'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_CODE");
        $row[0]['RESTRICTION_KM_NEAREST_TAXI'] = $generalobj->getConfigurations("configurations", "RESTRICTION_KM_NEAREST_TAXI");
        $row[0]['FACEBOOK_APP_ID'] = $generalobj->getConfigurations("configurations", "FACEBOOK_APP_ID");
        $row[0]['CONFIG_CLIENT_ID'] = $generalobj->getConfigurations("configurations", "CONFIG_CLIENT_ID");
        $row[0]['GOOGLE_SENDER_ID'] = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID");
        $row[0]['DRIVER_ARRIVED_MIN_TIME_PER_MINUTE'] = $generalobj->getConfigurations("configurations", "DRIVER_ARRIVED_MIN_TIME_PER_MINUTE");
        $row[0]['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");
        $row[0]['DRIVER_LOC_FETCH_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "DRIVER_LOC_FETCH_TIME_INTERVAL");
        $row[0]['ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL");
        $row[0]['LOCATION_ACCURACY_METERS'] = $generalobj->getConfigurations("configurations", "LOCATION_ACCURACY_METERS");
        $row[0]['STRIPE_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "STRIPE_PUBLISH_KEY");
        $row[0]['DRIVER_REQUEST_METHOD'] = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
        $row[0]['ENABLE_PUBNUB'] = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
        $row[0]['SITE_POLICE_CONTROL_NUMBER'] = $generalobj->getConfigurations("configurations", "SITE_POLICE_CONTROL_NUMBER");
        $row[0]['PUBNUB_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
        $row[0]['PUBNUB_SUBSCRIBE_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");
        $row[0]['PUBNUB_SECRET_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SECRET_KEY");
        $row[0]['REFERRAL_SCHEME_ENABLE'] = $generalobj->getConfigurations("configurations", "REFERRAL_SCHEME_ENABLE");
        $row[0]['WALLET_ENABLE'] = $generalobj->getConfigurations("configurations", "WALLET_ENABLE");
        $row[0]['APP_TYPE'] = $generalobj->getConfigurations("configurations", "APP_TYPE");
        $row[0]['APP_PAYMENT_MODE'] = $generalobj->getConfigurations("configurations", "APP_PAYMENT_MODE");
        $row[0]['WALLET_FIXED_AMOUNT_1'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_1");
        $row[0]['WALLET_FIXED_AMOUNT_2'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_2");
        $row[0]['WALLET_FIXED_AMOUNT_3'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_3");

        $row[0]['ENABLE_TIP_MODULE'] = $generalobj->getConfigurations("configurations", "ENABLE_TIP_MODULE");
        $row[0]['CUSTOM_MESSAGE_PASSENGER'] = $generalobj->getConfigurations("configurations", "CUSTOM_MESSAGE_PASSENGER");

        // $row[0]['ENABLE_DELIVERY_MODULE']=$generalobj->getConfigurations("configurations","ENABLE_DELIVERY_MODULE");
        $row[0]['ENABLE_DELIVERY_MODULE'] = SITE_TYPE == "Demo" ? $row[0]['eDeliverModule'] : $generalobj->getConfigurations("configurations", "ENABLE_DELIVERY_MODULE");
        $row[0]['PayPalConfiguration'] = $row[0]['ENABLE_DELIVERY_MODULE'] == "Yes" ? "Yes" : $generalobj->getConfigurations("configurations", "PAYMENT_ENABLED");

        // if($row[0]['ENABLE_DELIVERY_MODULE'] == "Yes"){
        // $row[0]['PayPalConfiguration'] = "Yes";
        // }
        $row[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        $row[0]['SITE_TYPE'] = SITE_TYPE;
        $row[0]['RIIDE_LATER'] = RIIDE_LATER;
        $row[0]['PROMO_CODE'] = PROMO_CODE;
        $row[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        $row[0]['CurrencySymbol'] = get_value('currency', 'vSymbol', 'vName', $row[0]['vCurrencyPassenger'], '', 'true');
        $row[0]['LIST_DRIVER_LIMIT_BY_DISTANCE'] = $generalobj->getConfigurations("configurations", "LIST_DRIVER_LIMIT_BY_DISTANCE");

        $row[0]['DESTINATION_UPDATE_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "DESTINATION_UPDATE_TIME_INTERVAL");
        $row[0]['TYPE_OF_FARE_CALCULATION'] = $generalobj->getConfigurations("configurations", "TYPE_OF_FARE_CALCULATION");
        /* fetch value */
        return $row[0];


    } else {

        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

        echo json_encode($returnArr);
        exit;


    }

}

function getDriverDetailInfo($driverId, $fromSignIN = 0)
{

    global $generalobj, $obj, $demo_site_msg;

    $where = " iDriverId = '" . $driverId . "'";
    $data_version['iAppVersion'] = "2";
    $obj->MySQLQueryPerform("register_driver", $data_version, 'update', $where);

    $returnArr = array();

    $sql = "SELECT rd.*,cmp.eStatus as cmpEStatus,(SELECT dv.vLicencePlate From driver_vehicle as dv WHERE rd.iDriverVehicleId != '' AND rd.iDriverVehicleId !='0' AND dv.iDriverVehicleId = rd.iDriverVehicleId) as vLicencePlateNo FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='$driverId' AND cmp.iCompanyId=rd.iCompanyId";

    $sql = "SELECT rd.*,cmp.eStatus as cmpEStatus, (SELECT dv.vLicencePlate From driver_vehicle as dv WHERE rd.iDriverVehicleId != '' AND rd.iDriverVehicleId !='0' AND dv.iDriverVehicleId = rd.iDriverVehicleId) as vLicencePlateNo,  (SELECT dv.vLicencePlate_local From driver_vehicle as dv WHERE rd.iDriverVehicleId != '' AND rd.iDriverVehicleId !='0' AND dv.iDriverVehicleId = rd.iDriverVehicleId) as vLicencePlateLocal FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='$driverId' AND cmp.iCompanyId=rd.iCompanyId";


    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        $Data[0]['Driver_Password_decrypt'] = $generalobj->decrypt($Data[0]['vPassword']);

        if ($Data[0]['vImage'] != "" && $Data[0]['vImage'] != "NONE") {
            $Data[0]['vImage'] = "3_" . $Data[0]['vImage'];
        }

        if ($Data[0]['iDriverVehicleId'] != '' && $Data[0]['iDriverVehicleId'] != '0') {
            $data_vehicle_arr = get_value('driver_vehicle', 'iMakeId, iModelId', 'iDriverVehicleId', $Data[0]['iDriverVehicleId']);
            $Data[0]['vMake'] = get_value('make', 'vMake', 'iMakeId', $data_vehicle_arr[0]['iMakeId'], '', 'true');
            $Data[0]['vModel'] = get_value('model', 'vTitle', 'iModelId', $data_vehicle_arr[0]['iModelId'], '', 'true');
        }
        if ($Data[0]['eStatus'] != "active" || $Data[0]['cmpEStatus'] != "Active") {

            $returnArr['Action'] = "0";

            if ($Data[0]['cmpEStatus'] != "Active") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_COMPANY";
            } else if ($Data[0]['eStatus'] == "Deleted") {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            } else {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER";
            }

            echo json_encode($returnArr);
            exit;

        }

        $TripStatus = $Data[0]['vTripStatus'];

        if ($TripStatus != "NONE") {
            $TripID = $Data[0]['iTripId'];

            $row_result_trips = getTripPriceDetails($TripID, $driverId, "Driver");

            $Data[0]['TripDetails'] = $row_result_trips;


            $Data[0]['PassengerDetails'] = $row_result_trips['PassengerDetails'];

            $sql = "SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='$TripID'";
            $row_result_ratings = $obj->MySQLSelect($sql);

            if (count($row_result_ratings) > 0) {

                $count_row_rating = 0;
                $ContentWritten = "false";
                while (count($row_result_ratings) > $count_row_rating) {

                    $UserType = $row_result_ratings[$count_row_rating]['eUserType'];

                    if ($UserType == "Driver") {
                        $ContentWritten = "true";
                        $Data[0]['Ratings_From_Driver'] = "Done";
                    } else if ($ContentWritten == "false") {
                        $Data[0]['Ratings_From_Driver'] = "Not Done";
                    }

                    $count_row_rating++;
                }

            } else {

                $Data[0]['Ratings_From_Driver'] = "No Entry";
            }

        }

        /*if($TripStatus != "NONE" ){
					$TripID 		=  $Data[0]['iTripId'];

					$sql = "SELECT * FROM `trips` WHERE iTripId='$TripID'";
					$row_result_trips = $obj->MySQLSelect($sql);


					$row_result_trips[0]['TotalFare']=round($row_result_trips[0]['iFare'] * $row_result_trips[0]['fRatioDriver'],1);
                    $row_result_trips[0]['fDiscount']=round($row_result_trips[0]['fDiscount'] * $row_result_trips[0]['fRatioDriver'],1);
					// $row_result_trips[0]['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$row_result_trips[0]['vCurrencyDriver']."' ")[0]['vSymbol']);
					$row_result_trips[0]['CurrencySymbol']=  get_value('currency', 'vSymbol', 'vName', $row_result_trips[0]['vCurrencyDriver'],'','true');
					$row_result_trips[0]['FormattedTripDate']=date('dS M \a\t h:i a',strtotime($row_result_trips[0]['tStartDate']));
					$Assigned_PassengerID = $row_result_trips[0]['iUserId'];
					$Data[0]['TripDetails']=$row_result_trips[0];

					$sql = "SELECT * FROM `register_user` WHERE iUserId='$Assigned_PassengerID'";
					$row_result_passenger  = $obj->MySQLSelect($sql);

					if($row_result_passenger[0]['vImgName']!="" && $row_result_passenger[0]['vImgName']!="NONE"){
						$row_result_passenger[0]['vImgName']="3_".$row_result_passenger[0]['vImgName'];
					}

					$Data[0]['PassengerDetails'] = $row_result_passenger[0];

					$sql = "SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='$TripID'";
					$row_result_ratings = $obj->MySQLSelect($sql);

					if(count($row_result_ratings)>0){

						$count_row_rating=0;
						$ContentWritten="false";
					 while(count($row_result_ratings) > $count_row_rating){

					 $UserType=$row_result_ratings[$count_row_rating]['eUserType'];

						if($UserType == "Driver"){
						$ContentWritten="true";
							$Data[0]['Ratings_From_Driver']="Done";
							}else if($ContentWritten=="false"){
							$Data[0]['Ratings_From_Driver']="Not Done";
						}

						 $count_row_rating++;
					}
						}else{
						$Data[0]['Ratings_From_Driver']="No Entry";
					}
				}    */
        $Data[0]['ABOUT_US_PAGE_DESCRIPTION'] = "";
        // $Data[0]['PayPalConfiguration']=$generalobj->getConfigurations("configurations","PAYMENT_ENABLED");
        $Data[0]['DefaultCurrencySign'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_SIGN");
        $Data[0]['DefaultCurrencyCode'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_CODE");
        $Data[0]['DRIVER_REFER_APP_SHARE_TXT'] = $generalobj->getConfigurations("configurations", "DRIVER_REFER_APP_SHARE_TXT");
        $Data[0]['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");
        $Data[0]['DRIVER_LOC_UPDATE_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "DRIVER_LOC_UPDATE_TIME_INTERVAL");
        $Data[0]['MIN_ANGLE_FOR_LOG_LOCATION_CONFIG'] = $generalobj->getConfigurations("configurations", "MIN_ANGLE_FOR_LOG_LOCATION_CONFIG");
        $Data[0]['MAX_SPEED_FOR_LOG_WAIT_TIME_CONFIG'] = $generalobj->getConfigurations("configurations", "MAX_SPEED_FOR_LOG_WAIT_TIME_CONFIG");
        $Data[0]['ENABLE_PUBNUB'] = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
        $Data[0]['SITE_TYPE'] = SITE_TYPE;
        $Data[0]['RIIDE_LATER'] = RIIDE_LATER;
        $Data[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        $Data[0]['vLicencePlateNo'] = is_null($Data[0]['vLicencePlateNo']) == false ? $Data[0]['vLicencePlateNo'] : '';
        $Data[0]['vLicencePlateLocal'] = is_null($Data[0]['vLicencePlateLocal']) == false ? $Data[0]['vLicencePlateLocal'] : 'IRAN|..|.|...|..';
        $Data[0]['LOCATION_ACCURACY_METERS'] = $generalobj->getConfigurations("configurations", "LOCATION_ACCURACY_METERS");
        $Data[0]['PUBNUB_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
        $Data[0]['PUBNUB_SUBSCRIBE_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");
        $Data[0]['PUBNUB_SECRET_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SECRET_KEY");
        $Data[0]['REFERRAL_SCHEME_ENABLE'] = $generalobj->getConfigurations("configurations", "REFERRAL_SCHEME_ENABLE");
        $Data[0]['WALLET_ENABLE'] = $generalobj->getConfigurations("configurations", "WALLET_ENABLE");
        $Data[0]['APP_TYPE'] = $generalobj->getConfigurations("configurations", "APP_TYPE");
        $Data[0]['APP_PAYMENT_MODE'] = $generalobj->getConfigurations("configurations", "APP_PAYMENT_MODE");
        $Data[0]['STRIPE_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "STRIPE_PUBLISH_KEY");
        $Data[0]['WALLET_FIXED_AMOUNT_1'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_1");
        $Data[0]['WALLET_FIXED_AMOUNT_2'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_2");
        $Data[0]['WALLET_FIXED_AMOUNT_3'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_3");
        $Data[0]['ENABLE_TIP_MODULE'] = $generalobj->getConfigurations("configurations", "ENABLE_TIP_MODULE");
        $Data[0]['ILLEGAL_APPLICATION_LIST'] = $generalobj->getConfigurations("configurations", "ILLEGAL_APPLICATION_LIST");
        $Data[0]['ENABLE_DELIVERY_MODULE'] = SITE_TYPE == "Demo" ? $Data[0]['eDeliverModule'] : $generalobj->getConfigurations("configurations", "ENABLE_DELIVERY_MODULE");
        $Data[0]['PayPalConfiguration'] = $Data[0]['ENABLE_DELIVERY_MODULE'] == "Yes" ? "Yes" : $generalobj->getConfigurations("configurations", "PAYMENT_ENABLED");
        // $Data[0]['CurrencyList']=($obj->MySQLSelect("SELECT * FROM currency"));
        $Data[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');

        //Mehrshad Added
//        $filei = "../app/admin/Modules/driver_max_owe.txt";
//        $max_owe = file_get_contents($filei);
        /*
         * دریافت مقدار حداکثر بدهی راننده از جدول تتنظیمات در
         * پایگاه داده
         */
        $max_owe = $generalobj->getConfigurations("configurations", "driver_max_owe");
        if ($max_owe == '') {

            $max_owe = '10000';
        }
        $Data[0]['max_owe'] = $max_owe;
        $Data[0]['max_owe'] = str_replace("\n", "", $max_owe);

        $Data[0]['user_available_balance'] = "" . $generalobj->get_user_available_balance($driverId, 'Driver') . "";
        require_once(TPATH_CLASS . "class.general_admin.php");
        $generalobjAdmin = new General_admin();
        $Data[0]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($driverId, '');
        //Mehrshad Added

        $Data[0]['CUSTOM_MESSAGE_DRIVER'] = $generalobj->getConfigurations("configurations", "CUSTOM_MESSAGE_DRIVER");
        // if($fromSignIN == 1){
        // $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
        // $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


        // if($Data_checkLangCode[0]['vCode'] != $Data[0]['vLang']){
        // $Data[0]['changeLangCode'] ="Yes";
        // $Data[0]['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'],"1");
        // }else{
        // $Data[0]['changeLangCode'] ="No";
        // }
        // }
        $str_date = @date('Y-m-d H:i:s', strtotime('-1 minutes'));

        $sql_request = "SELECT * FROM passenger_requests WHERE iDriverId='" . $driverId . "' AND dAddedDate > '" . $str_date . "' ";
        $data_requst = $obj->MySQLSelect($sql_request);

        $Data[0]['CurrentRequests'] = $data_requst;

        return $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

        echo json_encode($returnArr);
        exit;
    }
}


/* function checkDistanceWithGoogleDirections($tripDistance,$startLatitude,$startLongitude,$endLatitude,$endLongitude){
		global $generalobj,$obj;

		$GOOGLE_API_KEY=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$startLatitude.",".$startLongitude."&destination=".$endLatitude.",".$endLongitude."&sensor=false&key=".$GOOGLE_API_KEY;
		try {
			$jsonfile = file_get_contents($url);
		} catch (ErrorException $ex) {
			echo "Failed";
			exit;
		}

		$jsondata = json_decode($jsonfile);
		$distance_google_directions=($jsondata->routes[0]->legs[0]->distance->value)/1000;

		$comparedDist=($distance_google_directions *85)/100;

		if($tripDistance>$comparedDist){
			return $tripDistance;
		}else{
			return round($distance_google_directions,2);
		}
	} */


/* If no type found */
if ($type == '') {
    $result['result'] = 0;
    $result['message'] = 'Required parameter missing.';

    echo json_encode($result);
    exit;
}

/* function getLanguageLabelsArr($lCode = ''){
        global $obj;

		$sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
		$default_label = $obj->MySQLSelect($sql);

		if($lCode == ''){
			$lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode'])?$default_label[0]['vCode']:'EN';
		}


        $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label`  WHERE  `vCode` = '".$lCode."' ";
        $all_label = $obj->MySQLSelect($sql);

        $x = array();
        for($i=0; $i<count($all_label); $i++){
            $vLabel = $all_label[$i]['vLabel'];

			$vValue = $all_label[$i]['vValue'];
            $x[$vLabel]=$vValue;
        }

		$sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_other`  WHERE  `vCode` = '".$lCode."' ";
        $all_label = $obj->MySQLSelect($sql);

        for($i=0; $i<count($all_label); $i++){
            $vLabel = $all_label[$i]['vLabel'];

			$vValue = $all_label[$i]['vValue'];
            $x[$vLabel]=$vValue;
        }
        $x['vCode'] = $lCode; // to check in which languge code it is loading

        return $x;
    } */

/*-------------- For Luggage Lable default and as per user's Prefered language ----------------------- */
if ($type == 'language_label') {
    $lCode = isset($_REQUEST['vCode']) ? clean(strtoupper($_REQUEST['vCode'])) : ''; // User's prefered language

    /* find default language of website set by admin */
    if ($lCode == '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);

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
    $x['vCode'] = $lCode; // to check in which languge code it is loading

    echo json_encode($x);
    exit;
}
##########################################################################
## NEW WEBSERVICE START ##
##########################################################################

##########################################################################
if ($type == 'generalConfigData') {

    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $AppLanguage = isset($_REQUEST["AppLanguage"]) ? $_REQUEST["AppLanguage"] : '';

    if ($AppLanguage == '')
        $DataArr['LanguageLabels'] = getLanguageLabelsArr("", "1");
    else
        $DataArr['LanguageLabels'] = getLanguageLabelsArr($AppLanguage, "1");

    $DataArr['Action'] = "1";

    $defLangValues = get_value('language_master', 'vCode, vGMapLangCode, eDirectionCode as eType', 'eDefault', 'Yes');

    $DataArr['DefaultLanguageValues'] = $defLangValues[0];

    if ($UserType == "Passenger") {

        $DataArr['LINK_FORGET_PASS_PAGE_PASSENGER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_PASSENGER");

        $DataArr['FACEBOOK_APP_ID'] = $generalobj->getConfigurations("configurations", "FACEBOOK_APP_ID");
        $DataArr['CONFIG_CLIENT_ID'] = $generalobj->getConfigurations("configurations", "CONFIG_CLIENT_ID");

    } else {
        $DataArr['LINK_FORGET_PASS_PAGE_DRIVER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_DRIVER");
        $DataArr['LINK_SIGN_UP_PAGE_DRIVER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_SIGN_UP_PAGE_DRIVER");

    }
    $DataArr['LIST_CURRENCY'] = get_value('currency', '*', 'eStatus', 'Active');
    $DataArr['LIST_LANGUAGES'] = get_value('language_master', '*', 'eStatus', 'Active');
    $DataArr['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");
    $DataArr['GOOGLE_SENDER_ID'] = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID");
    $DataArr['ILLEGAL_APPLICATION_LIST'] = $generalobj->getConfigurations("configurations", "ILLEGAL_APPLICATION_LIST");
    $DataArr['REFERRAL_SCHEME_ENABLE'] = $generalobj->getConfigurations("configurations", "REFERRAL_SCHEME_ENABLE");
    $DataArr['WALLET_ENABLE'] = $generalobj->getConfigurations("configurations", "WALLET_ENABLE");

    echo json_encode($DataArr);
    exit;
}
############################ country_list #############################
if ($type == 'countryList') {

    // $sql = "SELECT * FROM  `country` WHERE eStatus = 'Active' ";
    // $all_label = $obj->MySQLSelect($sql);
    // $returnArr['countryList'] = $all_label;
    // echo json_encode($returnArr);
    // exit;

    global $lang_label, $obj, $tconfig, $generalobj;

    $returnArr = array();

    $counter = 0;
    for ($i = 0; $i < 26; $i++) {
        $cahracter = chr(65 + $i);

        $sql = "SELECT COU.* FROM country as COU WHERE COU.eStatus = 'Active' AND COU.vPhoneCode!='' AND COU.vCountryCode!='' AND COU.vCountry LIKE '$cahracter%' ORDER BY COU.vCountry";
        $db_rec = $obj->MySQLSelect($sql);

        if (count($db_rec) > 0) {

            $countryListArr = array();
            $subCounter = 0;
            for ($j = 0; $j < count($db_rec); $j++) {

                $countryListArr[$subCounter] = $db_rec[$j];
                $subCounter++;
            }

            if (count($countryListArr) > 0) {
                $returnArr[$counter]['key'] = $cahracter;
                $returnArr[$counter]['TotalCount'] = count($countryListArr);
                $returnArr[$counter]['List'] = $countryListArr;

                $counter++;

            }
        }

    }

    $countryArr['Action'] = 1;
    $countryArr['totalValues'] = count($returnArr);
    $countryArr['CountryList'] = $returnArr;
    echo json_encode($countryArr);
    exit;
}

###########################################################################

if ($type == "signup") {

    $fbid = isset($_REQUEST["vFbId"]) ? $_REQUEST["vFbId"] : '0';
    $Fname = isset($_REQUEST["vFirstName"]) ? $_REQUEST["vFirstName"] : '';
    $Lname = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $phone_mobile = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $password = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $iGcmRegId = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $phoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $vInviteCode = isset($_REQUEST["vInviteCode"]) ? $_REQUEST["vInviteCode"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $ePhoneVerified = isset($_REQUEST["ePhoneVerified"]) ? $_REQUEST["ePhoneVerified"] : 'No';

    #TLOG($_REQUEST);

    if ($email != '')
        $sql = "SELECT * FROM `register_user` WHERE vEmail = '$email' OR vPhone = '$phone_mobile'";
    else
        $sql = "SELECT * FROM `register_user` WHERE vPhone = '$phone_mobile'";

    #TLOG($sql);

    $check_passenger = $obj->MySQLSelect($sql);


    #TLOG($check_passenger);

    $Password_passenger = $generalobj->encrypt($password);

    if (count($check_passenger) > 0) {
        $returnArr['Action'] = "0";

        if ($email != '' && $email == $check_passenger[0]['vEmail']) {
            $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        } else {
            $returnArr['message'] = "LBL_MOBILE_EXIST";
        }
        echo json_encode($returnArr);
        exit;
    } else {
        $check_inviteCode = "";
        $inviteSuccess = false;
        if ($vInviteCode != "") {
            $check_inviteCode = $generalobj->validationrefercode($vInviteCode);
            if ($check_inviteCode == "" || $check_inviteCode == "0" || $check_inviteCode == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_INVITE_CODE_INVALID";

                #TLOG($returnArr);
                echo json_encode($returnArr);
                exit;
            } else {
                $inviteRes = explode("|", $check_inviteCode);
                $Data_passenger['iRefUserId'] = $inviteRes[0];
                $Data_passenger['eRefType'] = $inviteRes[1];
                $inviteSuccess = true;
            }
        }

        $Data_passenger['vFbId'] = $fbid;
        $Data_passenger['vName'] = $Fname;
        $Data_passenger['vLastName'] = $Lname;
        $Data_passenger['vEmail'] = $email;
        $Data_passenger['vPhone'] = $phone_mobile;

        $Data_passenger['vPassword'] = $Password_passenger;
        $Data_passenger['iGcmRegId'] = $iGcmRegId;
        $Data_passenger['vLang'] = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $Data_passenger['vPhoneCode'] = $phoneCode;
        $Data_passenger['vCountry'] = $CountryCode;
        $Data_passenger['eDeviceType'] = $deviceType;

        $Data_passenger['vCurrencyPassenger'] = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        $Data_passenger['dRefDate'] = Date('Y-m-d H:i:s');
        $Data_passenger['ePhoneVerified'] = $ePhoneVerified;


        //check referer code not duplicated
        $Data_passenger['vRefCode'] = $generalobj->ganaraterefercode("Rider");

        $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'insert');

        $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
        $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


        #Logger($Data_passenger);

        if ($id > 0) {

            // مربوط به رفرال
            // این قسمت به صورت حرفه ای تر
            // در قسمت پایان سفرها پردازش میشود
            /*
				if($inviteSuccess == true){
					$REFERRAL_AMOUNT = $generalobj->getConfigurations("configurations","REFERRAL_AMOUNT");
					$eFor = "Referrer";
					$tDescription = "Referral amount credited";
					$dDate = Date('Y-m-d H:i:s');
					$ePaymentStatus = "Unsettelled";
					$generalobj->InsertIntoUserWallet($Data_passenger['iRefUserId'],$Data_passenger['eRefType'],$REFERRAL_AMOUNT,'Credit',0,$eFor,$tDescription,$ePaymentStatus,$dDate);
				}
				*/
            /*new added*/
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($id);

            echo json_encode($returnArr);

            $maildata['EMAIL'] = $email;
            $maildata['NAME'] = $Fname;
            $maildata['PASSWORD'] = $password;
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);

            exit;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
            exit;
        }
    }

}

######################### isUserExist #############################

if ($type == "isUserExist") {

    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '';
    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';

    if ($Emid != "") {
        $mailStr = " vEmail = '$Emid' OR ";
    } else {
        $mailStr = "";
    }


    if ($fbid != '') {
        $sql = "SELECT vEmail,vPhone,vFbId FROM `register_user` WHERE vEmail = '$Emid' OR vPhone = '$Phone' OR vFbId = '$fbid'";
    } else {
        $sql = "SELECT vEmail,vPhone,vFbId FROM `register_user` WHERE $mailStr vPhone = '$Phone'";
    }

    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {

        $returnArr['Action'] = "0";

        if ($Emid != "" && $Emid == $Data[0]['vEmail']) {
            $returnArr['message'] = "LBL_MOBILE_EXIST";
        } else if ($Phone == $Data[0]['vPhone']) {
            $returnArr['message'] = "LBL_MOBILE_EXIST";
        } else {
            $returnArr['message'] = "LBL_FACEBOOK_ACC_EXIST";
        }
    } else {
        $returnArr['Action'] = "1";
    }

    echo json_encode($returnArr);
}
###########################################################################

if ($type == "signIn") {

    $Emid = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $Password_user = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $DeviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $Password_user = $generalobj->encrypt($Password_user);

    if ($UserType == "Passenger") {
        $sql = "SELECT iUserId,eStatus,vLang,vTripStatus FROM `register_user` WHERE (vEmail='$Emid' OR vPhone='$Emid' ) AND vPassword='$Password_user'";
        $Data = $obj->MySQLSelect($sql);

        if (count($Data) > 0) {
            if ($Data[0]['eStatus'] == "Active") {


                $iUserId_passenger = $Data[0]['iUserId'];
                $where = " iUserId = '$iUserId_passenger' ";

                if ($GCMID != '') {

                    $Data_update_passenger['iGcmRegId'] = $GCMID;
                    $Data_update_passenger['eDeviceType'] = $DeviceType;

                    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                }

                if ($Data[0]['vTripStatus'] == "Requesting") {

                    $Data_update_tripStatus['vTripStatus'] = "Not Requesting";

                    $id = $obj->MySQLQueryPerform("register_user", $Data_update_tripStatus, 'update', $where);
                }

                $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


                if ($Data_checkLangCode[0]['vCode'] != $Data[0]['vLang']) {
                    $returnArr['changeLangCode'] = "Yes";
                    $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
                    $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                    $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $Data[0]['vLang'], '', 'true');
                    $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $Data[0]['vLang'], '', 'true');
                } else {
                    $returnArr['changeLangCode'] = "No";
                }

                $returnArr['Action'] = "1";
                $returnArr['message'] = getPassengerDetailInfo($Data[0]['iUserId'], '');
                echo json_encode($returnArr);

                createUserLog($UserType, "No", $Data[0]['iUserId'], "Android");
            } else {
                $returnArr['Action'] = "0";
                if ($Data[0]['eStatus'] != "Deleted") {
                    $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                } else {
                    $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                }
                echo json_encode($returnArr);
            }

        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            echo json_encode($returnArr);
        }
    } else {

        $sql = "SELECT rd.iDriverId,rd.eStatus,rd.vLang,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE (rd.vEmail='$Emid' OR rd.vPhone='$Emid') AND rd.vPassword='$Password_user' AND cmp.iCompanyId=rd.iCompanyId";
        $Data = $obj->MySQLSelect($sql);

        if (count($Data) > 0) {


            if ($Data[0]['eStatus'] != "Deleted") {
                if ($GCMID != '') {

                    $iDriverId_driver = $Data[0]['iDriverId'];
                    $where = " iDriverId = '$iDriverId_driver' ";

                    $Data_update_driver['iGcmRegId'] = $GCMID;
                    $Data_update_driver['eDeviceType'] = $DeviceType;

                    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

                }
                // echo json_encode(getDriverDetailInfo($Data[0]['iDriverId'],1));

                $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


                if ($Data_checkLangCode[0]['vCode'] != $Data[0]['vLang']) {
                    $returnArr['changeLangCode'] = "Yes";
                    $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
                    $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                    $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $Data[0]['vLang'], '', 'true');
                    $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $Data[0]['vLang'], '', 'true');
                } else {
                    $returnArr['changeLangCode'] = "No";
                }

                $returnArr['Action'] = "1";
                $returnArr['message'] = getDriverDetailInfo($Data[0]['iDriverId'], 1);
                echo json_encode($returnArr);

                createUserLog($UserType, "No", $Data[0]['iDriverId'], "Android");

            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                echo json_encode($returnArr);
                exit;
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            echo json_encode($returnArr);
            exit;
        }
    }

}

###########################################################################

if ($type == "getDetail") {

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';


    if ($UserType == "Passenger") {
        $sql = "SELECT iGcmRegId,vTripStatus FROM `register_user` WHERE iUserId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);

        if (count($Data) > 0) {

            $iGCMregID = $Data[0]['iGcmRegId'];
            $vTripStatus = $Data[0]['vTripStatus'];

            // if($GCMID!=''){

            // if($iGCMregID != $GCMID){
            // $where = " iUserId = '$iUserId' ";

            // $Data_update_passenger['iGcmRegId']=$GCMID;
            // $Data_update_passenger['eDeviceType']=$deviceType;

            // $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
            // }

            // }

            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "SESSION_OUT";
                echo json_encode($returnArr);
                exit;
            }

            if ($vTripStatus == "Requesting") {
                $where = " iUserId = '$iUserId' ";

                $Data_update_tripStatus['vTripStatus'] = "Not Requesting";

                $id = $obj->MySQLQueryPerform("register_user", $Data_update_tripStatus, 'update', $where);
            }

            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($iUserId, '');

            createUserLog($UserType, "Yes", $iUserId, "Android");

        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }

        //Logger($returnArr);
        echo json_encode($returnArr);
    } else {
        $sql = "SELECT iGcmRegId FROM `register_driver` WHERE iDriverId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);

        if (count($Data) > 0) {

            $iGCMregID = $Data[0]['iGcmRegId'];

            // if($GCMID!=''){

            // if($iGCMregID!=$GCMID){
            // $where = " iDriverId = '$iUserId' ";

            // $Data_update_driver['iGcmRegId']=$GCMID;

            // $id = $obj->MySQLQueryPerform("register_driver",$Data_update_driver,'update',$where);
            // }

            // }
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "SESSION_OUT";
                echo json_encode($returnArr);
                exit;
            }

            $returnArr['Action'] = "1";
            $returnArr['message'] = getDriverDetailInfo($iUserId);

            createUserLog($UserType, "Yes", $iUserId, "Android");

        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }

        echo json_encode($returnArr);

    }


}

###########################################################################

if ($type == "LoginWithFB") {

    $fbid = isset($_REQUEST["iFBId"]) ? $_REQUEST["iFBId"] : '';
    $Fname = isset($_REQUEST["vFirstName"]) ? $_REQUEST["vFirstName"] : '';
    $Lname = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';


    $DeviceType = "Android";

    if ($email != '') {
        $sql = "SELECT iUserId,eStatus,vFbId,vLang,vTripStatus FROM `register_user` WHERE vEmail='$email' OR vFbId='$fbid'";
    } else {
        $sql = "SELECT iUserId,eStatus,vFbId,vLang,vTripStatus FROM `register_user` WHERE vFbId='$fbid'";
    }
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        if ($Data[0]['eStatus'] == "Active") {

            $iUserId_passenger = $Data[0]['iUserId'];
            $where = " iUserId = '$iUserId_passenger' ";

            if ($GCMID != '') {

                $Data_update_passenger['iGcmRegId'] = $GCMID;
                $Data_update_passenger['eDeviceType'] = $DeviceType;

                if ($Data[0]['vFbId'] == '' || $Data[0]['vFbId'] == "0") {
                    $Data_update_passenger['vFbId'] = $fbid;
                }

                $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
            }

            if ($Data[0]['vTripStatus'] == "Requesting") {

                $Data_update_tripStatus['vTripStatus'] = "Not Requesting";

                $id = $obj->MySQLQueryPerform("register_user", $Data_update_tripStatus, 'update', $where);
            }

            $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
            $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


            if ($Data_checkLangCode[0]['vCode'] != $Data[0]['vLang']) {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $Data[0]['vLang'], '', 'true');
                $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $Data[0]['vLang'], '', 'true');
            } else {
                $returnArr['changeLangCode'] = "No";
            }

            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($Data[0]['iUserId'], '');

            createUserLog("Passenger", "No", $Data[0]['iUserId'], "Android");

            echo json_encode($returnArr);
            exit;

        } else {
            $returnArr['Action'] = "0";
            if ($Data[0]['eStatus'] != "Deleted") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            } else {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            }
            echo json_encode($returnArr);
            exit;
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_REGISTER";
        echo json_encode($returnArr);
        exit;
    }
}

########################### Get Available Taxi ##############################


if ($type == "loadAvailableCab") {

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';

    $Data = getOnlineDriverArr($passengerLat, $passengerLon);


    $i = 0;
    while (count($Data) > $i) {
        if ($Data[$i]['vImage'] != "" && $Data[$i]['vImage'] != "NONE") {
            $Data[$i]['vImage'] = "3_" . $Data[$i]['vImage'];
        }
        $driverVehicleID = $Data[$i]['iDriverVehicleId'];

        $sql = "SELECT dv.*, make.vMake AS make_title, model.vTitle model_title FROM `driver_vehicle` dv, make, model
			WHERE dv.iMakeId = make.iMakeId
			AND dv.iModelId = model.iModelId
			AND iDriverVehicleId='$driverVehicleID'";
        $rows_driver_vehicle = $obj->MySQLSelect($sql);

        if (!empty($rows_driver_vehicle)) {

            $cid = $rows_driver_vehicle[0]["iCompanyId"];
            $sql = "SELECT `iCompanyCode`,`vCompany` FROM `company` WHERE `iCompanyId`=$cid";
            $res = $obj->MySQLSelect($sql);
            $rows_driver_vehicle[0]['iCompanyCode'] = $res[0]['iCompanyCode'];
            $rows_driver_vehicle[0]['iCompanyName'] = $res[0]['vCompany'];
        }

        $Data[$i]['DriverCarDetails'] = $rows_driver_vehicle[0];

        $i++;
    }

    //Logger($Data);

    $where = " iUserId=" . $iUserId;
    $data['vLatitude'] = $passengerLat;
    $data['vLongitude'] = $passengerLon;
    $data['tLastOnline'] = @jdate("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("register_user", $data, 'update', $where);

    $returnArr['AvailableCabList'] = $Data;
    $returnArr['PassengerLat'] = $passengerLat;
    $returnArr['PassengerLon'] = $passengerLon;
    echo json_encode($returnArr);
}

###########################################################################


if ($type == "CheckPromoCode") {
    $promoCode = isset($_REQUEST['PromoCode']) ? clean($_REQUEST['PromoCode']) : '';
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';

    $curr_date = @date("Y-m-d");

    $promoCode = strtoupper($promoCode);
    $sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '" . $promoCode . "' AND iUsageLimit > iUsed AND (eValidityType = 'Permanent' OR dExpiryDate > '$curr_date')";

    $data = $obj->MySQLSelect($sql);

    if (count($data) > 0) {
        $returnArr['Action'] = "1"; // code is valid

        // add by seyyed amir for check first trip an limit by person
        $eForFirstTrip = $data[0]['eForFirstTrip'];
        $eOnePerUser = $data[0]['eOnePerUser'];
        Logger($data);

        if ($eForFirstTrip == 'Yes') {
            $query = "SELECT count(*) as count FROM `trips` WHERE `iUserId` = $iUserId";
            $res = $obj->MySQLSelect($query);

            if ($res[0]['count'] > 0) {
                $returnArr['Action'] = "0";
            }
        }

        if ($eOnePerUser == 'Yes') {
            $query = "SELECT count(*) as count FROM `trips` WHERE `iUserId` = $iUserId  AND vCouponCode = '$promoCode'";
            $res = $obj->MySQLSelect($query);

            if ($res[0]['count'] > 0) {
                $returnArr['Action'] = "0";
            }
        }

        ////////////////////////////////////////
    } else {

        $returnArr['Action'] = "0";// code is invalid
        //$returnArr['Action']="01";// code is used by this user
    }
    echo json_encode($returnArr);
}

###########################################################################

if ($type == 'estimateFare') {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';
    $tReturn = isset($_REQUEST["hasReturn"]) ? $_REQUEST["hasReturn"] : 'false';
    $delayId = isset($_REQUEST["delayId"]) ? $_REQUEST["delayId"] : 0;
    $hasSecDst = isset($_REQUEST["hasSecDst"]) ? $_REQUEST["hasSecDst"] : 'false';

    $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    if ($vCurrencyPassenger == '')
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $Fare_data = calculateFareEstimate($hasSecDst, $tReturn, $delayId, $time, $distance, $SelectedCar, $iUserId, 1);

    $Fare_data[0]['total_fare'] = intval($Fare_data[0]['total_fare']);
    $Fare_data[0]['total_fare'] = number_format(round($Fare_data[0]['total_fare'] * $priceRatio, 1), 0);
    $Fare_data[0]['iBaseFare'] = number_format(round($Fare_data[0]['iBaseFare'] * $priceRatio, 1), 0);
    $Fare_data[0]['fPricePerMin'] = number_format(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1), 0);
    $Fare_data[0]['fPricePerKM'] = number_format(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1), 0);
    $Fare_data[0]['fCommision'] = number_format(round($Fare_data[0]['fCommision'] * $priceRatio, 1), 0);
    if ($Fare_data[0]['MinFareDiff'] > 0) {
        $Fare_data[0]['MinFareDiff'] = number_format(round($Fare_data[0]['MinFareDiff'] * $priceRatio, 1), 0);
    } else {
        $Fare_data[0]['MinFareDiff'] = "0";
    }
    $Fare_data[0]['MinFareDiff'] = "0";
    $Fare_data[0]['Action'] = "1";

    //if($Fare_data[0]['total_fare'] % 10 == 1)
    //TLOG($Fare_data[0]);

    echo json_encode($Fare_data[0]);
}

###########################################################################

if ($type == "updateUserProfileDetail") {

    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '';
    $vLastName = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $vPhone = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $phoneCode = isset($_REQUEST["vPhoneCode"]) ? $_REQUEST['vPhoneCode'] : '';
    $vCountry = isset($_REQUEST["vCountry"]) ? $_REQUEST['vCountry'] : '';
    $currencyCode = isset($_REQUEST["CurrencyCode"]) ? $_REQUEST['CurrencyCode'] : '';
    $languageCode = isset($_REQUEST["LanguageCode"]) ? $_REQUEST['LanguageCode'] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST['UserType'] : 'Passenger';
    $vEmail = isset($_REQUEST["vEmail"]) ? $_REQUEST['vEmail'] : '';

    if ($userType != "Driver") {

        // Added by seyyed amir for disable email require
        //$lastUserEmail = get_value('register_user', 'vEmail', 'iUserId',$iMemberId,'','true');

        if ($vEmail == '')
            $vEmail_userId_check = $iMemberId;
        else
            $vEmail_userId_check = get_value('register_user', 'iUserId', 'vEmail', $vEmail, '', 'true');
        //////////////

        $vPhone_userId_check = get_value('register_user', 'iUserId', 'vPhone', $vPhone, '', 'true');

        $where = " iUserId = '$iMemberId'";
        $tableName = "register_user";

        $Data_update_User['vPhoneCode'] = $phoneCode;
        $Data_update_User['vCurrencyPassenger'] = $currencyCode;
        $currentLanguageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');

        $vPhoneCode_orig = get_value('register_user', 'vPhoneCode', 'iUserId', $iMemberId, '', 'true');
        $vPhone_orig = get_value('register_user', 'vPhone', 'iUserId', $iMemberId, '', 'true');
        $vEmail_orig = get_value('register_user', 'vEmail', 'iUserId', $iMemberId, '', 'true');
    } else {

        // add by seyyed amir for disable change profile image by driver
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_CONTACT_US_COMPANY_FOR_CHANGE_PROFILE_DRIVER";
        echo json_encode($returnArr);
        die();
        ///////////////////////////////////////////////////////////////


        $vEmail_userId_check = get_value('register_driver', 'iDriverId', 'vEmail', $vEmail, '', 'true');
        $vPhone_userId_check = get_value('register_driver', 'iDriverId', 'vPhone', $vPhone, '', 'true');

        $where = " iDriverId = '$iMemberId'";
        $tableName = "register_driver";

        $Data_update_User['vCode'] = $phoneCode;
        $Data_update_User['vCurrencyDriver'] = $currencyCode;
        $currentLanguageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');

        $vPhoneCode_orig = get_value('register_driver', 'vCode', 'iDriverId', $iMemberId, '', 'true');
        $vPhone_orig = get_value('register_driver', 'vPhone', 'iDriverId', $iMemberId, '', 'true');
        $vEmail_orig = get_value('register_driver', 'vEmail', 'iDriverId', $iMemberId, '', 'true');
    }

    // $currentLanguageCode = ($obj->MySQLSelect("SELECT vLang FROM ".$tableName." WHERE".$where)[0]['vLang']);

    if ($vEmail_userId_check != "" && $vEmail_userId_check != $iMemberId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        echo json_encode($returnArr);
        exit;
    }
    if ($vPhone_userId_check != "" && $vPhone_userId_check != $iMemberId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MOBILE_EXIST";
        echo json_encode($returnArr);
        exit;
    }

    if ($vPhone_orig != $vPhone || $vPhoneCode_orig != $phoneCode) {
        $Data_update_User['ePhoneVerified'] = "No";
    }
    if ($vEmail_orig != $vEmail) {
        $Data_update_User['eEmailVerified'] = "No";
    }

    $Data_update_User['vName'] = $vName;
    $Data_update_User['vLastName'] = $vLastName;
    $Data_update_User['vPhone'] = $vPhone;
    $Data_update_User['vCountry'] = $vCountry;
    $Data_update_User['vLang'] = $languageCode;
    $Data_update_User['vEmail'] = $vEmail;


    $id = $obj->MySQLQueryPerform($tableName, $Data_update_User, 'update', $where);

    if ($currentLanguageCode != $languageCode) {
        $returnArr['changeLangCode'] = "Yes";
        $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($languageCode, "1");
        $returnArr['vLanguageCode'] = $languageCode;
        $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $languageCode, '', 'true');
        $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $languageCode, '', 'true');
    } else {
        $returnArr['changeLangCode'] = "No";
    }
    if ($userType != "Driver") {
        $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
    } else {
        $returnArr['message'] = getDriverDetailInfo($iMemberId);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

}

###########################################################################

if ($type == "uploadImage") {
    global $generalobj, $tconfig;

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $image_name = "123.jpg";

    if ($memberType == "Driver") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_driver_path'] . "/" . $iMemberId . "/";


        // add by seyyed amir for disable change profile image by driver
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_CONTACT_US_COMPANY_FOR_CHANGE_PROFILE_DRIVER";
        echo json_encode($returnArr);
        die();
        ///////////////////////////////////////////////////////////////


    } else {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_path'] . "/" . $iMemberId . "/";
    }

    // echo $Photo_Gallery_folder."===";
    if (!is_dir($Photo_Gallery_folder))
        mkdir($Photo_Gallery_folder, 0777);

    // echo $tconfig["tsite_upload_images_member_size1"];exit;

    $vImageName = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);

    if ($vImageName != '') {
        if ($memberType == "Driver") {
            $where = " iDriverId = '" . $iMemberId . "'";
            $Data_passenger['vImage'] = $vImageName;
            $Data_passenger['eStatus'] = 'Inactive';
            $id = $obj->MySQLQueryPerform("register_driver", $Data_passenger, 'update', $where);
        } else {
            $where = " iUserId = '" . $iMemberId . "'";
            $Data_passenger['vImgName'] = $vImageName;
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where);
        }


        if ($id > 0) {
            $returnArr['Action'] = "1";
            if ($memberType == "Driver") {
                $returnArr['message'] = getDriverDetailInfo($iMemberId);
            } else {
                $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
            }


        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

}

####################### getRideHistory #############################
if ($type == "getRideHistory") {
    global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    $per_page = 10;
    $sql_all = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iUserId='$iUserId' AND (iActive='Canceled' || iActive='Finished') AND eType='" . $eType . "'";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    //$sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tr.eType='$eType' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $sql = "SELECT tr.* FROM `trips` as tr WHERE tr.iUserId='$iUserId' AND tr.eType='$eType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);


    $i = 0;
    if (count($Data) > 0) {

        while (count($Data) > $i) {

            $jalaliDate = jdate('Y-m-d \س\ا\ع\ت g:i a', strtotime($Data[$i]['tTripRequestDate']));
            $returnArr = getTripPriceDetails($Data[$i]['iTripId'], $iUserId, "Passenger");
            $Data[$i] = array_merge($Data[$i], $returnArr);
            // add by seyyed amir
            $Data[$i]['tTripRequestDate'] = $jalaliDate;

            /////////////////////////////////////////////////
            $i++;
        }


        $returnData['message'] = $Data;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = "" . ($page + 1);
        } else {
            $returnData['NextPage'] = "0";
        }
        $returnData['Action'] = "1";

        echo json_encode($returnData);

    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_RIDES_TXT";
        echo json_encode($returnData);
    }

}
/* if ($type == "getRideHistory") {
		global $generalobj;

		$page        = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
		$iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
		$eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride';
		$UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

		$vCurrencyPassenger=get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId,'','true');
		$currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger,'','true');
		$vLanguage=get_value('register_user', 'vLang', 'iUserId',$iUserId,'','true');
		if($vLanguage == "" || $vLanguage == NULL){
			$vLanguage = "EN";
		}

		$per_page=10;
		$sql_all  = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iUserId='$iUserId' AND (iActive='Canceled' || iActive='Finished') AND eType='".$eType."'";
		$data_count_all = $obj->MySQLSelect($sql_all);
		$TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

		$start_limit = ($page - 1) * $per_page;
		$limit       = " LIMIT " . $start_limit . ", " . $per_page;

		$sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tr.eType='$eType' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
		$Data = $obj->MySQLSelect($sql);
		$totalNum = count($Data);

		$i=0;
		if ( count($Data) > 0 ) {

			$row = $Data;
            while ( count($row)> $i ) {

                $rows_driver_data    = array();
                $rows_driver_vehicle = array();
                $row_driver_id = $row[$i]['iDriverId'];
				$priceRatio = $row[$i]['fRatio_'.$vCurrencyPassenger];

				$petDetails_arr= get_value('user_pets', 'iPetTypeId,vTitle as PetName,vWeight as PetWeight, tBreed as PetBreed, tDescription as PetDescription', 'iUserPetId', $row[$i]['iUserPetId'],'','');

				if(count($petDetails_arr)>0){

					if($UserType == "Passenger"){
						$vLang = get_value('register_user', 'vLang', 'iUserId', $row[$i]['iUserId'],'','true');
					}else{
						$vLang = get_value('register_driver', 'vLang', 'iDriverId', $row[$i]['iDriverId'],'','true');
					}
					$petTypeName = get_value('pet_type', 'vTitle_'.$vLang, 'iPetTypeId', $petDetails_arr[0]['iPetTypeId'],'','true');
					$row[$i]['PetDetails']['PetName'] = $petDetails_arr[0]['PetName'];
					$row[$i]['PetDetails']['PetWeight'] = $petDetails_arr[0]['PetWeight'];
					$row[$i]['PetDetails']['PetBreed'] = $petDetails_arr[0]['PetBreed'];
					$row[$i]['PetDetails']['PetDescription'] = $petDetails_arr[0]['PetDescription'];
					$row[$i]['PetDetails']['PetTypeName'] = $petTypeName;
				}else{
					$row[$i]['PetDetails']['PetName'] = '';
					$row[$i]['PetDetails']['PetWeight'] = '';
					$row[$i]['PetDetails']['PetBreed'] = '';
					$row[$i]['PetDetails']['PetDescription'] = '';
					$row[$i]['PetDetails']['PetTypeName'] = '';
				}

				$row[$i]['CurrencySymbol']= $currencySymbol;

				$row[$i]['UserDebitAmount'] = "0";

				$sql = "SELECT iBalance, eType FROM `user_wallet` WHERE iTripId='".$row[$i]['iTripId']."'";
				$user_debit_data = $obj->MySQLSelect($sql);

				if(count($user_debit_data) > 0 && $user_debit_data[0]['eType'] == "Debit"){
					$row[$i]['UserDebitAmount'] = $user_debit_data[0]['iBalance'];
				}

				$vehicleType=$row[$i]['iVehicleTypeId'];

				$sql = "SELECT vVehicleType_".$vLanguage." as vVehicleType, iVehicleCategoryId FROM `vehicle_type`  WHERE  iVehicleTypeId='$vehicleType'";
				$vehicleType_data = $obj->MySQLSelect($sql);

				$row[$i]['vVehicleType']=$vehicleType_data[0]['vVehicleType'];
				$row[$i]['vVehicleCategory']=get_value('vehicle_category', 'vCategory_'.$vLanguage, 'iVehicleCategoryId',$vehicleType_data[0]['iVehicleCategoryId'],'','true');

				$startDate=$row[$i]['tStartDate'];
				$endDateOfTrip=$row[$i]['tEndDate'];

				$totalTimeInMinutes_trip=@round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60,2);

				$diff = @abs(strtotime($endDateOfTrip) - strtotime($startDate));
				$years = floor($diff / (365*60*60*24)); $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
				$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
				$minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
				$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));

				// $row[$i]['TripTimeInMinutes']=$hours.':'.$minuts.':'.$seconds;

				// $FareOfDistance=round(($row[$i]['fPricePerKM']*$row[$i]['fDistance'])* $priceRatio,1);
				// $FareOfMinutes=($row[$i]['fPricePerMin']*$totalTimeInMinutes_trip) * $priceRatio;

				$row[$i]['TripFareOfMinutes']=strval(number_format(round($row[$i]['fPricePerMin']* $priceRatio,1),2));
                $row[$i]['TripFareOfDistance']=strval(number_format(round($row[$i]['fPricePerKM']* $priceRatio,1),2));
				$row[$i]['iFare']=strval(number_format(round($row[$i]['iFare'] * $priceRatio,1),2));

				if($row[$i]['fMinFareDiff'] > 0){
					$row[$i]['fMinFareDiff']=strval(number_format(round($row[$i]['fMinFareDiff'] * $priceRatio,1),2));
				}else{
					$row[$i]['fMinFareDiff']="0";
				}

				if($row[$i]['fDiscount'] != "" && $row[$i]['fDiscount'] != "0"&& $row[$i]['fDiscount'] != 0){
					$row[$i]['fDiscount']= strval(number_format(round($row[$i]['fDiscount'] * $priceRatio,1),2));
				}else{
					$row[$i]['fDiscount']= round($row[$i]['fDiscount'] * $priceRatio,1);
				}
                // $row[$i]['fDiscount']=strval(number_format(round($row[$i]['fDiscount'] * $row[$i]['fRatioPassenger'],1),2));
				$row[$i]['iBaseFare']=strval(number_format(round($row[$i]['iBaseFare'] * $priceRatio,1),2));
				$row[$i]['fCommision']= strval(number_format(round($row[$i]['fCommision']* $priceRatio,1),2));
				$row[$i]['tTripRequestDate']=date('dS M \a\t h:i a',strtotime($row[$i]['tTripRequestDate']));

				$totalTime=0;
				 $hours= dateDifference($row[$i]['tStartDate'],$row[$i]['tEndDate'],'%h');
				 $minutes= dateDifference($row[$i]['tStartDate'],$row[$i]['tEndDate'],'%i');
				 $seconds= dateDifference($row[$i]['tStartDate'],$row[$i]['tEndDate'],'%s');

				 if($hours>0){
					 $totalTime = $hours*60;
				}if($minutes>0){
					 $totalTime = $totalTime+$minutes;
				}
				$totalTime = $totalTime.".".$seconds;

				$row[$i]['TripTimeInMinutes']=$totalTime;

                $sql = "SELECT * FROM `register_driver` WHERE iDriverId='$row_driver_id'";
				$result_drivers = $obj->MySQLSelect($sql);
                if (count($result_drivers) > 0) {

					if($result_drivers[0]['vImage']!="" && $result_drivers[0]['vImage']!="NONE"){
						$result_drivers[0]['vImage']="3_".$result_drivers[0]['vImage'];
					}
                    $row[$i]['DriverDetails'] = $result_drivers[0];

                    $iDriverVehicleId = $row[$i]['iDriverVehicleId'];

					$sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverVehicleId='$iDriverVehicleId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId`";

                    $row_driver_vehicle = $obj->MySQLSelect($sql);

					$row_driver_vehicle[0]['vModel']=$row_driver_vehicle[0]['vTitle'];
                    $row[$i]['DriverCarDetails']   = $row_driver_vehicle[0];
				}
				$i++;
			}
			$returnData['message']=$row;
			if ($TotalPages > $page) {
				$returnData['NextPage'] = $page + 1;
			} else {
				$returnData['NextPage'] = "0";
			}
			$returnData['Action']="1";
			echo json_encode($returnData);

		}else{
			$returnData['Action']="0";
			$returnData['message']="LBL_NO_RIDES_TXT";
			echo json_encode($returnData);
		}

	} */

###########################################################################

if ($type == 'staticPage') {
    $iPageId = isset($_REQUEST['iPageId']) ? clean($_REQUEST['iPageId']) : '';
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : ''; // Passenger OR Driver

    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $pageDesc = get_value('pages', 'tPageDesc_' . $languageCode, 'iPageId', $iPageId, '', 'true');
    // $meta['page_desc']=strip_tags($pageDesc);
    $meta['page_desc'] = $pageDesc;
    echo json_encode($meta, JSON_UNESCAPED_UNICODE);
}

###########################################################################

if ($type == 'sendContactQuery') {

    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $UserId = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $subject = isset($_REQUEST["subject"]) ? $_REQUEST["subject"] : '';

    if ($UserType == 'Passenger') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_user WHERE iUserId=$UserId";

        $result_data = $obj->MySQLSelect($sql);

    } else if ($UserType == 'Driver') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_driver WHERE iDriverId=$UserId";

        $result_data = $obj->MySQLSelect($sql);

    }

    $Data['vFirstName'] = $result_data[0]['vName'];
    $Data['vLastName'] = $result_data[0]['vLastName'];
    $Data['eSubject'] = $subject;
    $Data['tSubject'] = $message;
    $Data['vEmail'] = $result_data[0]['vEmail'];
    $Data['cellno'] = $result_data[0]['vPhone'];


    // add by seyyed amir
    SupportLogger($Data);

    $tmMessage['🔹 مشخصات فرستنده'] = $Data['vFirstName'] . ' ' . $Data['vLastName'] . "\n"
        . $Data['vEmail'] . "\n"
        . $Data['cellno'];
    $tmMessage['☀️ موضوع'] = $Data['eSubject'];
    $tmMessage['☀️ متن پیام'] = str_replace('\n', "\n", $Data['tSubject']);

    $tgb = new TelegramBot();
    $tgb->sendMessage($tmMessage);
    /////////////////////////////////


    $id = $generalobj->send_email_user("CONTACTUS", $Data);

    if ($id > 0 || true) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_SENT_CONTACT_QUERY_SUCCESS_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_CONTACT_QUERY_TXT";
    }
    echo json_encode($returnArr);
}

############################# GetFAQ ######################################
if ($type == "getFAQ") {
    $status = "Active";

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';

    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }

    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql = "SELECT * FROM `faq_categories` WHERE eStatus='$status' AND vCode='" . $languageCode . "' ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);

    $i = 0;
    if (count($Data) > 0) {
        $row = $Data;
        while (count($row) > $i) {
            $rows_questions = array();
            $iUniqueId = $row[$i]['iUniqueId'];

            $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer FROM `faqs` WHERE eStatus='$status' AND iFaqcategoryId='" . $iUniqueId . "'";
            $row_questions = $obj->MySQLSelect($sql);

            $j = 0;
            while (count($row_questions) > $j) {
                $rows_questions[$j] = $row_questions[$j];
                $j++;
            }
            $row[$i]['Questions'] = $rows_questions;
            $i++;
        }

        $returnData['Action'] = "1";
        $returnData['message'] = $row;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_FAQ_NOT_AVAIL";
    }

    echo json_encode($returnData);
}
###########################################################################

if ($type == 'getReceipt') {
    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : ''; //Passenger OR Driver

    $value = sendTripReceipt($iTripId);

    if ($value == true || $value == "true" || $value == "1") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_CHECK_INBOX_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_RECEIPT_EMAIL_TXT";
    }
    echo json_encode($returnArr);
    exit;

}

###########################################################################

if ($type == "cancelCabRequest") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    $vTripStatus = get_value('register_user', 'vTripStatus', 'iUserId', $iUserId, '', 'true');

    if ($vTripStatus == "Requesting") {
        $where = " iUserId='$iUserId'";
        $Data_update_passenger['vTripStatus'] = "Not Requesting";

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);


        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "DO_RESET";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_REQUEST_CANCEL_FAILED_TXT";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_RESTART";
    }

    echo json_encode($returnArr);

}

###########################################################################

if ($type == "sendRequestToDrivers") {
    //TODO request for driver
    $driver_id_auto = isset($_REQUEST["driverIds"]) ? $_REQUEST["driverIds"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $AppVersion = isset($_REQUEST["AppVersion"]) ? floatval($_REQUEST["AppVersion"]) : 1.0;
    $passengerId = isset($_REQUEST["userId"]) ? $_REQUEST["userId"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';

    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';

    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $DestAddress = isset($_REQUEST["DestAddress"]) ? $_REQUEST["DestAddress"] : '';
    $promoCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iPackageTypeId = isset($_REQUEST["iPackageTypeId"]) ? $_REQUEST["iPackageTypeId"] : '';
    $vReceiverName = isset($_REQUEST["vReceiverName"]) ? $_REQUEST["vReceiverName"] : '';
    $vReceiverMobile = isset($_REQUEST["vReceiverMobile"]) ? $_REQUEST["vReceiverMobile"] : '';
    $tPickUpIns = isset($_REQUEST["tPickUpIns"]) ? $_REQUEST["tPickUpIns"] : '';
    $tDeliveryIns = isset($_REQUEST["tDeliveryIns"]) ? $_REQUEST["tDeliveryIns"] : '';
    $tPackageDetails = isset($_REQUEST["tPackageDetails"]) ? $_REQUEST["tPackageDetails"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '0';
    $iBookingId = isset($_REQUEST["iBookingId"]) ? $_REQUEST["iBookingId"] : '';
    $IsMultiPassenger = isset($_REQUEST["IsMultiPassenger"]) ? $_REQUEST["IsMultiPassenger"] : '';
    $NumberOfSeats = isset($_REQUEST["NumberOfSeats"]) ? $_REQUEST["NumberOfSeats"] : '';
    $TripComment = isset($_REQUEST["TripComment"]) ? $_REQUEST["TripComment"] : '';
    $fixedDistance = isset($_REQUEST["fixedDistance"]) ? $_REQUEST["fixedDistance"] : '';
    $fixedTime = isset($_REQUEST["fixedTime"]) ? $_REQUEST["fixedTime"] : '';

    $tReturn = isset($_REQUEST["tReturn"]) ? $_REQUEST["tReturn"] : 'false';
    $tSecDestination = isset($_REQUEST["tSecDestination"]) ? $_REQUEST["tSecDestination"] : 'false';
    $tSecDestLatitude = isset($_REQUEST["secDestLatitude"]) ? $_REQUEST["secDestLatitude"] : '';
    $tSecDestLongitude = isset($_REQUEST["secDestLongitude"]) ? $_REQUEST["secDestLongitude"] : '';
    $tSecDestAddress = isset($_REQUEST["secDestAddress"]) ? $_REQUEST["secDestAddress"] : '';
    $delayId = isset($_REQUEST["delayId"]) ? $_REQUEST["delayId"] : 0;
    $estimatedFare = isset($_REQUEST["estimatedFare"]) ? $_REQUEST["estimatedFare"] : 0;

    $trip_status = "Requesting";


    if ($iBookingId == '') // agar dakhast az dispacher bood in check nashavad
        checkmemberemailphoneverification($passengerId, "Passenger");

    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
    $alertMsg = $userwaitinglabel;

    if ($IsMultiPassenger == '1') {
        $ret = getOnlineDriverArrMulti($PickUpLatitude, $PickUpLongitude, $NumberOfSeats);
        if (count($ret) > 0) {
            //$Data = array($ret[0]);
            //$selectedCarTypeID = $Data['iDriverId'];
            $Data = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude);
        }
        /*else
            {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "NO_CARS";
                $returnArr['count'] = count($Data);
                //echo json_encode($returnArr);
                //exit;
            }*/

        $Data = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude);
    } elseif ($driver_id_auto > 0) {

        $sqli = "SELECT * FROM `register_driver` WHERE `iDriverId` = '${driver_id_auto}'";
        $resi = $obj->MySQLSelect($sqli);
        $Data = $resi;
    } else {
        $Data = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude);
    }

    $iGcmRegId = get_value('register_user', 'iGcmRegId', 'iUserId', $passengerId, '', 'true');
    //print_r($iGcmRegId); exit;
    if ($vDeviceToken != "" && $vDeviceToken != $iGcmRegId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        echo json_encode($returnArr);
        exit;
    }

    $passengerFName = get_value('register_user', 'vName', 'iUserId', $passengerId, '', 'true');
    $passengerLName = get_value('register_user', 'vLastName', 'iUserId', $passengerId, '', 'true');
    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($passengerId);
    $final_message['PName'] = $passengerFName . " " . $passengerLName;
    $final_message['PPicName'] = get_value('register_user', 'vImgName', 'iUserId', $passengerId, '', 'true');
    $final_message['PFId'] = get_value('register_user', 'vFbId', 'iUserId', $passengerId, '', 'true');
    $final_message['PRating'] = get_value('register_user', 'vAvgRating', 'iUserId', $passengerId, '', 'true');
    $final_message['PPhone'] = get_value('register_user', 'vPhone', 'iUserId', $passengerId, '', 'true');
    $final_message['PPhoneC'] = get_value('register_user', 'vPhoneCode', 'iUserId', $passengerId, '', 'true');
    $final_message['REQUEST_TYPE'] = $eType;
    $final_message['PACKAGE_TYPE'] = $eType == "Deliver" ? get_value('package_type', 'vName_' . $vLangCode, 'iPackageTypeId', $iPackageTypeId, '', 'true') : '';
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['TripComment'] = $TripComment;
    $final_message['fixedDistance'] = $fixedDistance;
    $final_message['fixedTime'] = $fixedTime;

    $final_message['tSecDestination'] = strval($tSecDestination);
    $final_message['tReturn'] = strval($tReturn);
    $final_message['secDestLatitude'] = strval($tSecDestLatitude);
    $final_message['secDestLongitude'] = strval($tSecDestLongitude);
    $final_message['secDestAddress'] = strval($tSecDestAddress);
    $final_message['delayId'] = strval($delayId);

    // $finalDistance = 0;
    // $totalTimeInMinutes_trip = 0;
    // $FinalDistanceArr = checkDistanceWithGoogleDirections1(0,$PickUpLatitude,$PickUpLongitude,$DestLatitude,$DestLongitude);
    //
    // $finalDistance = $FinalDistanceArr["Distance"];
    // $totalTimeInMinutes_trip = @round(abs($FinalDistanceArr['Time']),2);
    //
    // if ($tSecDestination == 'true') {
    // 	$temp1 = $finalDistance;
    // 	$temp2 = $totalTimeInMinutes_trip;
    // 	$FinalDistanceArr = checkDistanceWithGoogleDirections1(0,$DestLatitude,$DestLongitude,$tSecDestLatitude,$tSecDestLongitude);
    // 	$finalDistance = $temp1 + $FinalDistanceArr["Distance"];
    // 	$totalTimeInMinutes_trip = $temp2 + @round(abs($FinalDistanceArr['Time']),2);
    // }
    // $tripDistance=$finalDistance;

    // $Fare_data=calculateFareEstimate($tSecDestination,$tReturn,$delayId,$totalTimeInMinutes_trip,$tripDistance,$selectedCarTypeID,$passengerId,1);
    $final_message['driverTripPrice'] = $estimatedFare; //strval(intval($Fare_data[0]['total_fare']));

    if ($iBookingId != '')
        $final_message['iBookingId'] = $iBookingId;
    else {
        $final_message['iBookingId'] = "";
    }

    $final_message['IsMultiPassenger'] = $IsMultiPassenger;
    $final_message['NumberOfSeats'] = $NumberOfSeats;

    $final_message['MsgCode'] = strval(mt_rand(1000, 9999));


    $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);

    /////////////////////////////////////////////////////
    ///// add by seyyed amir
    ///// send message to passenger if trip start
    if ($AppVersion > 0 && $iCabBookingId == '') {
        $vTripStatus = get_value('register_user', 'vTripStatus', 'iUserId', $passengerId, '', 'true');
        if ($vTripStatus == 'Active') {
            $iTripId = get_value('register_user', 'iTripId', 'iUserId', $passengerId, '', 'true');
            $ActiveDriverId = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
            $iVerificationCode = get_value('trips', 'iVerificationCode', 'iTripId', $iTripId, '', 'true');
            $iAppVersion = get_value('register_driver', 'iAppVersion', 'iDriverId', $ActiveDriverId, '', 'true');

            $message_arr = array();
            $message_arr['iDriverId'] = $ActiveDriverId;
            $message_arr['Message'] = "CabRequestAccepted";
            $message_arr['iTripId'] = strval($iTripId);
            $message_arr['DriverAppVersion'] = strval($iAppVersion);
            $message_arr['iTripVerificationCode'] = $iVerificationCode;

            $message = json_encode($message_arr);

            $returnArr['Action'] = "0";
            $returnArr['message'] = "DRIVER_ASSIGNE";
            $returnArr['data'] = $message;
            echo json_encode($returnArr);
            exit;
        }
    }
    /////////////////////////////////////////////////////

    $ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');
    $eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');

    $fPickUpPrice = 1;
    $fNightPrice = 1;

    $data_surgePrice = checkSurgePrice($selectedCarTypeID, "");


    if ($data_surgePrice['Action'] == "0") {
        if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
            $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
        } else {
            $fNightPrice = $data_surgePrice['SurgePriceValue'];
        }
    }

    // in ghesmat bayad eslah shavad
    // vAvailability='Available'
    $str_date = @date('Y-m-d H:i:s', strtotime('-15 minutes')); //-1440
    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLastOnline > '$str_date' AND vAvailability='Available'";
    $result = $obj->MySQLSelect($sql);


    // echo "Res:count:".count($sql);exit;
    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "NO_CARS";
        $returnArr['count'] = count($Data);
        echo json_encode($returnArr);
        exit;
    }

    if ($cashPayment == 'true') {
        $tripPaymentMode = "Cash";
    } else {
        $tripPaymentMode = "Card";
    }

    $where = " iUserId = '$passengerId'";

    $Data_update_passenger['vTripStatus'] = $trip_status;

    if (($generalobj->getConfigurations("configurations", "PAYMENT_ENABLED")) == 'Yes') {
        $Data_update_passenger['vTripPaymentMode'] = $tripPaymentMode;
    } else {
        $Data_update_passenger['vTripPaymentMode'] = "Cash";
    }

    $Data_update_passenger['iSelectedCarType'] = $selectedCarTypeID;
    $Data_update_passenger['tDestinationLatitude'] = $DestLatitude;
    $Data_update_passenger['tDestinationLongitude'] = $DestLongitude;
    $Data_update_passenger['tDestinationAddress'] = $DestAddress;
    $Data_update_passenger['vCouponCode'] = $promoCode;
    $Data_update_passenger['fPickUpPrice'] = $fPickUpPrice;
    $Data_update_passenger['fNightPrice'] = $fNightPrice;
    $Data_update_passenger['eType'] = $eType;
    $Data_update_passenger['iPackageTypeId'] = $eType == "Deliver" ? $iPackageTypeId : '';
    $Data_update_passenger['vReceiverName'] = $eType == "Deliver" ? $vReceiverName : '';
    $Data_update_passenger['vReceiverMobile'] = $eType == "Deliver" ? $vReceiverMobile : '';
    $Data_update_passenger['tPickUpIns'] = $eType == "Deliver" ? $tPickUpIns : '';
    $Data_update_passenger['tDeliveryIns'] = $eType == "Deliver" ? $tDeliveryIns : '';
    $Data_update_passenger['tPackageDetails'] = $eType == "Deliver" ? $tPackageDetails : '';
    $Data_update_passenger['iUserPetId'] = $iUserPetId;

    $Data_update_passenger['tSecDestination'] = strval($tSecDestination);
    $Data_update_passenger['tReturn'] = strval($tReturn);
    $Data_update_passenger['secDestLatitude'] = strval($tSecDestLatitude);
    $Data_update_passenger['secDestLongitude'] = strval($tSecDestLongitude);
    $Data_update_passenger['secDestAddress'] = $tSecDestAddress;
    $Data_update_passenger['delayId'] = strval($delayId);

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");


    if ($ENABLE_PUBNUB == "Yes") {

        $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
        $driverIds_arr = explode(",", $filter_driver_ids);

        $message = stripslashes(preg_replace("/[\n\r]/", "", $message));

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        for ($i = 0; $i < count($driverIds_arr); $i++) {


            addToUserRequest($passengerId, $driverIds_arr[$i], $msg_encode, $final_message['MsgCode']);
            addToDriverRequest($driverIds_arr[$i], $passengerId, 0, "Timeout", $final_message['MsgCode']);

            /* For PubNub Setting */
            $iAppVersion = get_value("register_driver", 'iAppVersion', "iDriverId", $driverIds_arr[$i], '', 'true');
            $eDeviceType = get_value("register_driver", 'eDeviceType', "iDriverId", $driverIds_arr[$i], '', 'true');
            $vDeviceToken = get_value("register_driver", 'iGcmRegId', "iDriverId", $driverIds_arr[$i], '', 'true');
            /* For PubNub Setting Finished */

            // if($iAppVersion > 1 && $eDeviceType == "Android"){

            $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];
            // $info = $pubnub->publish($channelName, $message);
            $info = $pubnub->publish($channelName, $msg_encode);

            // }else{
            // if($eDeviceType == "Android"){
            // array_push($registation_ids_new, $vDeviceToken);
            // }else{
            // array_push($deviceTokens_arr_ios, $vDeviceToken);
            // }
            // }

            if ($eDeviceType != "Android") {
                array_push($deviceTokens_arr_ios, $vDeviceToken);
            }

        }

        if (count($registation_ids_new) > 0) {
            $Rmessage = array("message" => $message);

            $result = send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            sendApplePushNotification(1, $deviceTokens_arr_ios, "", $alertMsg, 0);
        }

    } else {
        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        foreach ($result as $item) {
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
            }

            addToUserRequest($passengerId, $item['iDriverId'], $msg_encode, $final_message['MsgCode']);
            addToDriverRequest($item['iDriverId'], $passengerId, 0, "Timeout", $final_message['MsgCode']);
        }
        //TODO Send notification for driver
        if (count($registation_ids_new) > 0) {
            // $Rmessage = array("message" => $message);
            $Rmessage = array("message" => $msg_encode);
            $result = send_notification($registation_ids_new, $Rmessage, 0);
            //echo print_r($result);exit;
        }
        if (count($deviceTokens_arr_ios) > 0) {
            // sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0);
        }
    }


    ///////////////////////////////////////
    /// add by seyyed amir for save cab booking request archive
    if ($iBookingId != '') {
        $sql = "SELECT * FROM `cab_booking` WHERE `iCabBookingId` = '{$iBookingId}'";

        $res = $obj->MySQLSelect($sql);

        if (count($res) > 0) {
            $cab = $res[0];
            $driver_req_archive = $cab['tDriverReqArchive'];

            if ($driver_req_archive == '')
                $driver_req_archive = array();
            else
                $driver_req_archive = @unserialize($driver_req_archive);


//                if($booking_test)
//                    die('OK'.print_r($driver_req_archive,true));

            $iMsgCode = $final_message['MsgCode'];

            $driver_req_archive["msg"][] = $iMsgCode;

            //
            $tDriverReqArchive = (serialize($driver_req_archive));
            //

            $sql = "UPDATE cab_booking SET tDriverReqArchive = '{$tDriverReqArchive}'  WHERE iCabBookingId = {$iBookingId}";
            $obj->sql_query($sql);
        }
    }
    /////////////////////////////////////////////////////////////

    //print_r($returnArr);
    //die();

    $returnArr['Action'] = "1";
    //echo print_r($returnArr);exit;

    echo json_encode($returnArr);
}

###########################################################################

if ($type == "cancelTrip") {

    #Logger($_REQUEST);
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';


    ///////////////////////////////
    // added by seyyed amir
    if ($userType == 'Driver') {
        $iCabBookingId = get_value('cab_booking', 'iCabBookingId', 'iTripId', $iTripId, '', 'true');
        if (intval($iCabBookingId) > 0) ;
        {
            //var_dump($iCabBookingId);
            $updateQuery = "UPDATE cab_booking set iCancelByUserId='${iDriverId}', eCancelBy = '${userType}',
                 eStatus = 'Cancel', dCancelDate = '" . date("Y-m-d H:i:s") . "' , vCancelReason = '{$driverReason}'  WHERE iCabBookingId = '{$iCabBookingId}'";
            //die($updateQuery);
            $obj->sql_query($updateQuery);
        }
    }
    ///////////////////////////////
#		if($userType != "Driver"){
#            $vTripStatus = get_value('register_user', 'vTripStatus', 'iUserId',$user_id_auto,'','true');
#
#            if($vTripStatus != "Cancelled" && $vTripStatus != "Active" && $vTripStatus != "Arrived"){
#
#            		$returnArr['Action'] = "0";
#            		$returnArr['message'] = "DO_RESTART";
#            		echo json_encode($returnArr);
#            		exit;
#            }
#        }

    $active_status = "Canceled";
    if ($userType != "Driver") {
        $message = "TripCancelled";
    } else {
        $message = "TripCancelledByDriver";
    }

    $couponCode = get_value('trips', 'vCouponCode', 'iTripId', $iTripId, '', 'true');

    if ($couponCode != '') {
        $noOfCouponUsed = get_value('coupon', 'iUsed', 'vCouponCode', $couponCode, '', 'true');

        $where = " vCouponCode = '" . $couponCode . "'";
        $data_coupon['iUsed'] = $noOfCouponUsed - 1;
        $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where);
    }

    $statusUpdate_user = "Not Assigned";
    $trip_status = "Cancelled";

    $message_arr = array();
    $message_arr['Message'] = $message;
    if ($userType == "Driver") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "false";
    }
    $message_arr['iUserId'] = $iUserId;

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    $where = " iTripId = '$iTripId'";
    $Data_update_trips['iActive'] = $active_status;
    $Data_update_trips['tEndDate'] = @date("Y-m-d H:i:s");
    if ($userType == "Driver") {
        $Data_update_trips['vCancelReason'] = $driverReason;
        $Data_update_trips['vCancelComment'] = $driverComment;
        $Data_update_trips['eCancelled'] = "Yes";
    }

    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);


    $where = " iUserId = '$iUserId'";
    $Data_update_passenger['vCallFromDriver'] = $statusUpdate_user;
    $Data_update_passenger['vTripStatus'] = $trip_status;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);


    $where = " iDriverId='$iDriverId'";
    // $Data_update_driver['iTripId']=$statusUpdate_user;
    $Data_update_driver['vTripStatus'] = $trip_status;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);


    /* For PubNub Setting */
    $tableName = $userType != "Driver" ? "register_driver" : "register_user";
    $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $iUserId;
    $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
    $iAppVersion = get_value($tableName, 'iAppVersion', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
    $eDeviceType = get_value($tableName, 'eDeviceType', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
    /* For PubNub Setting Finished */


    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");

    $alertMsg = "Trip canceled";

    $vLangCode = get_value($tableName, 'vLang', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $usercanceltriplabel = $languageLabelsArr['LBL_TRIP_CANCEL_NOTIFICATION'];
    $alertMsg = $usercanceltriplabel;

    if ($ENABLE_PUBNUB == "Yes"/*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

        $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);

        if ($userType != "Driver") {
            $channelName = "DRIVER_" . $iDriverId;
        } else {
            $channelName = "PASSENGER_" . $iUserId;
        }

        $info = $pubnub->publish($channelName, $message);

    }

    if ($userType != "Driver") {
        $sql = "SELECT iGcmRegId,eDeviceType FROM register_driver WHERE iDriverId IN (" . $iDriverId . ")";
    } else {
        $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId IN (" . $iUserId . ")";
    }

    $result = $obj->MySQLSelect($sql);

    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();

    foreach ($result as $item) {
        if ($item['eDeviceType'] == "Android") {
            array_push($registation_ids_new, $item['iGcmRegId']);
        } else {
            array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
        }
    }

    if (count($registation_ids_new) > 0 && $ENABLE_PUBNUB != "Yes") {
        $Rmessage = array("message" => $message);

        $result = send_notification($registation_ids_new, $Rmessage, 0);
    }
    if (count($deviceTokens_arr_ios) > 0) {

        if ($ENABLE_PUBNUB == "Yes") {
            $message = "";
        }

        if ($userType == "Driver") {
            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        } else {
            sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        }
    }

    // Code for Check last logout date is update in driver_log_report

    $driverId_log = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
    $query = "SELECT * FROM driver_log_report WHERE iDriverId = '" . $driverId_log . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
    $db_driver = $obj->MySQLSelect($query);
    if (count($db_driver) > 0) {
        $driver_lastonline = @date("Y-m-d H:i:s");
        $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
        $obj->sql_query($updateQuery);
    }
    // Code for Check last logout date is update in driver_log_report Ends

    $returnArr['Action'] = "1";
    echo json_encode($returnArr);

}

###########################################################################

if ($type == "addDestination") {

    $userId = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $Latitude = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $Longitude = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
    $Address = isset($_REQUEST["Address"]) ? $_REQUEST["Address"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    if ($userType != "Driver") {
        $sql = "SELECT 
                       ru.iTripId,
                       tr.iDriverId,
                       rd.vTripStatus as driverStatus,
                       rd.iGcmRegId as regId,
                       rd.eDeviceType as deviceType 
                FROM register_user as ru,
                     trips as tr,
                     register_driver as rd 
                WHERE ru.iUserId='$userId' AND 
                      tr.iTripId=ru.iTripId AND 
                      rd.iDriverId=tr.iDriverId";
    } else {
        $sql = "SELECT rd.iTripId,rd.vTripStatus as driverStatus,ru.iGcmRegId as regId,ru.eDeviceType as deviceType FROM trips as tr,register_driver as rd ,register_user as ru WHERE ru.iUserId='$userId' AND rd.iDriverId='$iDriverId'";
    }

    $data = $obj->MySQLSelect($sql);

    if (count($data) > 0) {
        $driverStatus = $data[0]['driverStatus'];

        $where_trip = " iTripId = '" . $data[0]['iTripId'] . "'";
        $Data_trips['tEndLat'] = $Latitude;
        $Data_trips['tEndLong'] = $Longitude;
        $Data_trips['tDaddress'] = $Address;
        $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'update', $where_trip);

        if ($driverStatus == "Active") {

            $where_passenger = " iUserId = '$userId'";
            $Data_passenger['tDestinationLatitude'] = $Latitude;
            $Data_passenger['tDestinationLongitude'] = $Longitude;
            $Data_passenger['tDestinationAddress'] = $Address;
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where_passenger);

        } else {

            $message = "DestinationAdded";
            if ($userType != "Driver") {
                $alertMsg = "Destination is added by passenger.";
            } else {
                $alertMsg = "Destination is added by driver.";
            }
            $message_arr = array();
            $message_arr['Message'] = $message;
            $message_arr['DLatitude'] = $Latitude;
            $message_arr['DLongitude'] = $Longitude;
            $message_arr['DAddress'] = $Address;
            $message = json_encode($message_arr);

            /* For PubNub Setting */
            $tableName = $userType != "Driver" ? "register_driver" : "register_user";
            $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $userId;
            $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
            $iAppVersion = get_value($tableName, 'iAppVersion', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
            $eDeviceType = get_value($tableName, 'eDeviceType', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
            /* For PubNub Setting Finished */

            $vLangCode = get_value($tableName, 'vLang', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }

            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
            $lblValue = $userType != "Driver" ? "LBL_DEST_ADD_BY_DRIVER" : "LBL_DEST_ADD_BY_PASSENGER";
            $alertMsg = $languageLabelsArr[$lblValue];

            $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
            $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
            $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");

            if ($ENABLE_PUBNUB == "Yes"/*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

                $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);

                if ($userType != "Driver") {
                    $channelName = "DRIVER_" . $iDriverId;
                } else {
                    $channelName = "PASSENGER_" . $userId;
                }

                $info = $pubnub->publish($channelName, $message);

            }

            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();

            if ($data[0]['deviceType'] == "Android" && $ENABLE_PUBNUB != "Yes") {
                array_push($registation_ids_new, $data[0]['regId']);

                $Rmessage = array("message" => $message);

                $result = send_notification($registation_ids_new, $Rmessage, 0);
            } else if ($data[0]['deviceType'] != "Android") {
                array_push($deviceTokens_arr_ios, $data[0]['regId']);

                if ($ENABLE_PUBNUB == "Yes") {
                    $message = "";
                }

                if ($userType == "Driver") {
                    sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                } else {
                    sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                }
            }


        }

        $returnArr['Action'] = "1";

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
}

###################### getAssignedDriverLocation ##########################
if ($type == "getDriverLocations") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $sql = "SELECT vLatitude, vLongitude,vTripStatus FROM `register_driver` WHERE iDriverId='$iDriverId'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) == 1) {
        $returnArr['Action'] = "1";
        $returnArr['vLatitude'] = $Data[0]['vLatitude'];
        $returnArr['vLongitude'] = $Data[0]['vLongitude'];
        $returnArr['vTripStatus'] = $Data[0]['vTripStatus'];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'Not Found';
    }
    echo json_encode($returnArr);

}

###########################################################################

if ($type == 'displayFare') {
    global $currency_supported_paypal, $generalobj;

    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';


    $tableName = $userType != "Driver" ? "register_user" : "register_driver";
    $iMemberId_KEY = $userType != "Driver" ? "iUserId" : "iDriverId";
    $iTripId = get_value($tableName, 'iTripId', $iMemberId_KEY, $iMemberId, '', 'true');

    $result_fare['FormattedTripDate'] = jdate('jS F  g:i a', strtotime($result_fare[0]['tStartDate']));
    $result_fare['PayPalConfiguration'] = "No";
    $result_fare['DefaultCurrencyCode'] = "USD";
    $result_fare['PaypalFare'] = strval($result_fare[0]['TotalFare']);
    $result_fare['PaypalCurrencyCode'] = $vCurrencyCode;
    $returnArr = gettrippricedetails($iTripId, $iMemberId, $userType);
    //echo "<pre>";print_r($returnArr); exit;


    $result_fare = array_merge($result_fare, $returnArr);


    if (count($returnArr) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_fare;
    } else {
        $returnArr['Action'] = "0";
    }

    // add by seyyed amir
    $returnArr['message']['FormattedTripDate'] = jdate('jS F Y \س\ا\ع\ت g:i a', strtotime($returnArr['message']['FormattedTripDate']));
    $returnArr['message']['tTripRequestDate'] = jdate('jS F Y \س\ا\ع\ت g:i a', strtotime($returnArr['message']['tTripRequestDate']));
    $returnArr['tTripRequestDate'] = jdate('jS F Y \س\ا\ع\ت g:i a', strtotime($returnArr['tTripRequestDate']));
    $returnArr['tTripRequestDate'] = jdate('jS F Y \س\ا\ع\ت g:i a', strtotime($returnArr['tTripRequestDate']));
    $returnArr['FormattedTripDate'] = jdate('jS F Y \س\ا\ع\ت g:i a', strtotime($returnArr['FormattedTripDate']));
    /////////////////////////////////////////////////

    //Logger($returnArr);
    echo json_encode($returnArr);

}


###########################################################################

if ($type == "submitRating") {

    $iGeneralUserId = isset($_REQUEST["iGeneralUserId"]) ? $_REQUEST["iGeneralUserId"] : ''; // for both driver or passenger
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating = isset($_REQUEST["rating"]) ? $_REQUEST["rating"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : ''; // Passenger or Driver

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' and eUserType = '$userType'";
    $row_check = $obj->MySQLSelect($sql);

    $ENABLE_TIP_MODULE = $generalobj->getConfigurations("configurations", "ENABLE_TIP_MODULE");

    if (count($row_check) > 0) {
        // $returnArr['Action'] = "0"; //LBL_RATING_EXIST
        // $returnArr['message'] = "LBL_ERROR_RATING_SUBMIT_AGAIN_TXT"; //LBL_RATING_EXIST
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_TRIP_FINISHED_TXT";
        echo json_encode($returnArr);
        exit;
    } else {

        if ($userType == "Passenger") {
            $iDriverId = get_value('trips', 'iDriverId', 'iTripId', $tripID, '', 'true');
            $tableName = "register_driver";
            $where = "iDriverId='" . $iDriverId . "'";
            $iMemberId = $iDriverId;

            // add by seyyed amir
            $userTypeText = 'راننده';
            $userIdText = $iDriverId;
        } else {

            $where_trip = " iTripId = '$tripID'";

            $Data_update_trips['eVerified'] = "Verified";

            $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where_trip);

            $iUserId = get_value('trips', 'iUserId', 'iTripId', $tripID, '', 'true');
            $tableName = "register_user";
            $where = "iUserId='" . $iUserId . "'";
            $iMemberId = $iUserId;

            // add by seyyed amir
            $userTypeText = 'مسافر';
            $userIdText = $iUserId;
        }


        // add by seyyed amir
        $sql = "SELECT vName,vLastName,vPhone FROM " . $tableName . ' WHERE ' . $where;
        $rowUser = $obj->MySQLSelect($sql);

        if (count($rowUser) > 0) {
            $rowUser = $rowUser[0];

            if ($rating < 5 || ($rating == 5 && $message != '')) {
                $telegram['نام ' . $userTypeText] = $rowUser['vName'] . ' ' . $rowUser['vLastName'] . " ($userIdText)";
                $telegram['زمان'] = jdate("Y-m-d H:i:s");
                $telegram['شماره موبایل'] = $rowUser['vPhone'];
                $telegram['پیام'] = str_replace('\n', "\n", $message) . "\n";
                $telegram['لینک سفر'] = '<a href="' . $tconfig["tsite_url"] . 'admin/invoice.php?iTripId=' . $tripID . '">کلیک کنید</a>';
                $telegram['لینک سفر شرکت'] = '<a href="' . $tconfig["tsite_url"] . 'invoice.php?iTripId=' . base64_encode(base64_encode($tripID)) . '">کلیک کنید(شرکت)</a>';


                $areaId = get_value('trips', 'iAreaId', 'iTripId', $tripID, '', 'true');


                $tgb = new TelegramBot();
                $tgb->sendRate($telegram, $rating);

                // change for area
            }
        }
        ////////////////////////////////////////////////


        /* Insert records into ratings table*/
        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $userType;

        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

        /* Set average rating for passenger OR Driver */
        // Driver gives rating to passenger and passenger gives rating to driver
        /*$average_rating = getUserRatingAverage($iMemberId,$userType);

			$sql = "SELECT vAvgRating FROM ".$tableName.' WHERE '.$where;
            $fetchAvgRating= $obj->MySQLSelect($sql);

			if($fetchAvgRating[0]['vAvgRating'] > 0){
				$average_rating = round(($fetchAvgRating[0]['vAvgRating'] + $rating) / 2,1);
			}else{
				$average_rating = round($fetchAvgRating[0]['vAvgRating'] + $rating,1);
			} */

        $Data_update['vAvgRating'] = getUserRatingAverage($iMemberId, $userType);

        $id = $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_TRIP_FINISHED_TXT";
            $vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $tripID, '', 'true');
            if ($vTripPaymentMode == "Card") {
                $returnArr['ENABLE_TIP_MODULE'] = $ENABLE_TIP_MODULE;
            } else {
                $returnArr['ENABLE_TIP_MODULE'] = "No";
            }
            echo json_encode($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
        }

        if ($userType == "Passenger") {
            sendTripReceipt($tripID);
        } else {
            sendTripReceiptAdmin($tripID);
        }
    }


}

###########################################################################

if ($type == "updatePassword") {
    $user_id = isset($_REQUEST["UserID"]) ? $_REQUEST["UserID"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger

    $updatedPassword = $generalobj->encrypt($Upass);

    $Data_update_user['vPassword'] = $updatedPassword;

    if ($UserType == "Passenger") {

        $where = " iUserId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_user, 'update', $where);

        if ($id > 0) {

            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($user_id, "");
            echo json_encode($returnArr);

        } else {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
        }

    } else {
        $where = " iDriverId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);


        if ($id > 0) {

            $returnArr['Action'] = "1";
            $returnArr['message'] = getDriverDetailInfo($user_id);
            echo json_encode($returnArr);

        } else {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
        }
    }

}

############################Send Sms Twilio####################################

if ($type == 'sendVerificationSMS') {
    $mobileNo = isset($_REQUEST['MobileNo']) ? clean($_REQUEST['MobileNo']) : '';
    $mobileNo = str_replace('+', '', $mobileNo);
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $REQ_TYPE = isset($_REQUEST["REQ_TYPE"]) ? $_REQUEST['REQ_TYPE'] : '';

    $isdCode = $generalobj->getConfigurations("configurations", "SITE_ISD_CODE");
    //$toMobileNum= "+".$mobileNo;
    if ($userType == "Passenger") {
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
    $verificationCode_sms = mt_rand(1000, 9999);
    $verificationCode_email = mt_rand(1000, 9999);
    $message = $prefix . ' ' . $verificationCode_sms;


    if ($iMemberId == "" && $REQ_TYPE == "DO_PHONE_VERIFY") {
        $toMobileNum = "+" . $mobileNo;
    } else {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);

        $Data_Mail['vEmail'] = isset($db_member[0]['vEmail']) ? $db_member[0]['vEmail'] : '';
        $vFirstName = isset($db_member[0]['vName']) ? $db_member[0]['vName'] : '';
        $vLastName = isset($db_member[0]['vLastName']) ? $db_member[0]['vLastName'] : '';
        $Data_Mail['vName'] = $vFirstName . " " . $vLastName;
        $Data_Mail['CODE'] = $verificationCode_email;
        $mobileNo = $db_member[0]['vPhoneCode'] . $db_member[0]['vPhone'];
        $toMobileNum = "+" . $mobileNo;
    }


    $emailmessage = "";
    $phonemessage = "";
    if ($REQ_TYPE == "DO_EMAIL_PHONE_VERIFY") {
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail != true || $sendemail != "true" || $sendemail != "1") {
            $sendemail = 0;
        }
        $result = sendEmeSms($toMobileNum, $message);
        if ($result == 0) {
            $toMobileNum = "+" . $isdCode . $mobileNo;
            $result = sendEmeSms($toMobileNum, $message);
        }

        $returnArr['Action'] = "1";
        if ($sendemail == 0 && $result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ACC_VERIFICATION_FAILED";
        } else {
            $returnArr['message_sms'] = $result == 0 ? "LBL_MOBILE_VERIFICATION_FAILED_TXT" : $verificationCode_sms;
            $returnArr['message_email'] = $sendemail == 0 ? "LBL_EMAIL_VERIFICATION_FAILED_TXT" : $verificationCode_email;
        }
        echo json_encode($returnArr);
        exit;
    } else if ($REQ_TYPE == "DO_PHONE_VERIFY") {
        $result = sendEmeSms($toMobileNum, $message);
        if ($result == 0) {
            $toMobileNum = "+" . $isdCode . $mobileNo;
            $result = sendEmeSms($toMobileNum, $message);
        }

        if ($result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
            echo json_encode($returnArr);
            exit;
        } else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $verificationCode_sms;
            echo json_encode($returnArr);
            exit;
        }
    } else if ($REQ_TYPE == "DO_EMAIL_VERIFY") {
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail != true || $sendemail != "true" || $sendemail != "1") {
            $sendemail = 0;
        }
        if ($sendemail == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
            echo json_encode($returnArr);
            exit;
        } else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $Data_Mail['CODE'];
            echo json_encode($returnArr);
            exit;
        }
    } else if ($REQ_TYPE == "EMAIL_VERIFIED") {
        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['eEmailVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED";
            echo json_encode($returnArr);
            exit;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED_ERROR";
            echo json_encode($returnArr);
            exit;
        }

    } else if ($REQ_TYPE == "PHONE_VERIFIED") {

        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['ePhoneVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_PHONE_VERIFIED";
            echo json_encode($returnArr);
            exit;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PHONE_VERIFIED_ERROR";
            echo json_encode($returnArr);
            exit;
        }
    }

    //	$returnArr['message'] =$verificationCode;
    //echo json_encode($returnArr);
}

/* if($type=='sendVerificationSMS'){
		$mobileNo = isset($_REQUEST['MobileNo'])?clean($_REQUEST['MobileNo']):'';
		$iMemberId = isset($_REQUEST['iMemberId'])?clean($_REQUEST['iMemberId']):'';
		$userType = isset($_REQUEST['UserType'])?clean($_REQUEST['UserType']):'Passenger';

		$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
		$toMobileNum= "+".$mobileNo;
		$verificationCode = mt_rand(1000, 9999);

		if($iMemberId != ""){
			if($userType=="Passenger"){
				$vLangCode = get_value('register_user', 'vLang', 'iUserId',$iMemberId,'','true');
			}else{
				$vLangCode = get_value('register_driver', 'vLang', 'iDriverId',$iMemberId,'','true');
			}
		}else{
			$vLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
		}

		$languageLabelsArr= getLanguageLabelsArr($vLangCode,"1");

		$prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];

		$message = $prefix.' '.$verificationCode;

		$result = sendEmeSms($toMobileNum,$message);
		if($result ==0){
			$toMobileNum = "+".$isdCode.$mobileNo;
			 $result = sendEmeSms($toMobileNum,$message);
		}

		if($result ==0){
			$returnArr['Action'] ="0";
			$returnArr['message'] ="LBL_MOBILE_VERIFICATION_FAILED_TXT";
		}else{
			$returnArr['Action'] ="1";
			$returnArr['message'] =$verificationCode;
		}

		echo json_encode($returnArr);
	} */

############################Send Sms Twilio END################################

###########################################################################

if ($type == "updateDriverStatus") {

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Status_driver = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';
    $isUpdateOnlineDate = isset($_REQUEST["isUpdateOnlineDate"]) ? $_REQUEST["isUpdateOnlineDate"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $iGCMregID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';

    $_REQUEST["DELTA_TIME"] = date("Y-m-d H:i:s");
//        if($iDriverId == 291)
//            TLOG($_REQUEST);

    checkmemberemailphoneverification($iDriverId, "Driver");

    if ($iDriverId == '') {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }

    $GCMID = get_value('register_driver', 'iGcmRegId', 'iDriverId', $iDriverId, '', 'true');
    if ($GCMID != "" && $iGCMregID != "" && $GCMID != $iGCMregID) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        echo json_encode($returnArr);
        exit;
    }

    $sql = "SELECT make.vMake, model.vTitle, dv.*, rd.iDriverVehicleId as iSelectedVehicleId FROM `driver_vehicle` dv, make, model, register_driver as rd WHERE dv.iDriverId='$iDriverId' AND rd.iDriverId='$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'";

    $Data_Car = $obj->MySQLSelect($sql);

    if (count($Data_Car) > 0) {
        $status = "CARS_NOT_ACTIVE";

        $i = 0;
        while (count($Data_Car) > $i) {

            $eStatus = $Data_Car[$i]['eStatus'];
            if ($eStatus == "Active") {
                $status = "CARS_AVAIL";
            }
            $i++;
        }

        if ($status == "CARS_AVAIL" && ($Data_Car[0]['iSelectedVehicleId'] == "0" || $Data_Car[0]['iSelectedVehicleId'] == "")) {
            // echo "SELECT_CAR";
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SELECT_CAR_MESSAGE_TXT";
            echo json_encode($returnArr);
            exit;
        } else if ($status == "CARS_NOT_ACTIVE") {
            // echo "CARS_NOT_ACTIVE";
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
            echo json_encode($returnArr);
            exit;
        }


    } else {
        // echo "NO_CARS_AVAIL";
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
        echo json_encode($returnArr);
        exit;
    }

    $where = " iDriverId='$iDriverId'";
    if ($Status_driver != '') {
        $Data_update_driver['vAvailability'] = $Status_driver;
    }

    if ($latitude_driver != '' && $longitude_driver != '') {
        $Data_update_driver['vLatitude'] = $latitude_driver;
        $Data_update_driver['vLongitude'] = $longitude_driver;
        $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
    }

    if ($Status_driver == "Available") {
        $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
        // insert as online
        // Code for Check last logout date is update in driver_log_report
        $query = "SELECT * FROM driver_log_report WHERE dLogoutDateTime = '0000-00-00 00:00:00' AND iDriverId = '" . $iDriverId . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
        $db_driver = $obj->MySQLSelect($query);
        if (count($db_driver) > 0) {
            $sql = "SELECT tLastOnline FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
            $db_drive_lastonline = $obj->MySQLSelect($sql);
            $driver_lastonline = $db_drive_lastonline[0]['tLastOnline'];
            $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
            $obj->sql_query($updateQuery);
        }
        // Code for Check last logout date is update in driver_log_report Ends
        $vIP = get_client_ip();
        $curr_date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `driver_log_report` (`iDriverId`,`dLoginDateTime`,`vIP`) VALUES ('" . $iDriverId . "','" . $curr_date . "','" . $vIP . "')";
        $insert_log = $obj->sql_query($sql);
    }

    if ($Status_driver == "Not Available") {
        // update as offline
        $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
        $curr_date = date('Y-m-d H:i:s');
        $selct_query = "select * from driver_log_report WHERE iDriverId = '" . $iDriverId . "' order by `iDriverLogId` desc limit 0,1";
        $get_data_log = $obj->sql_query($selct_query);

        $update_sql = "UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'";
        $result = $obj->sql_query($update_sql);
    }

    if (($isUpdateOnlineDate == "true" && $Status_driver == "Available") || ($isUpdateOnlineDate == "" && $Status_driver == "")) {
        $Data_update_driver['tOnline'] = @date("Y-m-d H:i:s");
    }
    //print_r($Data_update_driver);exit;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);


    if ($id) {
        $returnArr['Action'] = "1";
        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
    }

}

###########################################################################

if ($type == "LoadAvailableCars") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $sql = "SELECT make.vMake, model.vTitle, dv.* FROM `driver_vehicle` dv, make, model WHERE dv.iDriverId='$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`!='Deleted'";

    $Data_Car = $obj->MySQLSelect($sql);

    if (count($Data_Car) > 0) {
        $status = "CARS_NOT_ACTIVE";

        $i = 0;
        while (count($Data_Car) > $i) {

            $eStatus = $Data_Car[$i]['eStatus'];
            if ($eStatus == "Active") {
                $status = "CARS_AVAIL";
            }
            $i++;
        }
        if ($status == "CARS_NOT_ACTIVE") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
            echo json_encode($returnArr);
            exit;
        }

        // $returnArr['carList'] = $Data_Car;

        // echo json_encode($returnArr);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data_Car;
        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
        echo json_encode($returnArr);
        exit;
    }
}

########################### Set Driver CarID ############################
if ($type == "SetDriverCarID") {

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Data['iDriverVehicleId'] = isset($_REQUEST["iDriverVehicleId"]) ? $_REQUEST["iDriverVehicleId"] : '';

    $where = " iDriverId = '" . $iDriverId . "'";

    $sql = $obj->MySQLQueryPerform("register_driver", $Data, 'update', $where);
    if ($sql > 0) {
        $returnArr['Action'] = "1";
        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
    }
}

###########################################################################

if ($type == "GenerateTrip") {
    #Logger("GenerateTrip");
    #Logger($_REQUEST);

    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $Source_point_latitude = isset($_REQUEST["start_lat"]) ? $_REQUEST["start_lat"] : '';
    $Source_point_longitude = isset($_REQUEST["start_lon"]) ? $_REQUEST["start_lon"] : '';
    $Source_point_Address = isset($_REQUEST["sAddress"]) ? $_REQUEST["sAddress"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    //$iCabBookingId    = isset($_REQUEST["iBookingId"]) ? $_REQUEST["iBookingId"] : '';
    $iBookingId = isset($_REQUEST["iBookingId"]) ? $_REQUEST["iBookingId"] : '';
    $vTripComment = isset($_REQUEST["vTripComment"]) ? $_REQUEST["vTripComment"] : '';
    $fixedDistance = isset($_REQUEST["fixedDistance"]) ? $_REQUEST["fixedDistance"] : '';
    $fixedTime = isset($_REQUEST["fixedTime"]) ? $_REQUEST["fixedTime"] : '';


    // add by seyyed amir
    $driverLat = isset($_REQUEST["driverLat"]) ? $_REQUEST["driverLat"] : '';
    $driverLon = isset($_REQUEST["driverLon"]) ? $_REQUEST["driverLon"] : '';

    $vDriverAcceptLocation = "";
    if ($driverLat != '' && $driverLon != '') {
        $vDriverAcceptLocation = $driverLat . ',' . $driverLon;
    } else {
        $driver = get_value('register_driver', 'vLatitude,vLongitude,iDriverId', 'iDriverId', $driver_id);
        if (count($driver) > 0) {
            $vDriverAcceptLocation = $driver[0]['vLatitude'] . ',' . $driver[0]['vLongitude'];
        }
    }

    if ($driver_id == 0)
        TLOG($_REQUEST);

    if ($iCabBookingId != "") {
        $bookingData = get_value('cab_booking', 'iUserId,vSourceLatitude,vSourceLongitude,vSourceAddresss', 'iCabBookingId', $iCabBookingId);
        $passenger_id = $bookingData[0]['iUserId'];
        $Source_point_latitude = $bookingData[0]['vSourceLatitude'];
        $Source_point_longitude = $bookingData[0]['vSourceLongitude'];
        $Source_point_Address = $bookingData[0]['vSourceAddresss'];
    }

    $DriverMessage = "CabRequestAccepted";

    $TripRideNO = rand(10000000, 99999999);
    $TripVerificationCode = rand(1000, 9999);
    $Active = "Active";

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $passenger_id, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $tripdriverarrivlbl = $languageLabelsArr['LBL_DRIVER_ARRIVING'];

    if ($Source_point_Address == "") {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $Source_point_latitude . "," . $Source_point_longitude . "&key=" . $GoogleServerKey . "&language=" . $vGMapLangCode;

        try {

            $jsonfile = file_get_contents($url);
            $jsondata = json_decode($jsonfile);
            $source_address = $jsondata->results[0]->formatted_address;

            $Source_point_Address = $source_address;

        } catch (ErrorException $ex) {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
            exit;
        }
    }

    // if($Source_point_Address == ""){
    //         $returnArr['Action'] = "0";
    // 	$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
    //         echo json_encode($returnArr);
    //         exit;
    // }

    $sql = "SELECT 
       tSecDestination,delayId,tReturn,secDestLatitude,secDestLongitude,
       secDestAddress,vCallFromDriver,vTripStatus,vTripPaymentMode,
       iSelectedCarType,tDestinationLatitude,tDestinationLongitude,
       tDestinationAddress,vCurrencyPassenger,vCouponCode,eType,
       iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,
       tPackageDetails,fPickUpPrice,fNightPrice,iAppVersion,iUserPetId FROM register_user WHERE iUserId='$passenger_id'";
    $check_row = $obj->MySQLSelect($sql);

    $check_assign_driver = $check_row[0]['vCallFromDriver'];

    if ($check_assign_driver != "assign") {
        $check_trip_request = $check_row[0]['vTripStatus'];
        if ($check_trip_request == "Requesting" || $iCabBookingId != "") {
            $where = " iUserId = '$passenger_id'";
            $Data_update_passenger['vCallFromDriver'] = 'assign';
            $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion FROM `register_driver` WHERE iDriverId = '$driver_id'";
            $Data_vehicle = $obj->MySQLSelect($sql);
            $CAR_id_driver = $Data_vehicle[0]['iDriverVehicleId'];
            if ($iCabBookingId != "") {
                $sql_booking = "SELECT vDestLatitude,vDestLongitude,tDestAddress,ePayType,iVehicleTypeId,eType,iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,tPackageDetails,fPickUpPrice,fNightPrice,iUserPetId,vCouponCode FROM cab_booking WHERE iCabBookingId='$iCabBookingId'";
                $data_booking = $obj->MySQLSelect($sql_booking);
                $iSelectedCarType = $data_booking[0]['iVehicleTypeId'];
                $vTripPaymentMode = $data_booking[0]['ePayType'];
                $tDestinationLatitude = $data_booking[0]['vDestLatitude'];
                $tDestinationLongitude = $data_booking[0]['vDestLongitude'];
                $tDestinationAddress = $data_booking[0]['tDestAddress'];
                $fPickUpPrice = $data_booking[0]['fPickUpPrice'];
                $fNightPrice = $data_booking[0]['fNightPrice'];

                $eType = $data_booking[0]['eType'];
                $iPackageTypeId = $data_booking[0]['iPackageTypeId'];
                $vReceiverName = $data_booking[0]['vReceiverName'];
                $vReceiverMobile = $data_booking[0]['vReceiverMobile'];
                $tPickUpIns = $data_booking[0]['tPickUpIns'];
                $tDeliveryIns = $data_booking[0]['tDeliveryIns'];
                $tPackageDetails = $data_booking[0]['tPackageDetails'];
                $iUserPetId = $data_booking[0]['iUserPetId'];
                $vCouponCode = $data_booking[0]['vCouponCode'];
            } else {
                $iSelectedCarType = $check_row[0]['iSelectedCarType'];
                $vTripPaymentMode = $check_row[0]['vTripPaymentMode'];
                $tDestinationLatitude = $check_row[0]['tDestinationLatitude'];
                $tDestinationLongitude = $check_row[0]['tDestinationLongitude'];
                $tDestinationAddress = $check_row[0]['tDestinationAddress'];
                $fPickUpPrice = $check_row[0]['fPickUpPrice'];
                $fNightPrice = $check_row[0]['fNightPrice'];

                $eType = $check_row[0]['eType'];
                $iPackageTypeId = $check_row[0]['iPackageTypeId'];
                $vReceiverName = $check_row[0]['vReceiverName'];
                $vReceiverMobile = $check_row[0]['vReceiverMobile'];
                $tPickUpIns = $check_row[0]['tPickUpIns'];
                $tDeliveryIns = $check_row[0]['tDeliveryIns'];
                $tPackageDetails = $check_row[0]['tPackageDetails'];
                $iUserPetId = $check_row[0]['iUserPetId'];
                $vCouponCode = $check_row[0]['vCouponCode'];
            }

            $Data_trips['vRideNo'] = $TripRideNO;
            $Data_trips['iUserId'] = $passenger_id;
            $Data_trips['iDriverId'] = $driver_id;
            $Data_trips['tTripRequestDate'] = @date("Y-m-d H:i:s");
            $Data_trips['tStartLat'] = $Source_point_latitude;
            $Data_trips['tStartLong'] = $Source_point_longitude;
            $Data_trips['tSaddress'] = $Source_point_Address;
            $Data_trips['vDriverAcceptLocation'] = $vDriverAcceptLocation;
            $Data_trips['iActive'] = $Active;
            $Data_trips['iDriverVehicleId'] = $CAR_id_driver;
            $Data_trips['iVerificationCode'] = $TripVerificationCode;
            $Data_trips['iVehicleTypeId'] = $iSelectedCarType;
            $Data_trips['eFareType'] = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $iSelectedCarType, '', 'true');
            $Data_trips['vTripPaymentMode'] = $vTripPaymentMode;
            $Data_trips['tEndLat'] = $tDestinationLatitude;
            $Data_trips['tEndLong'] = $tDestinationLongitude;
            $Data_trips['tDaddress'] = $tDestinationAddress;
            $Data_trips['fPickUpPrice'] = $fPickUpPrice;
            $Data_trips['fNightPrice'] = $fNightPrice;

            $Data_trips['eType'] = $eType;
            $Data_trips['iPackageTypeId'] = $iPackageTypeId;
            $Data_trips['vReceiverName'] = $vReceiverName;
            $Data_trips['vReceiverMobile'] = $vReceiverMobile;
            $Data_trips['tPickUpIns'] = $tPickUpIns;
            $Data_trips['tDeliveryIns'] = $tDeliveryIns;
            $Data_trips['tPackageDetails'] = $tPackageDetails;
            $Data_trips['iUserPetId'] = $iUserPetId;

            if ($iCabBookingId != "") {

            } else {
                $Data_trips['tSecDestination'] = $check_row[0]['tSecDestination'];
                $Data_trips['tReturn'] = $check_row[0]['tReturn'];
                $Data_trips['delayId'] = $check_row[0]['delayId'];
                $Data_trips['secDestLatitude'] = $check_row[0]['secDestLatitude'];
                $Data_trips['secDestLongitude'] = $check_row[0]['secDestLongitude'];
                $Data_trips['secDestAddress'] = $check_row[0]['secDestAddress'];
            }

            if ($fixedDistance != '')
                $Data_trips['fGDdistance'] = $fixedDistance;
            if ($fixedTime != '')
                $Data_trips['fGDtime'] = $fixedTime;


            // added by seyyed amir
            $Data_trips['iAreaId'] = 0;
            $Data_trips['vTripComment'] = $vTripComment;

            $vSavarAreaId = get_value('vehicle_type', 'vSavarArea', 'iVehicleTypeId', $Data_trips['iVehicleTypeId'], '', 'true');

            if ($vSavarAreaId == '')
                $vSavarAreaId = 0;

            $Data_trips['iAreaId'] = $vSavarAreaId;

            /*
             * بدست آوردن شرکتی که راننده در آن استخدام شده است.
             */
            $iCompanyId = 0;
            $iCompanyId = get_value('register_driver', 'iCompanyId', 'iDriverId', $Data_trips['iDriverId'], '', 'true');
            if ($iCompanyId == '')
                $iCompanyId = 0;
            $Data_trips['iCompanyId'] = $iCompanyId;
            ////////////////////////////////////////

            if ($vCouponCode != '') {
                $Data_trips['vCouponCode'] = $vCouponCode;

                $noOfCouponUsed = get_value('coupon', 'iUsed', 'vCouponCode', $vCouponCode, '', 'true');
                $where = " vCouponCode = '" . $vCouponCode . "'";
                $data_coupon['iUsed'] = $noOfCouponUsed + 1;
                $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where);
            }

            $currencyList = get_value('currency', '*', 'eStatus', 'Active');

            for ($i = 0; $i < count($currencyList); $i++) {
                $currencyCode = $currencyList[$i]['vName'];
                $Data_trips['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
            }

            $Data_trips['vCurrencyPassenger'] = $check_row[0]['vCurrencyPassenger'];
            $Data_trips['vCurrencyDriver'] = $Data_vehicle[0]['vCurrencyDriver'];
            // $Data_trips['fRatioPassenger']=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$check_row[0]['vCurrencyPassenger']."' ")[0]['Ratio']);
            $Data_trips['fRatioPassenger'] = get_value('currency', 'Ratio', 'vName', $check_row[0]['vCurrencyPassenger'], '', 'true');
            // $Data_trips['fRatioDriver']=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$Data_vehicle[0]['vCurrencyDriver']."' ")[0]['Ratio']);
            $Data_trips['fRatioDriver'] = get_value('currency', 'Ratio', 'vName', $Data_vehicle[0]['vCurrencyDriver'], '', 'true');


            #if($driver_id == 390)
            #    TLOG($Data_trips);

            $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'insert');
            $iTripId = $id;
            $trip_status = "Active";

            #### Update Driver Request Status of Trip ####
            UpdateDriverRequest($driver_id, $passenger_id, $iTripId, "Accept");
            #### Update Driver Request Status of Trip ####

            if ($iCabBookingId != "") {
                $where = " iCabBookingId = '$iCabBookingId'";
                $data_update_booking['iTripId'] = $iTripId;
                $data_update_booking['eStatus'] = "Completed";
                $obj->MySQLQueryPerform("cab_booking", $data_update_booking, 'update', $where);
            }

            $where = " iUserId = '$passenger_id'";
            $Data_update_passenger['iTripId'] = $iTripId;
            $Data_update_passenger['vTripStatus'] = $trip_status;
            $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

            $where = " iDriverId = '$driver_id'";
            $Data_update_driver['iTripId'] = $iTripId;
            $Data_update_driver['vTripStatus'] = $trip_status;
            $Data_update_driver['vAvailability'] = "Not Available";
            $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

            $message_arr = array();
            $message_arr['iDriverId'] = $driver_id;
            $message_arr['Message'] = $DriverMessage;
            $message_arr['iTripId'] = strval($iTripId);
            $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
            if ($iCabBookingId != "") {
                $message_arr['iCabBookingId'] = $iCabBookingId;
                $message_arr['iBookingId'] = $iCabBookingId;
            }
            $message_arr['iTripVerificationCode'] = $TripVerificationCode;

            $message = json_encode($message_arr);

            if ($iTripId > 0) {

                $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
                $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
                $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");


                /* For PubNub Setting */
                $tableName = "register_user";
                $iMemberId_VALUE = $passenger_id;
                $iMemberId_KEY = "iUserId";
                $iAppVersion = get_value($tableName, 'iAppVersion', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
                $eDeviceType = get_value($tableName, 'eDeviceType', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
                /* For PubNub Setting Finished */

                $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$passenger_id'";
                $result = $obj->MySQLSelect($sql);
                $registatoin_ids = $result[0]['iGcmRegId'];

                $deviceTokens_arr_ios = array();
                $registation_ids_new = array();

                if ($ENABLE_PUBNUB == "Yes"/*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

                    $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);

                    $channelName = "PASSENGER_" . $passenger_id;
                    $info = $pubnub->publish($channelName, $message);

                    if ($result[0]['eDeviceType'] != "Android") {
                        //$alertMsg = "Driver is arriving";
                        $alertMsg = $tripdriverarrivlbl;
                        array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);

                        sendApplePushNotification(0, $deviceTokens_arr_ios, "", $alertMsg, 0);
                    }

                } else {

                    if ($result[0]['eDeviceType'] == "Android") {
                        array_push($registation_ids_new, $result[0]['iGcmRegId']);
                        $Rmessage = array("message" => $message);
                        $result = send_notification($registation_ids_new, $Rmessage, 0);

                    } else {
                        //$alertMsg = "Driver is arriving";
                        $alertMsg = $tripdriverarrivlbl;
                        array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);

                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                }


                $returnArr['Action'] = "1";
                $data['iTripId'] = $iTripId;
                $data['tEndLat'] = $tDestinationLatitude;
                $data['tEndLong'] = $tDestinationLongitude;
                $data['tDaddress'] = $tDestinationAddress;
                $data['PAppVersion'] = $check_row[0]['iAppVersion'];

                $data['tSecDestination'] = $check_row[0]['tSecDestination'];
                $data['tReturn'] = $check_row[0]['tReturn'];
                $data['delayId'] = $check_row[0]['delayId'];
                $data['secDestLatitude'] = $check_row[0]['secDestLatitude'];
                $data['secDestLongitude'] = $check_row[0]['secDestLongitude'];
                $data['secDestAddress'] = $check_row[0]['secDestAddress'];

                $returnArr['message'] = $data;

                if ($iCabBookingId != "") {
                    $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode,iAppVersion', 'iUserId', $passenger_id);
                    $returnArr['sourceLatitude'] = $Source_point_latitude;
                    $returnArr['sourceLongitude'] = $Source_point_longitude;
                    $returnArr['PassengerId'] = $passenger_id;
                    $returnArr['PName'] = $passengerData[0]['vName'] . ' ' . $passengerData[0]['vLastName'];
                    $returnArr['PPicName'] = $passengerData[0]['vImgName'];
                    $returnArr['PFId'] = $passengerData[0]['vFbId'];
                    $returnArr['PRating'] = $passengerData[0]['vAvgRating'];
                    $returnArr['PPhone'] = $passengerData[0]['vPhone'];
                    $returnArr['PPhoneC'] = $passengerData[0]['vPhoneCode'];
                    $returnArr['PAppVersion'] = $passengerData[0]['iAppVersion'];
                    $returnArr['TripId'] = strval($iTripId);
                    $returnArr['DestLocLatitude'] = $tDestinationLatitude;
                    $returnArr['DestLocLongitude'] = $tDestinationLongitude;
                    $returnArr['DestLocAddress'] = $tDestinationAddress;

                }

                echo json_encode($returnArr);

            } else {
                $data['Action'] = "0";
                $data['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            }

            echo json_encode($data);
            exit;

        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CAR_REQUEST_CANCELLED_TXT";
            echo json_encode($returnArr);
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT";
        echo json_encode($returnArr);
    }

}


###########################################################################
if ($type == "arrivedDriverRequest") {

    $iDriverId = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $iMsgCode = isset($_REQUEST["iMsgCode"]) ? $_REQUEST["iMsgCode"] : '';

    //TLOG("ARRIVED\n".print_r($_REQUEST,true));
    $count = UpdateDriverRequestByMsgAndDriver($iMsgCode, $iDriverId, "Arrived");


    if ($count > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
    }
    echo json_encode($returnArr);
    exit;
}

###########################################################################

if ($type == "DriverArrived") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    if ($iDriverId != '') {

        $vTripStatus = get_value('register_driver', 'vTripStatus', 'iDriverId', $iDriverId, '', 'true');
        if ($vTripStatus == "Cancelled") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "DO_RESTART";
            echo json_encode($returnArr);
            exit;
        }

        $where = " iDriverId = '$iDriverId'";

        $Data_update_driver['vTripStatus'] = 'Arrived';

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

        if ($id > 0) {

            $sql = "SELECT tr.tSecDestination,tr.secDestLatitude,
				tr.secDestLongitude,tr.secDestAddress,tr.delayId,tr.tReturn,tr.tEndLat,tr.tEndLong,tr.tDaddress,tr.iUserId FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
            $result = $obj->MySQLSelect($sql);

            $returnArr['Action'] = "1";

            if ($result[0]['tEndLat'] != '' && $result[0]['tEndLong'] != '') {
                $data['DLatitude'] = $result[0]['tEndLat'];
                $data['DLongitude'] = $result[0]['tEndLong'];
                $data['DAddress'] = $result[0]['tDaddress'];

                $data['tSecDestination'] = $result[0]['tSecDestination'];
                $data['secDestLatitude'] = $result[0]['secDestLatitude'];
                $data['tReturn'] = $result[0]['tReturn'];
                $data['delayId'] = $result[0]['delayId'];
                $data['secDestAddress'] = $result[0]['secDestAddress'];
                $data['secDestLongitude'] = $result[0]['secDestLongitude'];

            } else {
                $data['DLatitude'] = "0";
                $data['DLongitude'] = "0";
                $data['DAddress'] = "0";
                $data['tSecDestination'] = "";
                $data['secDestLatitude'] = "";
                $data['tReturn'] = "";
                $data['delayId'] = "";
                $data['secDestAddress'] = "";
                $data['secDestLongitude'] = "";
            }
            $returnArr['message'] = $data;
            // echo "UpdateSuccess";

            $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
            $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
            $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");

            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $result[0]['iUserId'];
            $iMemberId_KEY = "iUserId";
            $iAppVersion = get_value($tableName, 'iAppVersion', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
            $eDeviceType = get_value($tableName, 'eDeviceType', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
            $iGcmRegId = get_value($tableName, 'iGcmRegId', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
            $vLangCode = get_value($tableName, 'vLang', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
            /* For PubNub Setting Finished */

            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }

            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
            $driverArrivedLblValue = $languageLabelsArr['LBL_DRIVER_ARRIVED_TXT'];

            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();
            $message = "";
            if ($ENABLE_PUBNUB == "Yes"/*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

                $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);

                $channelName = "PASSENGER_" . $result[0]['iUserId'];
                $message_arr['MsgType'] = "DriverArrived";
                $message_arr['iDriverId'] = $iDriverId;
                $message = json_encode($message_arr);

                $info = $pubnub->publish($channelName, $message);

            }

            if ($eDeviceType == "Android" && $ENABLE_PUBNUB != "Yes") {
                array_push($registation_ids_new, $iGcmRegId);
                $Rmessage = array("message" => $message);
                $result = send_notification($registation_ids_new, $Rmessage, 0);

            } else if ($eDeviceType != "Android") {
                if ($ENABLE_PUBNUB == "Yes") {
                    $message = "";
                }
                $alertMsg = $driverArrivedLblValue;
                array_push($deviceTokens_arr_ios, $iGcmRegId);

                sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
            }

        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            // echo "UpdateFailed";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
}

############################################################################

if ($type == "updateDriverLocations") {

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';


    $where = " iDriverId='$iDriverId'";
    $Data_update_driver['vLatitude'] = $latitude_driver;
    $Data_update_driver['vLongitude'] = $longitude_driver;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);


    if ($id) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
}

###########################################################################

if ($type == "updateTripLocations") {

    $tripId = isset($_POST["TripId"]) ? $_POST["TripId"] : '';
    $latitudes = isset($_POST['latList']) ? $_POST['latList'] : '';
    $longitudes = isset($_POST['lonList']) ? $_POST['lonList'] : '';

    if ($tripId != '' && $latitudes != '' && $longitudes != '') {
        $latitudes = preg_replace("/[^0-9,.-]/", "", $latitudes);
        $longitudes = preg_replace("/[^0-9,.-]/", "", $longitudes);
        $id = processTripsLocations($tripId, $latitudes, $longitudes);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
    }

    echo json_encode($returnArr);
}

###########################################################################


if ($type == "StartTrip") {

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $TripID = isset($_REQUEST["TripID"]) ? $_REQUEST["TripID"] : '';
    $startLat = isset($_REQUEST["startLat"]) ? $_REQUEST["startLat"] : '';
    $startLng = isset($_REQUEST["startLng"]) ? $_REQUEST["startLng"] : '';

    $vDriverStartLocation = $startLat . ',' . $startLng;

    $startDateOfTrip = @date("Y-m-d H:i:s");
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $tripstartlabel = $languageLabelsArr['LBL_START_TRIP_DIALOG_TXT'];
    $message = "TripStarted";

    $verificationCode = rand(10000000, 99999999);

    $eType = get_value('trips', 'eType', 'iTripId', $TripID, '', 'true');

    $message_arr = array();
    $message_arr['Message'] = $message;
    $message_arr['iDriverId'] = $iDriverId;

    if ($eType == "Deliver") {
        $message_arr['VerificationCode'] = strval($verificationCode);
    } else {
        $message_arr['VerificationCode'] = "";
    }

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    //Update passenger Table
    $where = " iUserId = '$iUserId'";

    $Data_update_passenger['vTripStatus'] = 'On Going Trip';

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    //Update Driver Table
    $where = " iDriverId = '$iDriverId'";

    $Data_update_driver['vTripStatus'] = 'On Going Trip';

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);


    $sql = "SELECT iGcmRegId,eDeviceType,iTripId FROM register_user WHERE iUserId='$iUserId'";
    $result = $obj->MySQLSelect($sql);

    // $Curr_TripID=$result[0]['iTripId'];

    $where = " iTripId = '$TripID'";

    $Data_update_trips['iActive'] = 'On Going Trip';
    $Data_update_trips['tStartDate'] = $startDateOfTrip;
    $Data_update_trips['vDriverStartLocation'] = $vDriverStartLocation;

    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);


    if ($id > 0) {
        $returnArr['Action'] = "1";

        $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
        $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
        $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");

        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $iUserId;
        $iMemberId_KEY = "iUserId";
        $iAppVersion = get_value($tableName, 'iAppVersion', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
        $eDeviceType = get_value($tableName, 'eDeviceType', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
        /* For PubNub Setting Finished */

        if ($ENABLE_PUBNUB == "Yes"/*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

            $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);

            $channelName = "PASSENGER_" . $iUserId;

            $info = $pubnub->publish($channelName, $message);

        }

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        if ($result[0]['eDeviceType'] == "Android" && $ENABLE_PUBNUB != "Yes") {
            array_push($registation_ids_new, $result[0]['iGcmRegId']);
            $Rmessage = array("message" => $message);
            $result = send_notification($registation_ids_new, $Rmessage, 0);

        } else if ($result[0]['eDeviceType'] != "Android") {
            if ($ENABLE_PUBNUB == "Yes") {
                $message = "";
            }

            //$alertMsg = "Your trip is started";
            $alertMsg = $tripstartlabel;
            array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);

            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        }

        // Send SMS to receiver if trip type is delivery.
        if ($eType == "Deliver") {
            $receiverMobile = get_value('trips', 'vReceiverMobile', 'iTripId', $TripID, '', 'true');
            $receiverMobile1 = "+" . $receiverMobile;

            $where_trip_update = " iTripId = '$TripID'";
            $data_delivery['vDeliveryConfirmCode'] = $verificationCode;
            $obj->MySQLQueryPerform("trips", $data_delivery, 'update', $where);

            //$message_deliver = "SMS format goes here. Your verification code is ".$verificationCode." Please give this code to driver to end delivery process.";
            $message_deliver = deliverySmsToReceiver($TripID);
            $result = sendEmeSms($receiverMobile1, $message_deliver);
            if ($result == 0) {
                $isdCode = $generalobj->getConfigurations("configurations", "SITE_ISD_CODE");
                $receiverMobile = "+" . $isdCode . $receiverMobile;
                sendEmeSms($receiverMobile, $message_deliver);
            }

            $returnArr['message'] = $verificationCode;
            $returnArr['SITE_TYPE'] = SITE_TYPE;
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

}

###########################################################################

if ($type == "ProcessEndTrip") {

    global $generalobj;

    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $userId = isset($_REQUEST["PassengerId"]) ? $_REQUEST["PassengerId"] : '';
    $driverId = isset($_REQUEST["DriverId"]) ? $_REQUEST["DriverId"] : '';
    $latitudes = isset($_REQUEST["latList"]) ? $_REQUEST["latList"] : '';
    $longitudes = isset($_REQUEST["lonList"]) ? $_REQUEST["lonList"] : '';
    $tripDistance = isset($_REQUEST["TripDistance"]) ? $_REQUEST["TripDistance"] : '0';
    $dAddress = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    $destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    $isTripCanceled = isset($_REQUEST["isTripCanceled"]) ? $_REQUEST["isTripCanceled"] : '';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    $waitingTimeInTrip = isset($_REQUEST["waitingTimeInTrip"]) ? $_REQUEST["waitingTimeInTrip"] : '';

    $tReturn = isset($_REQUEST["tReturn"]) ? $_REQUEST["tReturn"] : 'false';
    $delayId = isset($_REQUEST["delayId"]) ? $_REQUEST["delayId"] : 0;
    $tSecDestination = isset($_REQUEST["tSecDestination"]) ? $_REQUEST["tSecDestination"] : 'false';
    $tSecDestLatitude = isset($_REQUEST["secDestLatitude"]) ? $_REQUEST["secDestLatitude"] : '';
    $tSecDestLongitude = isset($_REQUEST["secDestLongitude"]) ? $_REQUEST["secDestLongitude"] : '';

    $vDriverEndLocation = $destination_lat . ',' . $destination_lon;
    $vDriverSecEndLocation = "";
    if ($tSecDestination == "true") {
        $vDriverSecEndLocation = $tSecDestLatitude . ',' . $tSecDestLongitude;
    }

    if ($waitingTimeInTrip == '')
        $waitingTimeInTrip = 0;
    else {
        $waitingTimeInTrip = round($waitingTimeInTrip, 2);
    }
    ///////////////////


    $Active = "Finished";
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $userId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $tripcancelbydriver = $languageLabelsArr['LBL_TRIP_CANCEL_BY_DRIVER'];
    $tripfinish = $languageLabelsArr['LBL_TRIP_FINISH'];

    if ($isTripCanceled == "true") {
        $message = "TripCancelledByDriver";
    } else {
        $message = "TripEnd";
    }

    $message_arr = array();
    $message_arr['Message'] = $message;
    $message_arr['iDriverId'] = $driverId;

    if ($isTripCanceled == "true") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "true";
    }

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    $couponCode = get_value('trips', 'vCouponCode', 'iTripId', $tripId, '', 'true');
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
        $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
    }


    if ($latitudes != '' && $longitudes != '') {
        processTripsLocations($tripId, $latitudes, $longitudes);
    }

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $currencySymbolDriver = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');

    $sql = "SELECT 
                tStartDate,
                iVehicleTypeId,
                tStartLat,
                tStartLong,
                fRatio_" . $vCurrencyDriver . " as fRatioDriver, 
                vTripPaymentMode,
                fPickUpPrice,
                fNightPrice 
                FROM 
                trips 
            WHERE 
                iTripId='$tripId'";
    $trip_start_data_arr = $obj->MySQLSelect($sql);

    $tripDistance = calcluateTripDistance($tripId);

    $sourcePointLatitude = $trip_start_data_arr[0]['tStartLat'];
    $sourcePointLongitude = $trip_start_data_arr[0]['tStartLong'];
    $startDate = $trip_start_data_arr[0]['tStartDate'];
    $vehicleTypeID = $trip_start_data_arr[0]['iVehicleTypeId'];
    $eFareType = $trip_start_data_arr[0]['eFareType'];
    //$vTripPaymentMode		= $trip_start_data_arr[0]['vTripPaymentMode'];

    $endDateOfTrip = @date("Y-m-d H:i:s");

    $totalTimeInMinutes_trip = @round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60, 2);

    // if($totalTimeInMinutes_trip <= 1){
    // 	$FinalDistance= $tripDistance;
    // }else{
    // 	$FinalDistance=checkDistanceWithGoogleDirections($tripDistance,$sourcePointLatitude,$sourcePointLongitude,$destination_lat,$destination_lon);
    // }


    if ($totalTimeInMinutes_trip <= 1) {
        $FinalDistance = $tripDistance;
    } else {
        $FinalDistance = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon);
        if ($tSecDestination == 'true') {
            $temp = $FinalDistance;
            $FinalDistance = $temp + checkDistanceWithGoogleDirections(0, $destination_lat, $destination_lon, $tSecDestLatitude, $tSecDestLongitude);
        }
    }

    $tripDistance = $FinalDistance;

    $typeOfFare = $generalobj->getConfigurations("configurations", "TYPE_OF_FARE_CALCULATION");
    if ($typeOfFare == "Fixed") {
        $tripItem = get_value('trips', '*', 'iTripId', $tripId, '');
        //TLOG($tripItem);
        if (count($tripItem) > 0) {
            $tripItem = $tripItem[0];
            if ($tripItem['fGDtime'] != "")
                $totalTimeInMinutes_trip = $tripItem['fGDtime'];
            if ($tripItem['fGDdistance'] != "")
                $tripDistance = $tripItem['fGDdistance'];
        }
    }

    $Fare_data = calculateFare($tReturn, $delayId, $tSecDestination, $totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $userId, 1, $startDate, $endDateOfTrip, $couponCode, $tripId, $waitingTimeInTrip);

    //if($waitingTimeInTrip > 0)
    //    TLOG($Fare_data);

    $where = " iTripId = '" . $tripId . "'";

    $Data_update_trips['tEndDate'] = $endDateOfTrip;

    // disable by seyyed amir
    //$Data_update_trips['tEndLat']=$destination_lat;
    //$Data_update_trips['tEndLong']=$destination_lon;

    // add by seyyed amir
    if ($tSecDestination == "true") {
        $Data_update_trips['vDriverEndLocation'] = $vDriverSecEndLocation;
    } else {
        $Data_update_trips['vDriverEndLocation'] = $vDriverEndLocation;
    }

    $Data_update_trips['tDaddress'] = $dAddress;
    $Data_update_trips['iFare'] = ($Fare_data['total_fare']);
    $Data_update_trips['iActive'] = $Active;
    $Data_update_trips['fDistance'] = $tripDistance;
    $Data_update_trips['fWaitingTime'] = $waitingTimeInTrip;
    $Data_update_trips['fPricePerMin'] = $Fare_data['fPricePerMin'];
    $Data_update_trips['fPricePerKM'] = $Fare_data['fPricePerKM'];
    $Data_update_trips['iBaseFare'] = $Fare_data['iBaseFare'];
    //TODO where trip data updated and we should add area commission and platform commission and also we have to change fCommision to company commission
    /*
     * جهت محاسبه کمسیون مربوط به ناحیه و پلتفرم
     * باید مقدار سهم مشارکت هر کدام از جداول مربوطه
     * دریافت و در محاسبات لحاظ شود
     * سهم ناحیه با توجه به شرکت طرف قرارداد با راننده
     * انتخاب شده در جدول company ذخیره شده است
     * سهم پلتفرم هم طبق قرارداد با ناحیه مورد نظر در جدول
     * مربوطه savar_area برای هر ناحیه ذخیره شده است
     */
    $Data_update_trips['area_commission'] = 0;
    $Data_update_trips['platform_commission'] = 0;




    $Data_update_trips['fCommision'] = $Fare_data['fCommision'];
    $Data_update_trips['fDiscount'] = $Fare_data['fDiscount'];
    $Data_update_trips['vDiscount'] = $Fare_data['vDiscount'];
    $Data_update_trips['fMinFareDiff'] = $Fare_data['MinFareDiff'];
    $Data_update_trips['fSurgePriceDiff'] = $Fare_data['fSurgePriceDiff'];
    $Data_update_trips['fWalletDebit'] = $Fare_data['user_wallet_debit_amount'];
    $Data_update_trips['fTripGenerateFare'] = $Fare_data['fTripGenerateFare'];

    $Data_update_trips['iPriceZoneRatio'] = $Fare_data['priceZoneRatio'];


    if ($isTripCanceled == "true") {
        $Data_update_trips['vCancelReason'] = $driverReason;
        $Data_update_trips['vCancelComment'] = $driverComment;
        $Data_update_trips['eCancelled'] = "Yes";
    }

    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);

    $trip_status = "Not Active";

    $where = " iUserId = '$userId'";
    $Data_update_passenger['iTripId'] = $tripId;
    $Data_update_passenger['vTripStatus'] = $trip_status;
    $Data_update_passenger['vCallFromDriver'] = 'Not Assigned';

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    $where = " iDriverId = '$driverId'";
    $Data_update_driver['iTripId'] = $tripId;
    $Data_update_driver['vTripStatus'] = $trip_status;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id > 0) {

        $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
        $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
        $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");

        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $userId;
        $iMemberId_KEY = "iUserId";
        $iAppVersion = get_value($tableName, 'iAppVersion', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
        $eDeviceType = get_value($tableName, 'eDeviceType', $iMemberId_KEY, $iMemberId_VALUE, '', 'true');
        /* For PubNub Setting Finished */

        if ($ENABLE_PUBNUB == "Yes" /* && $iAppVersion > 1 && $eDeviceType == "Android" */) {

            $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);

            $channelName = "PASSENGER_" . $userId;

            $info = $pubnub->publish($channelName, $message);

        }

        $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$userId'";
        $result = $obj->MySQLSelect($sql);

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        if ($result[0]['eDeviceType'] == "Android" && $ENABLE_PUBNUB != "Yes") {
            array_push($registation_ids_new, $result[0]['iGcmRegId']);
            $Rmessage = array("message" => $message);
            $result = send_notification($registation_ids_new, $Rmessage);

        } else if ($result[0]['eDeviceType'] != "Android") {
            // $alertMsg = "Your trip is finished";
            if ($isTripCanceled == "true") {
                //$alertMsg = "Your trip is cancelled by driver.";
                $alertMsg = $tripcancelbydriver;
            } else {
                //$alertMsg = "Your trip is finished";
                $alertMsg = $tripfinish;
            }
            array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);

            if ($ENABLE_PUBNUB == "Yes") {
                $message = "";
            }

            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        }


        $returnArr['Action'] = "1";
        $returnArr['iTripsLocationsID'] = $id;
        // $returnArr['TotalFare']=round($Fare_data[0]['total_fare'] * $trip_start_data_arr[0]['fRatioDriver']);
        $returnArr['TotalFare'] = round($Fare_data['total_fare'] * $trip_start_data_arr[0]['fRatioDriver'], 0);
        // $returnArr['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$trip_start_data_arr[0]['vCurrencyDriver']."' ")[0]['vSymbol']);
        $returnArr['CurrencySymbol'] = $currencySymbolDriver;
        $returnArr['tripStartTime'] = $startDate;
        $returnArr['TripPaymentMode'] = $trip_start_data_arr[0]['vTripPaymentMode'];
        $returnArr['Discount'] = round($Fare_data['fDiscount'] * $trip_start_data_arr[0]['fRatioDriver'], 0);
        $returnArr['Message'] = "Data Updated";
        $returnArr['FormattedTripDate'] = jdate('jS F  g:i a', strtotime($startDate));

        // Code for Check last logout date is update in driver_log_report
        $query = "SELECT * FROM driver_log_report WHERE iDriverId = '" . $driverId . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
        $db_driver = $obj->MySQLSelect($query);
        if (count($db_driver) > 0) {
            $driver_lastonline = @date("Y-m-d H:i:s");
            $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
            $obj->sql_query($updateQuery);
        }
        // Code for Check last logout date is update in driver_log_report Ends

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    // added by seyyed amir
    // Referrals
    @SavarProcessReferrals($tripId);

    #Logger($returnArr);
    echo json_encode($returnArr);

}

###########################################################################

if ($type == "CollectPayment") {

    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isCollectCash = isset($_REQUEST["isCollectCash"]) ? $_REQUEST["isCollectCash"] : '';

    $sql = "SELECT vTripPaymentMode,iUserId,iFare,vRideNo,fWalletDebit FROM trips WHERE iTripId='$iTripId'";
    $tripData = $obj->MySQLSelect($sql);

    $vTripPaymentMode = $tripData[0]['vTripPaymentMode'];

    $iUserId = $tripData[0]['iUserId'];
    $iFare = $total_fare = $tripData[0]['iFare'];
    $vRideNo = $tripData[0]['vRideNo'];


    // agar daryafte naghdi taghaza nashode bood
    if ($isCollectCash == "") {

        try {
            if ($iFare > 0) {
                $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
                $user_wallet_debit_amount = 0;
                if ($total_fare > $user_available_balance) {
                    $total_fare = $total_fare - $user_available_balance;
                    $user_wallet_debit_amount = $user_available_balance;
                } else {
                    $user_wallet_debit_amount = $total_fare;
                    $total_fare = 0;
                }

                // Update User Wallet
                if ($user_wallet_debit_amount > 0) {
                    //$vRideNo = get_value('trips', 'vRideNo', 'iTripId',$tripId,'','true');
                    $data_wallet['iUserId'] = $iUserId;
                    $data_wallet['eUserType'] = "Rider";
                    $data_wallet['iBalance'] = $user_wallet_debit_amount;
                    $data_wallet['eType'] = "Debit";
                    $data_wallet['dDate'] = date("Y-m-d H:i:s");
                    $data_wallet['iTripId'] = $iTripId;
                    $data_wallet['eFor'] = "Booking";
                    $data_wallet['ePaymentStatus'] = "Unsettelled";
                    $data_wallet['tDescription'] = "Amount " . $user_wallet_debit_amount . " debited from your account for trip number #" . $vRideNo;
                    $data_wallet['tDescription'] = "مقدار " . $user_wallet_debit_amount . " کسر شده از حساب شما برای سفر با شماره #" . $vRideNo;

                    $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
                    //$obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');

                    // important
                    // for refresh activity if iFare > 0
                    if ($total_fare > 0) {
                        $data['vTripPaymentMode'] = "Card";
                        $returnArr['message'] = "refresh";
                    } else
                        $data['vTripPaymentMode'] = "Cash";
                } else {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                    echo json_encode($returnArr);
                    exit;
                }


                $data['iFare'] = $total_fare;
                $data['fWalletDebit'] = $tripData[0]['fWalletDebit'] + $user_wallet_debit_amount;


            }
        } catch (Exception $e) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
            echo json_encode($returnArr);
            exit;
        }

    } else if ($vTripPaymentMode == "Card" && $isCollectCash == "true") {
        $data['vTripPaymentMode'] = "Cash";
    }


    $where = " iTripId = '$iTripId'";
    $data['ePaymentCollect'] = "Yes";

    $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);

    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    //Logger($returnArr);
    echo json_encode($returnArr);
}

###########################################################################

###########################################################################

/*
	if($type=="CollectPayment"){
		$iTripId     = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
		$isCollectCash     = isset($_REQUEST["isCollectCash"]) ? $_REQUEST["isCollectCash"] : '';

		$sql = "SELECT vTripPaymentMode,iUserId,iFare,vRideNo FROM trips WHERE iTripId='$iTripId'";
		$tripData = $obj->MySQLSelect($sql);

		 $vTripPaymentMode = $tripData[0]['vTripPaymentMode'];

		$iUserId = $tripData[0]['iUserId'];
		$iFare = $tripData[0]['iFare'];
		$vRideNo = $tripData[0]['vRideNo'];

		if($vTripPaymentMode == "Card" && $isCollectCash == ""){

			$vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId,'','true');

			$price_new = $iFare * 100;
			$currency = get_value('currency', 'vName', 'eDefault', 'Yes','','true');

			$description = "Payment received for trip number:".$vRideNo;

			try{
				if($iFare > 0){
					$charge_create = Stripe_Charge::create(array(
					  "amount" => $price_new,
					  "currency" => $currency,
					  "customer" => $vStripeCusId,
					  "description" =>  $description
					));

					$details = json_decode($charge_create);
					$result = get_object_vars($details);
				}


				if($iFare == 0 || ($result['status']=="succeeded" && $result['paid']=="1")){

					 $pay_data['tPaymentUserID']= $iFare == 0? "":$result['id'];
					$pay_data['vPaymentUserStatus']="approved";
					$pay_data['iTripId']=$iTripId;
					$pay_data['iAmountUser']=$iFare;

					$id = $obj->MySQLQueryPerform("payments",$pay_data,'insert');

				}else{
					$returnArr['Action'] = "0";
					$returnArr['message']="LBL_CHARGE_COLLECT_FAILED";

					echo json_encode($returnArr);exit;
				}


			}catch(Exception $e){
				$returnArr['Action'] = "0";
				$returnArr['message']="LBL_CHARGE_COLLECT_FAILED";

				echo json_encode($returnArr);exit;
			}

		}else if($vTripPaymentMode == "Card" && $isCollectCash == "true"){
		// echo "else if";exit;
			$data['vTripPaymentMode']="Cash";
		}

		// echo "out";exit;
		$where = " iTripId = '$iTripId'";
		$data['ePaymentCollect']="Yes";

		$id = $obj->MySQLQueryPerform("trips",$data,'update',$where);

		if($id >0){
			$returnArr['Action'] = "1";
		}else{
			$returnArr['Action'] = "0";
			$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
		}

		echo json_encode($returnArr);
	}
*/
###########################################################################

###########################################################################

if ($type == "addMoneyUserWallet") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iUserId = "iUserId";
        $eUserType = "Rider";
    } else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iUserId = "iDriverId";
        $eUserType = "Driver";
    }
    $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId, '', 'true');
    $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId, '', 'true');
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId, '', 'true');
    $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $currencyCode, '', 'true');
    $price = $fAmount * $currencyratio;
    $price_new = $price * 100;
    $price_new = round($price_new);
    if ($vStripeCusId == "" || $vStripeToken == "") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        echo json_encode($returnArr);
        exit;
    }

    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $iTripId = 0;
    $tDescription = "Amount credited";
    $ePaymentStatus = 'Unsettelled';

    try {
        $charge_create = Stripe_Charge::create(array(
            "amount" => $price_new,
            "currency" => $currencyCode,
            "customer" => $vStripeCusId,
            "description" => $tDescription
        ));

        $details = json_decode($charge_create);
        $result = get_object_vars($details);
        //echo "<pre>";print_r($result);exit;
        if ($result['status'] == "succeeded" && $result['paid'] == "1") {
            $generalobj->InsertIntoUserWallet($iMemberId, $eUserType, $price, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate);
            $user_available_balance = $generalobj->get_user_available_balance($iMemberId, $eUserType);
            $returnArr["Action"] = "1";
            $returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0, $user_available_balance, $userCurrencyCode));
            $returnArr['message'] = "LBL_WALLET_MONEY_CREDITED";
            echo json_encode($returnArr);
            exit;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WALLET_MONEY_CREDITED_FAILED";

            echo json_encode($returnArr);
            exit;
        }

    } catch (Exception $e) {
        //echo "<pre>";print_r($e);exit;
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

        echo json_encode($returnArr);
        exit;
    }

}
###########################################################################

if ($type == "GenerateCustomer") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    $vStripeToken = isset($_REQUEST["vStripeToken"]) ? $_REQUEST["vStripeToken"] : '';
    $CardNo = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : '';

    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $vEmail = "vEmail";
        $iMemberId = "iUserId";
        $eUserType = "Rider";
    } else {
        $tbl_name = "register_driver";
        $vEmail = "vEmail";
        $iMemberId = "iDriverId";
        $eUserType = "Driver";
    }

    $vEmail = get_value($tbl_name, $vEmail, $iMemberId, $iUserId, '', 'true');
    $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iMemberId, $iUserId, '', 'true');

    if ($vStripeCusId != "") {
        $customer = Stripe_Customer::retrieve($vStripeCusId);
        $sources = $customer->sources;
        $data = $sources->data;
        // print_r($sources);
        // echo "<br/>".$data[0]['id'];exit;

        if (count($data) > 0 && $data[0]['id'] != '') {
            $customer->sources->retrieve($data[0]['id'])->delete();
        }

        $card = $customer->sources->create(array("source" => $vStripeToken));

        $where = " $iMemberId = '$iUserId'";
        $data_user['vStripeToken'] = $vStripeToken;
        $data_user['vCreditCard'] = $CardNo;

        $id = $obj->MySQLQueryPerform($tbl_name, $data_user, 'update', $where);
        if ($eMemberType == "Passenger") {
            $profileData = getPassengerDetailInfo($iUserId);
        } else {
            $profileData = getDriverDetailInfo($iUserId);
        }

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $profileData;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }

    } else {
        try {
            $customer = Stripe_Customer::create(array("source" => $vStripeToken, "email" => $vEmail));
            $vStripeCustomerId = $customer->id;

            $where = " $iMemberId = '$iUserId'";
            $data['vStripeToken'] = $vStripeToken;
            $data['vStripeCusId'] = $vStripeCustomerId;
            $data['vCreditCard'] = $CardNo;

            $id = $obj->MySQLQueryPerform($tbl_name, $data, 'update', $where);
            if ($eMemberType == "Passenger") {
                $profileData = getPassengerDetailInfo($iUserId);
            } else {
                $profileData = getDriverDetailInfo($iUserId);
            }

            if ($id > 0) {
                $returnArr['Action'] = "1";
                $returnArr['message'] = $profileData;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }

    }
    echo json_encode($returnArr);
}

###########################################################################

if ($type == "CheckCard") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');

    if ($vStripeCusId != "") {

        try {
            $customer = Stripe_Customer::retrieve($vStripeCusId);
            $sources = $customer->sources;
            $data = $sources->data;

            $cvc_check = $data[0]['cvc_check'];

            if ($cvc_check && $cvc_check == "pass") {
                $returnArr['Action'] = "1";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_INVALID_CARD";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
}

###########################################################################

if ($type == "getDriverRideHistory") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $date = isset($_REQUEST["date"]) ? $_REQUEST["date"] : '';

    $date = date("Y-m-d", strtotime($date));

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId, '', 'true');
    // $currencySymbol=get_value('currency', 'vSymbol', 'eDefault', 'Yes','','true');
    // $priceRatio=1;
    // $fRatioDriver = get_value('currency', 'Ratio', 'vName', $vCurrencyDriver,'','true');
    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');

    $vLanguage = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    $sql = "SELECT tr.*, rate.vRating1, rate.vMessage,ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,ratings_user_driver as rate,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND tr.iActive='Finished' AND rate.iTripId = tr.iTripId AND rate.eUserType='Passenger' AND ru.iUserId=tr.iUserId";


    // Dsable rate.eUserType='Passenger' by seyyed amir
    $sql = "SELECT tr.*, rate.vRating1, rate.vMessage,ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,ratings_user_driver as rate,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND tr.iActive='Finished' AND rate.iTripId = tr.iTripId AND rate.eUserType='Driver' AND ru.iUserId=tr.iUserId";

    $tripData = $obj->MySQLSelect($sql);

    //TLOG($tripData);

    $totalEarnings = 0;
    $avgRating = 0;

    if (count($tripData) > 0) {

        for ($i = 0; $i < count($tripData); $i++) {
            $iFare = $tripData[$i]['fTripGenerateFare'];
            $fCommision = $tripData[$i]['fCommision'];
            $fDiscount = $tripData[$i]['fDiscount'];
            $vRating1 = $tripData[$i]['vRating1'];
            $priceRatio = $tripData[$i]['fRatio_' . $vCurrencyDriver];

            if (($iFare == "" || $iFare == 0) && $fDiscount > 0) {
                $incValue = ($fDiscount - $fCommision);
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            } else if ($iFare != "" && $iFare > 0) {
                $incValue = ($iFare - $fCommision);
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            }

            $avgRating = $avgRating + $vRating1;

            $returnArr = getTripPriceDetails($tripData[$i]['iTripId'], $iDriverId, "Driver");
            $tripData[$i] = array_merge($tripData[$i], $returnArr);
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $tripData;

    } else {
        $returnArr['Action'] = "0";
    }
    $returnArr['TotalEarning'] = strval(round($totalEarnings, 0));
    $returnArr['TripDate'] = jdate('l, dS F Y', strtotime($date));
    $returnArr['TripCount'] = strval(count($tripData));
    $returnArr['AvgRating'] = strval(count($tripData) == 0 ? 0 : ($avgRating / count($tripData)));
    $returnArr['CurrencySymbol'] = $currencySymbol;

    echo json_encode($returnArr);

}
###########################################################################

if ($type == "loadDriverFeedBack") {
    global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $vAvgRating = get_value('register_driver', 'vAvgRating', 'iDriverId', $iDriverId, '', 'true');

    $per_page = 10;
    $sql_all = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iDriverId='$iDriverId' AND iActive='Finished'";


    $data_count_all = $obj->MySQLSelect($sql_all);

    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    $sql = "SELECT rate.*,DATE_FORMAT(rate.tDate, '%M, %Y') AS tDate FROM ratings_user_driver as rate, trips as tr WHERE  rate.iTripId = tr.iTripId AND tr.iDriverId='$iDriverId' AND tr.iActive='Finished' AND rate.eUserType='Passenger' ORDER BY tr.iTripId DESC" . $limit;

    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);

    if (count($Data) > 0) {

        $returnData['message'] = $Data;
        // DISABLE RATING LIST BY seyyed amir
        $returnData['message'] = '';//$Data;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        } else {
            $returnData['NextPage'] = "0";
        }
        $returnData['vAvgRating'] = strval($vAvgRating);
        $returnData['Action'] = "1";
        echo json_encode($returnData);

    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_FEEDBACK";
        echo json_encode($returnData);
    }

}

###########################################################################

if ($type == "loadEmergencyContacts") {
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';

    $data = get_value('user_emergency_contact', '*', 'iUserId', $iUserId);

    if (count($data) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $data;
    } else {
        $returnData['Action'] = "0";
    }
    echo json_encode($returnData);
}

###########################################################################

if ($type == "addEmergencyContacts") {
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '0';
    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '0';

    $sql = "SELECT vPhone FROM user_emergency_contact WHERE iUserId = '" . $iUserId . "' AND vPhone='" . $Phone . "'";

    $Data_Exist = $obj->MySQLSelect($sql);

    if (count($Data_Exist) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EME_CONTACT_EXIST";
    } else {
        $Data['vName'] = $vName;
        $Data['vPhone'] = $Phone;
        $Data['iUserId'] = $iUserId;

        $id = $obj->MySQLQueryPerform("user_emergency_contact", $Data, 'insert');

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }

    echo json_encode($returnArr);
}

###########################################################################

if ($type == "deleteEmergencyContacts") {
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iEmergencyId = isset($_REQUEST["iEmergencyId"]) ? $_REQUEST["iEmergencyId"] : '0';

    $sql = "DELETE FROM user_emergency_contact WHERE `iEmergencyId`='" . $iEmergencyId . "' AND `iUserId`='" . $iUserId . "'";
    $id = $obj->sql_query($sql);
    // echo "ID:".$id;exit;
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
}

###########################################################################
if ($type == "sendAlertToEmergencyContacts") {
    global $generalobj, $obj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '0';

    $dataArr = get_value('user_emergency_contact', '*', 'iUserId', $iUserId);

    if (count($dataArr) > 0) {
        $sql = "SELECT tr.*,dv.vLicencePlate,CONCAT(rd.vName,' ',rd.vLastName) as vDriverName,rd.vPhone as DriverPhone,CONCAT(ru.vName,' ',ru.vLastName) as vPassengerName,ru.vPhone as PassengerPhone FROM trips as tr, register_driver as rd, register_user as ru, driver_vehicle as dv WHERE tr.iTripId = '" . $iTripId . "' AND rd.iDriverId = tr.iDriverId AND ru.iUserId = tr.iUserId AND dv.iDriverVehicleId = tr.iDriverVehicleId";

        $tripData = $obj->MySQLSelect($sql);

        $isdCode = $generalobj->getConfigurations("configurations", "SITE_ISD_CODE");

        $message = "مهم: این پیام را " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') از طریق نرم افزار الو تاکسی برای شما ارسال کرده است. لطفا سریع خود را به او برسانید. جزئیات سفر او: ' . jdate('jS F  g:i a', strtotime($tripData[0]['tStartDate'])) . '. حرکت از: ' . $tripData[0]['tSaddress'] . '. نام راننده: ' . $tripData[0]['vDriverName'] . '. تلفن راننده:(' . $tripData[0]['DriverPhone'] . "). شماره ماشین: " . $tripData[0]['vLicencePlate'];

        for ($i = 0; $i < count($dataArr); $i++) {
            $phone = preg_replace("/[^0-9]/", "", $dataArr[$i]['vPhone']);

            $toMobileNum = "+" . $phone;

            //Logger($toMobileNum . " -> " . $message);
            $result = sendEmeSms($toMobileNum, $message);
            if ($result == 0) {
                $toMobileNum = "+" . $isdCode . $phone;
                sendEmeSms($toMobileNum, $message);
            }
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_ALERT_SENT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ADD_EME_CONTACTS";
    }


    echo json_encode($returnArr);
}


###########################################################################

if ($type == "ScheduleARide") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $pickUpLocAdd = isset($_REQUEST["pickUpLocAdd"]) ? $_REQUEST["pickUpLocAdd"] : '';
    $pickUpLatitude = isset($_REQUEST["pickUpLatitude"]) ? $_REQUEST["pickUpLatitude"] : '';
    $pickUpLongitude = isset($_REQUEST["pickUpLongitude"]) ? $_REQUEST["pickUpLongitude"] : '';
    $destLocAdd = isset($_REQUEST["destLocAdd"]) ? $_REQUEST["destLocAdd"] : '';
    $destLatitude = isset($_REQUEST["destLatitude"]) ? $_REQUEST["destLatitude"] : '';
    $destLongitude = isset($_REQUEST["destLongitude"]) ? $_REQUEST["destLongitude"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    $timeZone = isset($_REQUEST["TimeZone"]) ? $_REQUEST["TimeZone"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iPackageTypeId = isset($_REQUEST["iPackageTypeId"]) ? $_REQUEST["iPackageTypeId"] : '';
    $vReceiverName = isset($_REQUEST["vReceiverName"]) ? $_REQUEST["vReceiverName"] : '';
    $vReceiverMobile = isset($_REQUEST["vReceiverMobile"]) ? $_REQUEST["vReceiverMobile"] : '';
    $tPickUpIns = isset($_REQUEST["tPickUpIns"]) ? $_REQUEST["tPickUpIns"] : '';
    $tDeliveryIns = isset($_REQUEST["tDeliveryIns"]) ? $_REQUEST["tDeliveryIns"] : '';
    $tPackageDetails = isset($_REQUEST["tPackageDetails"]) ? $_REQUEST["tPackageDetails"] : '';
    $vCouponCode = isset($_REQUEST["vCouponCode"]) ? $_REQUEST["vCouponCode"] : '';
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    // $paymentMode =  isset($_REQUEST["paymentMode"]) ? $_REQUEST["paymentMode"] : 'Cash'; // Cash OR Card
    // $paymentMode = "Cash";
    // $paymentMode = $eType == "Deliver" ?"Card":"Cash";
    if ($cashPayment == 'true') {
        $paymentMode = "Cash";
    } else {
        $paymentMode = "Card";
    }

    checkmemberemailphoneverification($iUserId, "Passenger");

    // $systemTimeZone = date_default_timezone_get();
    // echo "hererrrrr:::".$systemTimeZone;exit;
    // $pickUpDateTime = converToTz($scheduleDate,$systemTimeZone,$timeZone);
    // $pickUpDateTime = convertTimeZone("2016-29-14 15:29:41","Asia/Calcutta");

    // date_default_timezone_set($timeZone);
    // echo gmdate('Y-m-d H:i', strtotime($scheduleDate));exit;

    // echo "hererrrrr:::".$pickUpDateTime;exit;

    $ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    $eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');

    $fPickUpPrice = 1;
    $fNightPrice = 1;

    $data_surgePrice = checkSurgePrice($selectedCarTypeID, $scheduleDate);

    if ($data_surgePrice['Action'] == "0") {
        if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
            $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
        } else {
            $fNightPrice = $data_surgePrice['SurgePriceValue'];
        }
    }

    $Data['iUserId'] = $iUserId;
    $Data['vSourceLatitude'] = $pickUpLatitude;
    $Data['vSourceLongitude'] = $pickUpLongitude;
    $Data['vDestLatitude'] = $destLatitude;
    $Data['vDestLongitude'] = $destLongitude;
    $Data['vSourceAddresss'] = $pickUpLocAdd;
    $Data['tDestAddress'] = $destLocAdd;
    $Data['ePayType'] = $paymentMode;
    $Data['iVehicleTypeId'] = $iVehicleTypeId;
    $Data['vBookingNo'] = rand(10000000, 99999999);
    $Data['dBooking_date'] = date('Y-m-d H:i', strtotime($scheduleDate));
    $Data['eCancelBy'] = "";
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $Data['eType'] = $eType;
    $Data['iUserPetId'] = $iUserPetId;
    if ($eType == "Deliver") {
        $Data['iPackageTypeId'] = $iPackageTypeId;
        $Data['vReceiverName'] = $vReceiverName;
        $Data['vReceiverMobile'] = $vReceiverMobile;
        $Data['tPickUpIns'] = $tPickUpIns;
        $Data['tDeliveryIns'] = $tDeliveryIns;
        $Data['tPackageDetails'] = $tPackageDetails;
        $Data['vCouponCode'] = $vCouponCode;
    }
    $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'insert');

    if ($id > 0) {
        $returnArr["Action"] = "1";
        $returnArr['message'] = $eType == "Deliver" ? "LBL_DELIVERY_BOOKED" : "LBL_RIDE_BOOKED";

    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

    }

    echo json_encode($returnArr);
}


###########################################################################

if ($type == "checkBookings") {
    global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $bookingType = isset($_REQUEST["bookingType"]) ? $_REQUEST["bookingType"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $per_page = 10;

    if ($UserType == "Driver") {
        $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Assign' AND iDriverId='" . $iDriverId . "' AND eType='" . $bookingType . "'";
    } else {
        $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE  iUserId='$iUserId' AND eStatus != 'Completed' AND eType='" . $bookingType . "'";
    }

    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    if ($UserType == "Driver") {
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND cb.eStatus = 'Assign' AND cb.iDriverId='$iDriverId' AND cb.eType='" . $bookingType . "' ORDER BY cb.iCabBookingId DESC" . $limit;
    } else {
        // $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iUserId='$iUserId' AND (cb.eStatus = 'Assign' OR cb.eStatus = 'Pending') ORDER BY cb.iCabBookingId DESC" . $limit;
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iUserId='$iUserId' AND cb.eStatus != 'Completed' AND cb.eType='" . $bookingType . "' ORDER BY cb.iCabBookingId DESC" . $limit;
    }

    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);

    if (count($Data) > 0) {

        for ($i = 0; $i < count($Data); $i++) {
            //change date to jdate by seyyed amir
            $Data[$i]['dBooking_date'] = jdate('Y-m-d \س\ا\ع\ت g:i a', strtotime($Data[$i]['dBooking_date']));
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;

        if ($TotalPages > $page) {
            $returnArr['NextPage'] = $page + 1;
        } else {
            $returnArr['NextPage'] = "0";
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $bookingType == "Ride" ? "LBL_NO_BOOKINGS_AVAIL" : "LBL_NO_DELIVERY_AVAIL";
    }

    echo json_encode($returnArr);
}

/* if($type=="checkPassengerBookings"){
		$iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

		$sql = "SELECT * FROM cab_booking WHERE iUserId='$iUserId'";
		$data = $obj->MySQLSelect($sql);

		if(count($data)>0){

			for($i=0;$i<count($data);$i++){
				$eStatus = $data[$i]['eStatus'];

				if($eStatus == "Assign"){
					$iTripId = $data[$i]['iTripId'];

					$sql = "SELECT iActive,eCancelled FROM trips WHERE iTripId='$iTripId'";
					$trip_data_arr = $obj->MySQLSelect($sql);

					if($trip_data_arr[0]['iActive'] == "Finished" || $trip_data_arr[0]['iActive'] == "Canceled" || $trip_data_arr[0]['eCancelled'] == "Yes"){
						if($trip_data_arr[0]['eCancelled'] == "Yes"){
							$eStatus = "Cancelled by driver";
						}else{
							$eStatus = $trip_data_arr[0]['iActive'];
						}

					}
				}

			}
			$returnArr['Action'] ="1";
			$returnArr['Data'] =$data;
		}else{
			$returnArr['Action'] ="0";
		}

		echo json_encode($returnArr);
	} */

###########################################################################
if ($type == "cancelBooking") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $Reason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';

    $where = " iCabBookingId = '$iCabBookingId'";
    $data_update_booking['eStatus'] = "Cancel";
    $data_update_booking['vCancelReason'] = $Reason;
    $data_update_booking['iCancelByUserId'] = $iUserId;
    $data_update_booking['dCancelDate'] = @date("Y-m-d H:i:s");
    $data_update_booking['eCancelBy'] = $userType == "Driver" ? $userType : "Rider";
    $id = $obj->MySQLQueryPerform("cab_booking", $data_update_booking, 'update', $where);

    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_BOOKING_CANCELED";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
}
###########################################################################
// if($type == "loadPackageTypes"){
// $vehicleTypes = get_value('package_type', '*', 'eStatus', 'Active');

// if(count($vehicleTypes) >0){
// $returnArr['Action']="1";
// $returnArr['message']=$vehicleTypes;
// }else{
// $returnArr['Action']="0";
// $returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
// }

// echo json_encode($returnArr);
// }
if ($type == "loadPackageTypes") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $vehicleTypes = get_value('package_type', '*', 'eStatus', 'Active');

    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iUserId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    }
    if ($languageCode == "" || $languageCode == NULL) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $vehicleDataArr = [];

    for ($i = 0; $i < count($vehicleTypes); $i++) {
        $vehicleDataArr[$i]['iPackageTypeId'] = $vehicleTypes[$i]['iPackageTypeId'];
        $vehicleDataArr[$i]['vName'] = $vehicleTypes[$i]['vName_' . $languageCode];
        $vehicleDataArr[$i]['eStatus'] = $vehicleTypes[$i]['eStatus'];
    }

    if (count($vehicleDataArr) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $vehicleDataArr;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
}
###########################################################################
if ($type == "loadDeliveryDetails") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';

    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }
    // else{
    // $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId,'','true');
    // }
    if ($languageCode == "" || $languageCode == NULL) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql = "SELECT tr.vReceiverName,tr.vReceiverMobile,tr.tPickUpIns,tr.tDeliveryIns,tr.tPackageDetails,pt.vName_" . $languageCode . " as packageType,concat(ru.vName,' ',ru.vLastName) as senderName, ru.vPhone as senderMobile from trips as tr, register_user as ru, package_type as pt WHERE ru.iUserId = tr.iUserId AND tr.iTripId = '" . $iTripId . "' AND pt.iPackageTypeId = tr.iPackageTypeId";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0 && $iTripId != "") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
}

###########################################################################

if ($type == "checkSurgePrice") {
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    $selectedTime = isset($_REQUEST["SelectedTime"]) ? $_REQUEST["SelectedTime"] : '';

    $data = checkSurgePrice($selectedCarTypeID, $selectedTime);

    echo json_encode($data);
}

###########################################################################

if ($type == "getTransactionHistory") {
    global $generalobj;
    #echo "hello"; exit;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';

    if ($UserType == "Passenger") {
        $UserType = "Rider";
    }

    $ssql = '';
    $per_page = 10;
    $sql_all = "SELECT COUNT(iUserWalletId) As TotalIds FROM user_wallet WHERE  iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    $user_available_balance = $generalobj->get_user_available_balance($iUserId, $UserType);
    $returnData['user_available_balance_value'] = $user_available_balance;

    //$sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $sql = "SELECT * from user_wallet where iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ORDER BY iUserWalletId ASC";
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);

    $vSymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
    if ($UserType == 'Driver') {
        $uservSymbol = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iUserId, '', 'true');

        // Add By seyyed amir for driver app
        require_once(TPATH_CLASS . "class.general_admin.php");
        $generalobjAdmin = new General_admin();
        $returnData['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($iUserId, '');
        $returnData['transferAmountValue'] = $returnData['transferAmount'];
        ////////////////////////////////////
    } else {
        $uservSymbol = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    }

    $userCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $uservSymbol, '', 'true');

    if (isset($returnData['transferAmount']))
        $returnData['transferAmount'] .= ' ' . $userCurrencySymbol;

    $returnData['userCurrencySymbol'] = $userCurrencySymbol;

    $i = 0;
    if (count($Data) > 0) {

        $row = $Data;
        $prevbalance = 0;
        while (count($row) > $i) {
            if ($row[$i]['eType'] == "Credit") {
                $row[$i]['currentbal'] = $prevbalance + $row[$i]['iBalance'];
            } else {
                $row[$i]['currentbal'] = $prevbalance - $row[$i]['iBalance'];
            }
            $prevbalance = $row[$i]['currentbal'];
            $row[$i]['dDate'] = date('d-M-Y', strtotime($row[$i]['dDate']));

            //$row[$i]['currentbal'] = $vSymbol.$row[$i]['currentbal'];
            //$row[$i]['iBalance'] = $vSymbol.$row[$i]['iBalance'];
            $row[$i]['currentbal'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['currentbal'], $uservSymbol);
            $row[$i]['iBalance'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['iBalance'], $uservSymbol);
            $i++;
        }

        // added vby seyyed amir
        $rowCount = count($row);
        for ($i = 0; $i < $rowCount; $i++)
            $row[$i]['dDate'] = jdate('j F Y \س\ا\ع\ت g:i a', strtotime($row[$i]['dDate']));
        ///////////////


        $returnData['message'] = array_reverse($row);
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        } else {
            $returnData['NextPage'] = 0;
        }

        $returnData['user_available_balance_default'] = $user_available_balance . $vSymbol;
        $returnData['user_available_balance'] = strval($generalobj->userwalletcurrency(0, $user_available_balance, $uservSymbol));
        $returnData['user_available_balance_value'] = $user_available_balance;
        $returnData['Action'] = "1";
        #echo "<pre>"; print_r($returnData); exit;
        echo json_encode($returnData);

    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_TRANSACTION_AVAIL";
        $returnData['user_available_balance'] = $userCurrencySymbol . "0";
        echo json_encode($returnData);
    }

}

###########################################################################
if ($type == "loadPassengersLocation") {

    global $generalobj, $obj;

    /*$iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
		$radius = isset($_REQUEST["Radius"]) ? $_REQUEST["Radius"] : '';
		$sourceLat = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
		$sourceLon = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';

		$str_date = @date('Y-m-d H:i:s', strtotime('-5 minutes'));

		$sql = "SELECT ROUND(( 3959 * acos( cos( radians(".$sourceLat.") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(".$sourceLon.") )
		+ sin( radians(".$sourceLat.") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver`
					WHERE (vLatitude != '' AND vLongitude != '' AND eStatus='Active' AND tLastOnline > '$str_date')
					HAVING distance < ".$radius." ORDER BY `register_driver`";


		$Data = $obj->MySQLSelect($sql);*/


    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $radius = isset($_REQUEST["Radius"]) ? $_REQUEST["Radius"] : '';
    $sourceLat = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $sourceLon = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';

    $str_date = @date('Y-m-d H:i:s', strtotime('-5 minutes'));

    // register_user table
    $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(" . $sourceLon . ") )
		+ sin( radians(" . $sourceLat . ") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_user.*  FROM `register_user`
					WHERE (vLatitude != '' AND vLongitude != '' AND eStatus='Active' AND tLastOnline > '$str_date')
					HAVING distance < " . $radius . " ORDER BY `register_user`.iUserId ASC";


    $Data = $obj->MySQLSelect($sql);
    $storeuser = array();
    $storetrip = array();

    foreach ($Data as $value) {

        $dataofuser = array("Type" => 'Online', "Latitude" => $value['vLatitude'], "Longitude" => $value['vLongitude'], "iUserId" => $value['iUserId']);
        array_push($storeuser, $dataofuser);

    }

    // trip table
    $sql_trip = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( tStartLat ) )
		* cos( radians( tStartLong ) - radians(" . $sourceLon . ") )
		+ sin( radians(" . $sourceLat . ") )
		* sin( radians( tStartLat ) ) ) ),2) AS distance, trips.*  FROM `trips`
					WHERE (tStartLat != '' AND tStartLong != '' AND tTripRequestDate >= DATE_SUB(CURDATE(), INTERVAL 2 HOUR))
					HAVING distance < " . $radius . " ORDER BY `trips`.iTripId DESC";

    $Dataoftrips = $obj->MySQLSelect($sql_trip);

    foreach ($Dataoftrips as $value1) {

        $valuetrip = array("Type" => 'History', "Latitude" => $value1['tStartLat'], "Longitude" => $value1['tStartLong'], "iTripId" => $value1['iTripId']);
        array_push($storetrip, $valuetrip);

    }

    $finaldata = array_merge($storeuser, $storetrip);
    //echo "<pre>"; print_r($finaldata); exit;

    if (count($finaldata) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $finaldata;
    } else {
        $returnData['Action'] = "0";
    }
    echo json_encode($returnData);

}
###########################################################################
###########################################################################
if ($type == "loadPetsType") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');

        $vLanguage = $vLanguage == "" ? "EN" : $vLanguage;

        $petTypes = get_value('pet_type', 'iPetTypeId, vTitle_' . $vLanguage . ' as vTitle', 'eStatus', 'Active');

        $returnData['Action'] = "1";
        $returnData['message'] = $petTypes;
    } else {
        $returnData['Action'] = "0";
    }
    echo json_encode($returnData);
}
###########################################################################

if ($type == "loadUserPets") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;

    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');

    $vLanguage = $vLanguage == "" ? "EN" : $vLanguage;

    $per_page = 10;
    $sql = "SELECT COUNT(iUserPetId) as TotalIds from user_pets WHERE iUserId='" . $iUserId . "'";

    $Data_all = $obj->MySQLSelect($sql);
    $TotalPages = ceil($Data_all[0]['TotalIds'] / $per_page);


    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    $sql = "SELECT up.*,pt.vTitle_" . $vLanguage . " as petType from user_pets as up,  pet_type as pt WHERE pt.iPetTypeId = up.iPetTypeId AND up.iUserId='" . $iUserId . "'" . $limit;
    $Data = $obj->MySQLSelect($sql);

    $totalNum = count($Data);

    if (count($Data) > 0 && $iUserId != "") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = $page + 1;
        } else {
            $returnArr['NextPage'] = "0";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
}
###########################################################################
if ($type == "deleteUserPets") {
    global $generalobj;

    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '0';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';

    $sql = "DELETE FROM user_pets WHERE `iUserPetId`='" . $iUserPetId . "' AND `iUserId`='" . $iUserId . "'";
    $id = $obj->sql_query($sql);
    // echo "ID:".$id;exit;
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
}
###########################################################################
if ($type == "addUserPets") {
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iPetTypeId = isset($_REQUEST["iPetTypeId"]) ? $_REQUEST["iPetTypeId"] : '0';
    $vTitle = isset($_REQUEST["vTitle"]) ? $_REQUEST["vTitle"] : '';
    $vWeight = isset($_REQUEST["vWeight"]) ? $_REQUEST["vWeight"] : '';
    $tBreed = isset($_REQUEST["tBreed"]) ? $_REQUEST["tBreed"] : '';
    $tDescription = isset($_REQUEST["tDescription"]) ? $_REQUEST["tDescription"] : '';

    $Data_pets['iUserId'] = $iUserId;
    $Data_pets['iPetTypeId'] = $iPetTypeId;
    $Data_pets['vTitle'] = $vTitle;
    $Data_pets['vWeight'] = $vWeight;
    $Data_pets['tBreed'] = $tBreed;
    $Data_pets['tDescription'] = $tDescription;

    $id = $obj->MySQLQueryPerform("user_pets", $Data_pets, 'insert');

    if ($id > 0) {
        $returnArr['Action'] = "1";

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
}
###########################################################################
if ($type == "editUserPets") {
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST['iUserPetId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST['iUserId'] : '';
    $iPetTypeId = isset($_REQUEST["iPetTypeId"]) ? $_REQUEST['iPetTypeId'] : '';
    $vTitle = isset($_REQUEST["vTitle"]) ? $_REQUEST['vTitle'] : '';
    $vWeight = isset($_REQUEST["vWeight"]) ? $_REQUEST['vWeight'] : '';
    $tBreed = isset($_REQUEST["tBreed"]) ? $_REQUEST['tBreed'] : '';
    $tDescription = isset($_REQUEST["tDescription"]) ? $_REQUEST['tDescription'] : '';

    $where = " iUserPetId = '" . $iUserPetId . "' AND `iUserId`='" . $iUserId . "'";

    $Data['iUserId'] = $iUserId;
    $Data['iPetTypeId'] = $iPetTypeId;
    $Data['vTitle'] = $vTitle;
    $Data['vWeight'] = $vWeight;
    $Data['tBreed'] = $tBreed;
    $Data['tDescription'] = $tDescription;
    $id = $obj->MySQLQueryPerform("user_pets", $Data, 'update', $where);


    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
}
###########################################################################
if ($type == "loadPetDetail") {
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST['iUserPetId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST['iUserId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST['iDriverId'] : '';


    $vLanguage = get_value('register_user', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    $sql = "SELECT up.*,pt.vTitle_" . $vLanguage . " as petTypeName from user_pets as up,  pet_type as pt WHERE pt.iPetTypeId = up.iPetTypeId AND up.iUserId='" . $iUserId . "' AND up.iUserPetId='" . $iUserPetId . "'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
}
###########################################################################
if ($type == "collectTip") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';

    $tbl_name = "register_user";
    $currencycode = "vCurrencyPassenger";
    $iUserId = "iUserId";
    $eUserType = "Rider";

    $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId, '', 'true');
    $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId, '', 'true');
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId, '', 'true');
    $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $currencyCode, '', 'true');
    $price = $fAmount * $currencyratio;
    $price_new = $price * 100;
    $price_new = round($price_new);
    if ($vStripeCusId == "" || $vStripeToken == "") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        echo json_encode($returnArr);
        exit;
    }

    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $tDescription = "Amount debited";
    $ePaymentStatus = 'Unsettelled';

    $userAvailableBalance = $generalobj->get_user_available_balance($iMemberId, $eUserType);
    if ($userAvailableBalance > $price) {
        $where = " iTripId = '$iTripId'";
        $data['fTipPrice'] = $price;

        $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);

        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $tripId, '', 'true');
        $data_wallet['iUserId'] = $iUserId;
        $data_wallet['eUserType'] = "Rider";
        $data_wallet['iBalance'] = $price;
        $data_wallet['eType'] = "Debit";
        $data_wallet['dDate'] = date("Y-m-d H:i:s");
        $data_wallet['iTripId'] = $iTripId;
        $data_wallet['eFor'] = "Booking";
        $data_wallet['ePaymentStatus'] = "Unsettelled";
        $data_wallet['tDescription'] = "Amount " . $fAmount . " debited from your account for trip number #" . $vRideNo;
        $data_wallet['tDescription'] = "مقدار " . $fAmount . " کسر شده از حساب شما برای سفر با شماره #" . $vRideNo;

        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);

        $returnArr["Action"] = "1";
        echo json_encode($returnArr);
        exit;

    } else if ($price > 50) {
        try {
            $charge_create = Stripe_Charge::create(array(
                "amount" => $price_new,
                "currency" => $currencyCode,
                "customer" => $vStripeCusId,
                "description" => $tDescription
            ));

            $details = json_decode($charge_create);
            $result = get_object_vars($details);
            //echo "<pre>";print_r($result);exit;
            if ($result['status'] == "succeeded" && $result['paid'] == "1") {

                $where = " iTripId = '$iTripId'";
                $data['fTipPrice'] = $price;

                $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);

                $returnArr["Action"] = "1";
                echo json_encode($returnArr);
                exit;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRANS_FAILED";

                echo json_encode($returnArr);
                exit;
            }

        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            $returnArr["Action"] = "0";
            $returnArr['message'] = "LBL_TRANS_FAILED";

            echo json_encode($returnArr);
            exit;
        }

    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_REQUIRED_MINIMUM_AMOUT";
        $returnArr['minValue'] = strval(round(51 * $currencyratio));

        echo json_encode($returnArr);
        exit;
    }


}
###########################################################################

###########################################################################

###########################################################################

###########################################################################

#########################################################################
## NEW WEBSERVICE END ##
##########################################################################
############################ language_master #############################
if ($type == 'language_master') {

    $sql = "SELECT * FROM  `language_master` WHERE eStatus = 'Active' ";
    $all_label = $obj->MySQLSelect($sql);
    $returnArr['language_master_code'] = $all_label;
    echo json_encode($returnArr);
    exit;
}
##########################################################################

if ($type == 'GetLinksConfiguration') {
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';

    if ($UserType == 'Passenger') {
        $DataArr['LINK_FORGET_PASS_PAGE_PASSENGER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_PASSENGER");
        $DataArr['FACEBOOK_APP_ID'] = $generalobj->getConfigurations("configurations", "FACEBOOK_APP_ID");
        $DataArr['CONFIG_CLIENT_ID'] = $generalobj->getConfigurations("configurations", "CONFIG_CLIENT_ID");
        $DataArr['GOOGLE_SENDER_ID'] = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID");
        $DataArr['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");

        echo json_encode($DataArr);
    } else if ($UserType == 'Driver') {
        $DataArr['LINK_FORGET_PASS_PAGE_DRIVER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_DRIVER");
        $DataArr['LINK_SIGN_UP_PAGE_DRIVER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_SIGN_UP_PAGE_DRIVER");
        $DataArr['GOOGLE_SENDER_ID'] = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID");
        $DataArr['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");

        echo json_encode($DataArr);
    }
}

##########################################################################

if ($type == 'UpdateLanguageCode') {

    $lCode = isset($_REQUEST['vCode']) ? clean(strtoupper($_REQUEST['vCode'])) : ''; // User's prefered language
    $UserID = isset($_REQUEST['UserID']) ? clean($_REQUEST['UserID']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';

    if ($UserType == "Passenger") {

        $where = " iUserId = '$UserID'";
        $Data_update_passenger['vLang'] = $lCode;

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
        // echo $id; exit;
        if ($id < 0) {
            echo "UpdateFailed";
            exit;
        }
    } else if ($UserType == "Driver") {
        $where = " iDriverId = '$UserID'";
        $Data_update_driver['vLang'] = $lCode;

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
        // echo $id; exit;
        if ($id < 0) {
            echo "UpdateFailed";
            exit;
        }
    }

    /* find default language of website set by admin */
    if ($lCode == '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);

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
    $x['vCode'] = $lCode; // to check in which languge code it is loading

    echo json_encode($x);

}

##########################################################################


/* get variables value directly */
if ($type == 'get_value') {
    global $obj;
    $returnArr = array();
    $table = isset($_REQUEST['table']) ? clean($_REQUEST['table']) : '';
    $field_name = isset($_REQUEST['field_name']) ? clean($_REQUEST['field_name']) : '';
    $condition_field = isset($_REQUEST['condition_field']) ? clean($_REQUEST['condition_field']) : '';
    $condition_value = isset($_REQUEST['condition_value']) ? clean($_REQUEST['condition_value']) : '';

    $where = ($condition_field != '') ? ' WHERE ' . $condition_field : '';
    $where .= ($where != '' && $condition_value != '') ? ' = "' . $condition_value . '"' : '';

    $returnArr = get_value($table, $field_name, $condition_field, $condition_value);

    echo json_encode($returnArr);
    exit;
}


############################## Get DriverDetail ###################################
if ($type == "getDriverDetail") {


    $Did = isset($_REQUEST["DriverAutoId"]) ? $_REQUEST["DriverAutoId"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';

    $sql = "SELECT iGcmRegId FROM `register_driver` WHERE iDriverId='$Did'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {

        $iGCMregID = $Data[0]['iGcmRegId'];

        if ($GCMID != '') {

            if ($iGCMregID != $GCMID) {
                $where = " iDriverId = '$Did' ";

                $Data_update_driver['iGcmRegId'] = $GCMID;

                $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
            }

        }

    }

    echo json_encode(getDriverDetailInfo($Did));

    exit;

}
###########################################################################

######################## Get Driver Car Detail ############################
if ($type == "getDriverCarDetail") {
    $Did = isset($_REQUEST["DriverAutoId"]) ? $_REQUEST["DriverAutoId"] : '';

    $sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverId='$Did' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'";

    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {

        $i = 0;
        while (count($Data) > $i) {

            $Data[$i]['vModel'] = $Data[$i]['vTitle'];
            $i++;
        }

        $returnArr['carList'] = $Data;

        echo json_encode($returnArr);
    } else {
        $returnArr['action'] = 0; //duplicate entry
        $returnArr['message'] = 'Fail';

        echo json_encode($returnArr);
    }

}
###########################################################################


###########################################################################


############################ checkUser_FB ################################

if ($type == "checkUser_FB") {

    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
    $cityName = isset($_REQUEST["cityName"]) ? $_REQUEST["cityName"] : '';
    $emailId = isset($_REQUEST["emailId"]) ? $_REQUEST["emailId"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';
    $autoSign = isset($_REQUEST["autoSign"]) ? $_REQUEST["autoSign"] : '';


    if ($fbid == '') {
        echo "LBL_NO_REG_FOUND";
        exit;
    }

    $sql = "SELECT iUserId,eStatus,iGcmRegId FROM `register_user` WHERE vFbId=" . $fbid . " OR vEmail='$emailId'";
    $row = $obj->MySQLSelect($sql);

    if (count($row) > 0) {
        if ($row[0]['eStatus'] == "Active") {
            if ($autoSign == "true") {
                $iGCMregID = $row[0]['iGcmRegId'];

                if ($GCMID != '') {

                    if ($iGCMregID != $GCMID) {

                        $iUserID_passenger = $row[0]['iUserId'];
                        $where = " iUserId = '$iUserID_passenger' ";

                        $Data_update_passenger['iGcmRegId'] = $GCMID;

                        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                    }

                }

            } else {
                if ($GCMID != '') {
                    $iUserId_passenger = $row[0]['iUserId'];
                    $where = " iUserId = '$iUserId_passenger' ";

                    $Data_update_passenger['iGcmRegId'] = $GCMID;

                    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                }
            }

            echo json_encode(getPassengerDetailInfo($row[0]['iUserId'], $cityName));
        } else {
            echo "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
        }

    } else {
        echo "LBL_NO_REG_FOUND";
    }

}

###########################################################################

if ($type == 'checkFacebookUser') {
    $FbID = isset($_REQUEST["FbID"]) ? $_REQUEST["FbID"] : '';
    $EmailID = isset($_REQUEST["EmailID"]) ? $_REQUEST["EmailID"] : '';

    $sql = "SELECT iUserId FROM `register_user` WHERE vFbId=" . $FbID . " OR vEmail='$EmailID' ";
    $row = $obj->MySQLSelect($sql);

    if (count($row) > 0) {
        echo "Failed";
    } else {
        echo "success";
    }
    exit;
}

###########################################################################

######################### checkUser_passenger #############################

if ($type == "checkUser_passenger") {

    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '';

    $sql = "SELECT vEmail,vPhone FROM `register_user` WHERE vEmail = '$Emid' OR vPhone = '$Phone'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {

        if ($Emid == $Data[0]['vEmail']) {
            echo "EMAIL_EXIST";
        } else {
            echo "MOBILE_EXIST";
        }
    } else {
        echo "NO_REG_FOUND";
    }

}
###########################################################################

######################## getDriverDetail_signIN ###########################

if ($type == "getDriverDetail_signIN") {
    $Driver_email = $_REQUEST["DriverId"];
    $Password_driver = $generalobj->encrypt($_REQUEST["Pass"]);
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';

    //Logger($_REQUEST);

    $DeviceType = "Android";
    $sql = "SELECT rd.iDriverId,rd.eStatus,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE rd.vEmail='$Driver_email'  AND rd.vPassword='$Password_driver' AND cmp.iCompanyId=rd.iCompanyId";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {


        if ($Data[0]['eStatus'] != "Deleted") {
            if ($GCMID != '') {

                $iDriverId_driver = $Data[0]['iDriverId'];
                $where = " iDriverId = '$iDriverId_driver' ";

                $Data_update_driver['iGcmRegId'] = $GCMID;
                $Data_update_driver['eDeviceType'] = $DeviceType;

                $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

            }
            echo json_encode(getDriverDetailInfo($Data[0]['iDriverId'], 1));

        } else {
            echo "ACC_DELETED";
        }
    } else {
        $sql = "SELECT * FROM `register_driver` WHERE vEmail='$Driver_email'";
        $num_rows_Email = $obj->MySQLSelect($sql);
        if (count($num_rows_Email) == 1) {
            echo "LBL_PASSWORD_ERROR_TXT";

        } else {
            echo "LBL_NO_REG_FOUND";
        }
    }

}

###########################################################################

###########################################################################
if ($type == "getDetail_signIN_passenger") {

    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Password_user = isset($_REQUEST["Pass"]) ? $_REQUEST["Pass"] : '';
    $cityName = isset($_REQUEST["cityName"]) ? $_REQUEST["cityName"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';

    $Password_passenger = $generalobj->encrypt($Password_user);

    $DeviceType = "Android";

    $sql = "SELECT iUserId,eStatus,vLang,vTripStatus FROM `register_user` WHERE vEmail='$Emid'  && vPassword='$Password_passenger'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        if ($Data[0]['eStatus'] == "Active") {


            $iUserId_passenger = $Data[0]['iUserId'];
            $where = " iUserId = '$iUserId_passenger' ";

            if ($GCMID != '') {

                $Data_update_passenger['iGcmRegId'] = $GCMID;
                $Data_update_passenger['eDeviceType'] = $DeviceType;

                $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
            }

            if ($Data[0]['vTripStatus'] == "Requesting") {

                $Data_update_tripStatus['vTripStatus'] = "Not Requesting";

                $id = $obj->MySQLQueryPerform("register_user", $Data_update_tripStatus, 'update', $where);
            }

            $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
            $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


            if ($Data_checkLangCode[0]['vCode'] != $Data[0]['vLang']) {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
            } else {
                $returnArr['changeLangCode'] = "No";
            }

            $returnArr['ProfileData'] = getPassengerDetailInfo($Data[0]['iUserId'], $cityName);
            echo json_encode($returnArr);
        } else {
            if ($Data[0]['eStatus'] != "Deleted") {
                echo "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            } else {
                echo "ACC_DELETED";
            }
        }

    } else {
        $sql = "SELECT * FROM `register_user` WHERE vEmail='$Emid'";
        $num_rows_Email = $obj->MySQLSelect($sql);
        if (count($num_rows_Email) == 1) {
            echo "LBL_PASSWORD_ERROR_TXT";
        } else {
            echo "LBL_NO_REG_FOUND";
        }
    }

}
###########################################################################


###########################################################################

###########################################################################
if ($type == "getFareConfigurations") {


    $configurations = array();
    $configurations["LBL_PAYMENT_ENABLED"] = $generalobj->getConfigurations("configurations", "PAYMENT_ENABLED");
    $configurations["LBL_BASE_FARE"] = $generalobj->getConfigurations("configurations", "BASE_FARE");
    $configurations["LBL_FARE_PER_MINUTE"] = $generalobj->getConfigurations("configurations", "FARE_PER_MINUTE");
    $configurations["LBL_FARE_PAR_KM"] = $generalobj->getConfigurations("configurations", "FARE_PAR_KM");
    $configurations["LBL_SERVICE_TAX"] = $generalobj->getConfigurations("configurations", "SERVICE_TAX");

    echo json_encode($configurations);
}
###########################################################################


//**********************Update Details************************************//
###########################################################################

if ($type == "updatePassengerGcmID") {
    $user_id_auto = isset($_REQUEST["UidAuto"]) ? $_REQUEST['UidAuto'] : '';
    $GcmID = isset($_REQUEST["GcmId"]) ? $_REQUEST['GcmId'] : '';

    $where = " iUserId = '" . $user_id_auto . "'";
    $Data['iGcmRegId'] = $GcmID;
    $id = $obj->MySQLQueryPerform("register_user", $Data, 'update', $where);


    if ($id) {
        echo "Update Successful..";
    } else {
        echo "No Update.";
    }

}
###########################################################################

###########################################################################
if ($type == "updateDriverGcmID") {
    $user_id_auto = isset($_REQUEST["UidAuto"]) ? $_REQUEST['UidAuto'] : '';
    $GcmID = isset($_REQUEST["GcmId"]) ? $_REQUEST['GcmId'] : '';

    $where = " iDriverId = '" . $user_id_auto . "'";
    $Data['iGcmRegId'] = $GcmID;
    $id = $obj->MySQLQueryPerform("register_driver", $Data, 'update', $where);


    if ($id) {
        echo "Update Successful..";
    } else {
        echo "No Update.";
    }

}
###########################################################################


###########################################################################

if ($type == "getTripIdFor_driver") {


    $driver_id = isset($_REQUEST["driver_id"]) ? $_REQUEST["driver_id"] : '';

    $sql = "SELECT iTripId FROM `register_driver` WHERE iDriverId = '$driver_id'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) == 1) {
        $current_trip_id = $Data[0]['iTripId'];
    }
    echo $current_trip_id;

}

###########################################################################
if ($type == "updateUserImage") {

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $UIpath = isset($_REQUEST["Path"]) ? $_REQUEST["Path"] : '';

    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vImgName'] = $UIpath;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);


    if ($id) {
        echo "Update Successful..";
    } else {

        echo "Failed.";
    }

}
###########################################################################

if ($type == "updateDriverImage") {

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $UIpath = isset($_REQUEST["Path"]) ? $_REQUEST["Path"] : '';

    $where = " iDriverId = '$user_id_auto'";
    $Data_update_driver['vImage'] = $UIpath;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id) {
        echo "Update Successful..";
    } else {

        echo "Failed.";
    }

}

###########################################################################

if ($type == "UpdateLastOnline_Driver") {

    $Did = isset($_REQUEST["DriverAutoId"]) ? $_REQUEST["DriverAutoId"] : '';
    $availabilityStatus = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';

    $where = " iDriverId='$Did'";

    $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
    $Data_update_driver['vAvailability'] = $availabilityStatus;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id > 0) {
        echo "UpdateSuccessful";
    } else {
        echo "Failed";
    }
}
###########################################################################
###########################################################################

if ($type == "update_pass_passenger_Detail") {
    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';

    $Password_passenger = $generalobj->encrypt($Upass);
    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vPassword'] = $Password_passenger;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);


    if ($id > 0) {

        echo json_encode(getPassengerDetailInfo($user_id_auto, "none"));

    } else {

        echo "Failed.";
    }

}

###########################################################################

if ($type == "update_pass_Detail_driver") {
    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';

    $Password_driver = $generalobj->encrypt($Upass);

    $where = " iDriverId = '$user_id_auto'";
    $Data_update_driver['vPassword'] = $Password_driver;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);


    if ($id > 0) {
        echo json_encode(getDriverDetailInfo($user_id_auto));
    } else {
        echo "Failed.";
    }

}

###########################################################################

if ($type == "update_payment_Detail_passenger") {

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $UcrdNO = isset($_REQUEST["crd_no"]) ? $_REQUEST["crd_no"] : '';
    $UexMonth = isset($_REQUEST["expMonth"]) ? $_REQUEST["expMonth"] : '';
    $UexYear = isset($_REQUEST["expYear"]) ? $_REQUEST["expYear"] : '';
    $UCVV = isset($_REQUEST["cvv_no"]) ? $_REQUEST['cvv_no'] : '';


    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vCreditCard'] = $UcrdNO;
    $Data_update_passenger['vExpMonth'] = $UexMonth;
    $Data_update_passenger['vExpYear'] = $UexYear;
    $Data_update_passenger['vCvv'] = $UCVV;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);


    if ($id) {
        echo "Update Successful..";
    } else {

        echo "No Update.";
    }

}

###########################################################################

if ($type == "update_payment_Detail_driver") {

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $UcrdNO = isset($_REQUEST["crd_no"]) ? $_REQUEST["crd_no"] : '';
    $UexMonth = isset($_REQUEST["expMonth"]) ? $_REQUEST["expMonth"] : '';
    $UexYear = isset($_REQUEST["expYear"]) ? $_REQUEST["expYear"] : '';
    $UCVV = isset($_REQUEST["cvv_no"]) ? $_REQUEST['cvv_no'] : '';


    $where = " iDriverId = '$user_id_auto'";
    $Data_update_driver['vCreditCard'] = $UcrdNO;
    $Data_update_driver['vExpMonth'] = $UexMonth;
    $Data_update_driver['vExpYear'] = $UexYear;
    $Data_update_driver['vCvv'] = $UCVV;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);


    if ($id) {
        echo "Update Successful..";
    } else {

        echo "No Update.";
    }

}

###########################################################################

if ($type == "updateName_Mobile_Detail_passenger") {

    $Fname = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
    $Lname = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
    $Umobile = isset($_REQUEST["mobile"]) ? $_REQUEST["mobile"] : '';
    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST['user_id'] : '';
    $phoneCode = isset($_REQUEST["phoneCode"]) ? $_REQUEST['phoneCode'] : '';


    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vName'] = $Fname;
    $Data_update_passenger['vLastName'] = $Lname;
    $Data_update_passenger['vPhone'] = $Umobile;
    $Data_update_passenger['vPhoneCode'] = $phoneCode;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    if ($id > 0) {
        echo json_encode(getPassengerDetailInfo($user_id_auto, "none"));
    } else {
        echo "Failed.";
    }

}


###########################################################################

if ($type == "updateName_Mobile_Detail_driver") {

    $Fname = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
    $Lname = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
    $Umobile = isset($_REQUEST["mobile"]) ? $_REQUEST["mobile"] : '';
    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST['user_id'] : '';
    $phoneCode = isset($_REQUEST["phoneCode"]) ? $_REQUEST['phoneCode'] : '';


    $where = " iDriverId = '$user_id_auto'";
    $Data_update_driver['vName'] = $Fname;
    $Data_update_driver['vLastName'] = $Lname;
    $Data_update_driver['vPhone'] = $Umobile;
    $Data_update_driver['vCode'] = $phoneCode;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);


    if ($id > 0) {
        echo json_encode(getDriverDetailInfo($user_id_auto));
    } else {
        echo "Failed.";
    }
}

###########################################################################

if ($type == "uploadImage_driver") {

    $target_path = "webimages/upload/";
    $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    $base = isset($_REQUEST['image']) ? $_REQUEST['image'] : '';
    $name = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
    $target_path_temp = $target_path . "Driver/";
    $target_path = $target_path_temp . $user_id . "/";

    if (is_dir($target_path) === false) {
        mkdir($target_path, 0755);
    }
    // base64 encoded utf-8 string
    $binary = base64_decode($base);

    header('Content-Type: bitmap; charset=utf-8');

    $time_val = time();
    $img_arr = explode(".", $name);
    $fileextension = $img_arr[count($img_arr) - 1];

    $Random_filename = mt_rand(11111, 99999);
    // $ImgFileName="3_".$name;
    $ImgFileName = $time_val . "_" . $Random_filename . "." . $fileextension;

    $file = fopen($target_path . '/' . $ImgFileName, "w");

    fwrite($file, $binary);
    fclose($file);

    $path = $target_path . $ImgFileName;


    if (file_exists($path)) {

        $where = " iDriverId = '" . $user_id . "'";
        $Data_Driver['vImage'] = $ImgFileName;
        $id = $obj->MySQLQueryPerform("register_driver", $Data_Driver, 'update', $where);

        if ($id > 0) {
            // echo "UPLOADSUCCESS";
            $thumb->createthumbnail($target_path . '/' . $ImgFileName); // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size1"]);    // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);
            $thumb->save($target_path . "1" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_path . "/" . $ImgFileName);   // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size2"]);       // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);      // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_path . "2" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_path . "/" . $ImgFileName);   // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size3"]);       // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);      // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_path . "3" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $returnArrayImg['Action'] = "SUCCESS";
            $returnArrayImg['ImgName'] = '3_' . $ImgFileName;
            echo json_encode($returnArrayImg);
        } else {
            echo "Failed";
        }

    } else {
        // handle the error

        echo "Failed";
    }

    exit;

}

###########################################################################

if ($type == "uploadImage_passenger") {

    $target_path = "webimages/upload/";
    $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    $base = isset($_REQUEST['image']) ? $_REQUEST['image'] : '';
    $name = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';

    $target_path_temp = $target_path . "Passenger/";
    $target_path = $target_path_temp . $user_id . "/";

    if (is_dir($target_path) === false) {
        mkdir($target_path, 0777);
    }
    // base64 encoded utf-8 string
    $binary = base64_decode($base);
    // binary, utf-8 bytes
    header('Content-Type: bitmap; charset=utf-8');

    $time_val = time();
    $img_arr = explode(".", $name);
    $fileextension = $img_arr[count($img_arr) - 1];

    $Random_filename = mt_rand(11111, 99999);
    // $ImgFileName="3_".$name;
    $ImgFileName = $time_val . "_" . $Random_filename . "." . $fileextension;

    $file = fopen($target_path . '/' . $ImgFileName, "w");

    fwrite($file, $binary);
    fclose($file);

    $path = $target_path . $ImgFileName;

    if (file_exists($path)) {

        $where = " iUserId = '" . $user_id . "'";
        $Data_passenger['vImgName'] = $ImgFileName;
        $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where);

        if ($id > 0) {
            // echo "UPLOADSUCCESS";
            $thumb->createthumbnail($target_path . '/' . $ImgFileName); // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size1"]);    // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);
            $thumb->save($target_path . "1" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_path . "/" . $ImgFileName);   // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size2"]);       // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);      // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_path . "2" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_path . "/" . $ImgFileName);   // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size3"]);       // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);      // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_path . "3" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $returnArrayImg['Action'] = "SUCCESS";
            $returnArrayImg['ImgName'] = '3_' . $ImgFileName;
            echo json_encode($returnArrayImg);
            //exit;
        } else {
            echo "Failed";
        }
    } else {
        echo "Failed";
    }

}


###########################################################################


###########################################################################

if ($type == "registerFbUser") {
    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
    $Fname = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
    $Lname = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
    $email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';
    $phone_mobile = isset($_REQUEST["phone"]) ? $_REQUEST["phone"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $PhoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';

    // $Language_Code=($obj->MySQLSelect("SELECT `vCode` FROM `language_master` WHERE `eDefault`='Yes'")[0]['vCode']);
    $Language_Code = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $deviceType = "Android";

    $sql = "SELECT * FROM `register_user` WHERE vEmail = '$email' OR vPhone = '$phone_mobile'";
    $check_passenger = $obj->MySQLSelect($sql);

    if (count($check_passenger) > 0) {
        if ($email == $check_passenger[0]['vEmail']) {
            echo "EMAIL_EXIST";
        } else {
            echo "MOBILE_EXIST";
        }
    } else {

        $Data_passenger['vFbId'] = $fbid;
        $Data_passenger['vName'] = $Fname;
        $Data_passenger['vLastName'] = $Lname;
        $Data_passenger['vEmail'] = $email;
        $Data_passenger['vPhone'] = $phone_mobile;
        $Data_passenger['vPassword'] = '';
        $Data_passenger['iGcmRegId'] = $GCMID;
        $Data_passenger['vLang'] = $Language_Code;
        $Data_passenger['vPhoneCode'] = $PhoneCode;
        $Data_passenger['vCountry'] = $CountryCode;
        $Data_passenger['eDeviceType'] = $deviceType;
        // $Data_passenger['vCurrencyPassenger']=($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
        $Data_passenger['vCurrencyPassenger'] = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

        $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'insert');

        if ($id > 0) {
            /*new added*/

            echo json_encode(getPassengerDetailInfo($id, $cityName));

            $maildata['EMAIL'] = $email;
            $maildata['NAME'] = $Fname;
            $maildata['PASSWORD'] = $password;
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
        } else {
            echo "Registration UnSuccessful.";
        }
    }
}

###########################################################################
###########################################################################

###########################################################################


if ($type == "setVehicleTypes") {
    // $startDate="2016-04-04 14:33:58";

    // echo date('dS M \a\t h:i a',strtotime($startDate));
    // $value= get_value('user_emergency_contact', 'COUNT(iEmergencyId) as Count', 'iUserId', "34");
    // echo $value[0]['Count'];
    // echo $res = preg_replace("/[^0-9]/", "", "Every 6.1,0--//+2 Months" );

    /* $tripID    = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
        $rating  = isset($_REQUEST["rating"]) ? $_REQUEST["rating"] : '';

$iUserId =get_value('trips', 'iUserId', 'iTripId',$tripID,'','true');
                $tableName = "register_user";
                $where = " WHERE iUserId='".$iUserId."'";

 $sql = "SELECT vAvgRating FROM ".$tableName.' '.$where;
            $fetchAvgRating= $obj->MySQLSelect($sql);



            $fetchAvgRating[0]['vAvgRating'] = floatval($fetchAvgRating[0]['vAvgRating']);
			// echo  "Fetch:".$fetchAvgRating[0]['vAvgRating'];exit;

			if($fetchAvgRating[0]['vAvgRating'] > 0){
				$average_rating = round(($fetchAvgRating[0]['vAvgRating'] + $rating) / 2,1);
			}else{
				$average_rating = round($fetchAvgRating[0]['vAvgRating'] + $rating,1);
			}

            $Data_update['vAvgRating']=$average_rating;

		echo "AvgRate:".$Data_update['vAvgRating']; */

    $langCodesArr = get_value('language_master', 'vCode', '', '');

    print_r($langCodesArr);

    echo "<BR/>";

    for ($i = 0; $i < count($langCodesArr); $i++) {
        $currLngCode = $langCodesArr[$i]['vCode'];
        $vVehicleType = $langCodesArr[$i]['vVehicleType'];
        $fieldName = "vVehicleType_" . $currLngCode;
        $suffixName = $i == 0 ? "vVehicleType" : "vVehicleType_" . $langCodesArr[$i - 1]['vCode'];


        $sql = "ALTER TABLE vehicle_type ADD " . $fieldName . " VARCHAR(50) AFTER" . " " . $suffixName;
        $id = $obj->sql_query($sql);
    }


    $vehicleTypesArr = get_value('vehicle_type', 'vVehicleType,iVehicleTypeId', '', '');

    for ($j = 0; $j < count($vehicleTypesArr); $j++) {
        $vVehicleType = $vehicleTypesArr[$j]['vVehicleType'];
        $iVehicleTypeId = $vehicleTypesArr[$j]['iVehicleTypeId'];

        echo "vVehicleType:" . $vVehicleType . "<BR/>";
        for ($k = 0; $k < count($langCodesArr); $k++) {
            $currLngCode = $langCodesArr[$k]['vCode'];
            $fieldName = "vVehicleType_" . $currLngCode;
            $suffixName = $k == 0 ? "vVehicleType" : "vVehicleType_" . $langCodesArr[$k - 1]['vCode'];


            // $sql = "ALTER TABLE vehicle_type ADD ".$fieldName." VARCHAR(50) AFTER"." ".$suffixName;
            // $id= $obj->sql_query($sql);
            echo $sql = "UPDATE `vehicle_type` SET " . $fieldName . " = '" . $vVehicleType . "' WHERE iVehicleTypeId = '$iVehicleTypeId'";
            echo "<br/>";
            $id1 = $obj->sql_query($sql);

            echo "<br/>" . $id1;
        }

    }

    // echo $sql = "UPDATE `vehicle_type` SET ".$fieldName." = ".$vVehicleType;
    // $id1= $obj->sql_query($sql);
    // echo "<br/>".$id;

}
###########################################################################

if ($type == "callToDriver_Message") {

    $driver_id_auto = isset($_REQUEST["DautoId"]) ? $_REQUEST["DautoId"] : '';
    $user_id_auto = isset($_REQUEST["UautoId"]) ? $_REQUEST["UautoId"] : '';
    $message_rec = isset($_REQUEST["message_rec"]) ? $_REQUEST["message_rec"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';

    $sender_type = "Passenger";

    $where = " iUserId = '$user_id_auto'";

    $Data_update_Messages['tMessage'] = $message;
    $Data_update_Messages['tSendertype'] = $sender_type;
    $Data_update_Messages['iTripId'] = $tripID;

    $id = $obj->MySQLQueryPerform("driver_user_messages", $Data_update_Messages, 'insert');

    $message_new_combine = $message_rec . $message;

    $DArray = explode(',', $driver_id_auto);

    foreach ($DArray as $key => $val) {

        $sql = "SELECT iGcmRegId FROM register_driver WHERE iDriverId='$val'  AND eDeviceType = 'Android'";
        $result = $obj->MySQLSelect($sql);

        $rows[] = $result[0];

    }


    foreach ($rows as $item) {

        $registatoin_ids = $item['iGcmRegId'];


        $Rregistatoin_ids = array(
            $registatoin_ids
        );

        $Rmessage = array(
            "message" => $message_new_combine
        );
        $result = send_notification($Rregistatoin_ids, $Rmessage);

        echo $result;
    }

}

###########################################################################

if ($type == "callToUser_Message") {

    $driver_id_auto = isset($_REQUEST["DautoId"]) ? $_REQUEST["DautoId"] : '';
    $user_id_auto = isset($_REQUEST["UautoId"]) ? $_REQUEST["UautoId"] : '';
    $message_rec = isset($_REQUEST["message_rec"]) ? $_REQUEST["message_rec"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';

    $sender_type = "Driver";

    $Data_update_Messages['tMessage'] = $message;
    $Data_update_Messages['tSendertype'] = $sender_type;
    $Data_update_Messages['iTripId'] = $tripID;

    $id = $obj->MySQLQueryPerform("driver_user_messages", $Data_update_Messages, 'insert');

    $message_new_combine = $message_rec . $message;

    $sql = "SELECT iGcmRegId FROM register_user WHERE iUserId='$user_id_auto'  AND eDeviceType = 'Android'";
    $result = $obj->MySQLSelect($sql);

    $registatoin_ids = $result[0]['iGcmRegId'];

    $Rregistatoin_ids = array(
        $registatoin_ids
    );
    $Rmessage = array(
        "message" => $message_new_combine
    );
    $result = send_notification($Rregistatoin_ids, $Rmessage);

    echo $result;


}

###########################################################################

if ($type == "submit_rating_user") {

    $usr_email = isset($_REQUEST["usr_email"]) ? $_REQUEST["usr_email"] : '';
    $driver_id = isset($_REQUEST["driver_id"]) ? $_REQUEST["driver_id"] : '';
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating_1 = isset($_REQUEST["rating_1"]) ? $_REQUEST["rating_1"] : '';

    $message = isset($_REQUEST["message"]) ? $_REQUEST['message'] : '';
    $tripVerificationCode = isset($_REQUEST["verification_code"]) ? $_REQUEST['verification_code'] : '';

    $average_rating = $rating_1;

    $sql = "SELECT iVerificationCode FROM `trips`  WHERE  iTripId='$tripID'";
    $row_code = $obj->MySQLSelect($sql);

    $verificationCode = $row_code[0]['iVerificationCode'];

    // if($tripVerificationCode==$verificationCode){

    $VerificationStatus = "Verified";


    $where = " iTripId = '$tripID'";

    $Data_update_trips['eVerified'] = $VerificationStatus;

    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);


    $sql = "SELECT iUserId,vAvgRating,vName,vLastName,vPhone FROM `register_user` WHERE iUserId='$usr_email'";
    $row = $obj->MySQLSelect($sql);

    // add by seyyed amir
    $passenger = $row[0];

    $average_rating = ($row[0]['vAvgRating'] + $average_rating) / 2;

    $usrType = "Driver";

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' && eUserType = '$usrType'";
    $row = $obj->MySQLSelect($sql);


    if (count($row) > 0) {
        echo "LBL_RATING_EXIST";

    } else {

        $where = " iUserId = '$usr_email'";

        $Data_update_passenger['vAvgRating'] = round($average_rating, 1);

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);


        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating_1;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $usrType;


        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

        // add by seyyed amir
        //if(intval($rating_1) < 6)
        {
            //$telegram['شماره سفر']  = $tripID;
            //$telegram['نمره']       = $rating_1;
            //$telegram['نوع یوزر']   = "Passenger  مسافر";
            $telegram['زمان'] = jdate("Y-m-d H:i:s");
            $telegram['نام مسافر'] = $passenger['vName'] . ' ' . $passenger['vLastName'];
            $telegram['آی دی مسافر'] = $passenger['iUserId'];
            $telegram['شماره موبایل'] = $passenger['vPhone'];
            $telegram['پیام'] = $message . "\n";
            $telegram['لینک سفر'] = '<a href="' . $tconfig["tsite_url"] . 'admin/invoice.php?iTripId=' . $tripID . '">کلیک کنید</a>';
            $tgb = new TelegramBot();
            $tgb->sendRate($telegram, $rating_1);
        }
        /////////////////////////////

        if ($id > 0) {
            echo "Ratings Successful.";
        } else {

            echo "Ratings UnSuccessful.";
        }
        sendTripReceiptAdmin($tripID);
    }


}

###########################################################################

if ($type == "submit_rating_driver") {

    $usr_email = isset($_REQUEST["usr_email"]) ? $_REQUEST["usr_email"] : '';
    $driver_id = isset($_REQUEST["driver_id"]) ? $_REQUEST["driver_id"] : '';
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating_1 = isset($_REQUEST["rating_1"]) ? $_REQUEST["rating_1"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST['message'] : '';
    $tripVerificationCode = isset($_REQUEST["verification_code"]) ? $_REQUEST['verification_code'] : '';
    //$average_rating=($rating_1+$rating_2+$rating_3+$rating_4)/4 ;

    $average_rating = $rating_1;

    $usrType = "Passenger";

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' and eUserType = '$usrType'";
    $row_check = $obj->MySQLSelect($sql);

    $sql = "SELECT iDriverId,vAvgRating,vName,vLastName,vPhone FROM `register_driver` WHERE iDriverId = '$driver_id'";
    $row = $obj->MySQLSelect($sql);

    // add by seyyed amir
    $driver = $row[0];

    $average_rating = ($row[0]['vAvgRating'] + $average_rating) / 2;


    if (count($row_check) > 0) {

        echo "LBL_RATING_EXIST";

    } else {

        $where = " iDriverId = '$driver_id'";

        $Data_update_driver['vAvgRating'] = round($average_rating, 1);

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating_1;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $usrType;

        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

        // add by seyyed amir
        //if(intval($rating_1) < 6)
        {
            //$telegram['شماره سفر']  = $tripID;
            //$telegram['نمره']       = $rating_1;
            //$telegram['نوع یوزر']   = "Driver راننده";
            $telegram['زمان'] = jdate("Y-m-d H:i:s");
            $telegram['نام راننده'] = $driver['vName'] . ' ' . $driver['vLastName'];
            $telegram['آی دی راننده'] = $driver['iDriverId'];
            $telegram['شماره موبایل'] = $driver['vPhone'];
            $telegram['پیام'] = $message . "\n";
            $telegram['لینک سفر'] = '<a href="' . $tconfig["tsite_url"] . 'admin/invoice.php?iTripId=' . $tripID . '">کلیک کنید</a>';
            $tgb = new TelegramBot();
            $tgb->sendRate($telegram, $rating_1);
        }
        ///////////////////////////////

        if ($id) {
            echo "Ratings Successful.";
        } else {

            echo "Ratings UnSuccessful.";
        }

        sendTripReceipt($tripID);

    }

}

###########################################################################

if ($type == "updateLog") {
    $Uid = isset($_REQUEST["access_sign_token_user_id_auto"]) ? $_REQUEST["access_sign_token_user_id_auto"] : '';

    $where = " iUserId='$Uid'";
    $Data_update_passenger['vLogoutDev'] = "false";

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    if ($id) {
        echo "Update Successful";
    }

}


###########################################################################

if ($type == 'getCarTypes') {
    $sql = "SELECT * FROM vehicle_type";

    $row_result_vehivle_type = $obj->MySQLSelect($sql);

    $arr_temp['Types'] = $row_result_vehivle_type;
    echo json_encode($arr_temp);
}


###########################################################################

###########################################################################

if ($type == 'CheckVerificationCode') {
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';

    $sql = "SELECT eVerified FROM trips WHERE iTripId=$tripId";

    $result_eVerified = $obj->MySQLSelect($sql);

    if ($result_eVerified[0]['eVerified'] == "Verified") {
        echo "Verified";
    } else {
        echo "Not Verified";
    }

}
###########################################################################
###########################################################################

if ($type == 'AddPaypalPaymentData') {
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $PayPalPaymentId = isset($_REQUEST["PayPalPaymentId"]) ? $_REQUEST["PayPalPaymentId"] : '';
    $PaidAmount = isset($_REQUEST["PaidAmount"]) ? $_REQUEST["PaidAmount"] : '';


    $Data_payments['tPaymentUserID'] = $PayPalPaymentId;
    $Data_payments['vPaymentUserStatus'] = "approved";
    $Data_payments['iTripId'] = $tripId;
    $Data_payments['iAmountUser'] = $PaidAmount;


    $id = $obj->MySQLQueryPerform("payments", $Data_payments, 'insert');

    if ($id > 0) {
        echo "PaymentSuccessful";
    } else {
        echo "PaymentUnSuccessful";
    }

}

####################### To get Currency Values ##############################

if ($type == "getCurrencyList") {
    // $returnArr['List']=($obj->MySQLSelect("SELECT * FROM currency WHERE eStatus='Active'"));
    $returnArr['List'] = get_value('currency', '*', 'eStatus', 'Active');
    echo json_encode($returnArr);
}

####################### To get Currency Values END############################


####################### Update Currency Values ##############################

if ($type == "updateCurrencyValue") {
    $Uid = isset($_REQUEST["UserID"]) ? $_REQUEST["UserID"] : '';
    $currencyCode = isset($_REQUEST["vCurrencyCode"]) ? $_REQUEST["vCurrencyCode"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';

    if ($UserType == "Driver") {
        $where = " iDriverId = '$Uid'";
        $Data_update_user['vCurrencyDriver'] = $currencyCode;
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);
    } else {
        $where = " iUserId = '$Uid'";
        $Data_update_user['vCurrencyPassenger'] = $currencyCode;
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_user, 'update', $where);
    }


    if ($id) {
        echo "SUCCESS";
    } else {
        echo "UpdateFailed";
    }
}

####################### To get Currency Values END############################


if ($type == "enc_pass") {
    $pass = isset($_REQUEST['pass']) ? clean($_REQUEST['pass']) : '';

    echo $generalobj->encrypt($pass);
}

if ($type == "DeclineTripRequest") {
    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';

    $request_count = UpdateDriverRequest($driver_id, $passenger_id, "0", "Decline");

    echo $request_count;
}

###########################################################################
###########################################################################
######################       S V A R . I R       ##########################
###########################################################################

if ($type == "addMoneyUserWalletIranBanks") {

    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';  //Passenger,Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';

    if ($eMemberType == '') {

        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }

    if ($eMemberType == "Passenger") {

        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iUserId = "iUserId";
        $eUserType = "Rider";
    } else if ($eMemberType == "Driver") {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iUserId = "iDriverId";
        $eUserType = "Driver";
    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }

    try {
        require_once(TPATH_CLASS . 'savar/class.factor.php');
        $token = SavarFactor::Create($iMemberId, $fAmount, $eMemberType);

        if ($token !== false) {
            $returnArr["Action"] = "1";
            $returnArr['message'] = $tconfig["tsite_url"] . 'payment/?token=' . $token;
            echo json_encode($returnArr);
            exit;
        } else {
            $returnArr["Action"] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
            exit;
        }
    } catch (Exception $e) {
        //echo "<pre>";print_r($e);exit;
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }
}

###########################################################################
if ($type == "DateTest") {
    $returnArr["Action"] = "0";
    $returnArr['message'] = jdate('jS F  g:i a');
    echo print_r($returnArr, true);
    exit;
}
###########################################################################
###########################################################################

###########################################################################

if ($type == "getShabaData") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    //$vStripeToken     = isset($_REQUEST["vStripeToken"]) ? $_REQUEST["vStripeToken"] : '';

    if ($eMemberType == "Passenger") {
        $eUserType = "Rider";
    } else {
        $eUserType = "Driver";
    }


    $vEmail = get_value($tbl_name, $vEmail, $iMemberId, $iUserId, '', 'true');
    // able name savar_shaba
    try {

        $sql = "SELECT * FROM `savar_shaba` WHERE userId = '$iUserId' AND userType = '$eUserType'";
        $row = $obj->MySQLSelect($sql);

        if (is_array($row) && count($row) > 0) {
            $profileData['shabaNumber'] = $row[0]['shabaNumber'];
            $profileData['shabaName'] = $row[0]['shabaName'];
            $profileData['shabaBank'] = $row[0]['shabaBank'];
            $profileData['status'] = $row[0]['status'];

            $returnArr['Action'] = "1";
            $returnArr['message'] = $profileData;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
}

###########################################################################
###########################################################################

if ($type == "saveShabaData") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver

    $ShabaNumber = isset($_REQUEST["ShabaNumber"]) ? $_REQUEST["ShabaNumber"] : '';
    $ShabaName = isset($_REQUEST["ShabaName"]) ? $_REQUEST["ShabaName"] : '';
    $ShabaBank = isset($_REQUEST["ShabaBank"]) ? $_REQUEST["ShabaBank"] : '';

    if ($eMemberType == "Passenger") {
        $eUserType = "Rider";
    } else {
        $eUserType = "Driver";
    }

    if ($ShabaNumber == "" || $ShabaName == "" || $ShabaBank == '') {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    } else {
        try {

            $sql = "SELECT userId FROM `savar_shaba` WHERE `userId` = ${iUserId}";
            $res = $obj->MySQLSelect($sql);

            if (count($res) > 0) {
                $sql = "UPDATE `savar_shaba` SET `shabaNumber` = '$ShabaNumber' , `shabaName`='$ShabaName', `shabaBank`='$ShabaBank', `userType`='$eUserType', `status`='UPDATE' WHERE `userId` = '$iUserId'";
            } else {
                $sql = "INSERT INTO `savar_shaba`(`shabaNumber`, `shabaName`, `shabaBank`, `userId`, `userType`, `status`)
				    VALUES ('$ShabaNumber','$ShabaName','$ShabaBank ','$iUserId','$eUserType','NEW')";
            }


            $inserLog = $obj->sql_query($sql);


            //$returnArr['log'] = $inserLog;

            if ($inserLog != false) {

                $returnArr['Action'] = "1";
                $returnArr['message'] = "LBL_SHABA_SAVE_SUCCESS_TXT";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_SHABA_SAVE_ERROR_TXT";
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }
    echo json_encode($returnArr);
}

###########################################################################
function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
{
    $i = $j = $c = 0;
    for ($i = 0, $j = $points_polygon; $i < $points_polygon; $j = $i++) {
        if ((($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
            ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i])))
            $c = !$c;
    }
    return $c;
}

if ($type == "getVhicleTypy") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $cLatitude = isset($_REQUEST["cLatitude"]) ? $_REQUEST["cLatitude"] : '0';
    $cLongitude = isset($_REQUEST["cLongitude"]) ? $_REQUEST["cLongitude"] : '0';

    //Logger($_REQUEST);

    if ($cLatitude == '0' || $cLongitude == '0') {
        $returnArr['Action'] = '0';
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    } else {
        try {

            $sql = "SELECT * FROM `register_user` WHERE iUserId='$iUserId'";
            $row = $obj->MySQLSelect($sql);
            $areaType = -1;

            if (count($row) > 0) {
                // $sql = "SELECT * FROM savar_area WHERE MBRWITHIN( POINT($cLongitude,$cLatitude) , sPolygonArea )  ORDER BY `savar_area`.`sPriority` DESC";
                $sql = "SELECT * FROM savar_area WHERE 1";
                $res = $obj->MySQLSelect($sql);

                if (count($res) > 0) {
                    for ($k = 0; $k < count($res); $k++) {

                        $vertices_x = array();
                        $vertices_y = array();

                        $colls = json_decode($res[$k]['sFeatureCollection']);
                        $coords = $colls->features[0]->geometry->coordinates[0];
                        //echo print_r($coords);exit;

                        for ($i = 0; $i < sizeof($coords); $i++) {

                            array_push($vertices_x, $coords[$i][1]);
                            array_push($vertices_y, $coords[$i][0]);
                        }
                        // echo print_r($vertices_x);exit;
                        // echo print_r($vertices_y);exit;

                        $inside_area = is_in_polygon(sizeof($coords) - 1, $vertices_x, $vertices_y, $cLatitude, $cLongitude);
                        if ($inside_area) {

                            $areaType = $res[$k]['aId'];
                            break;
                        }
                    }

                    //$areaType = $res[0]['aId'];
                    $sql = "SELECT * FROM `vehicle_type` WHERE `vSavarArea` = $areaType ";


                    $app_type = $generalobj->getConfigurations("configurations", "APP_TYPE");;
                    if ($app_type == "Ride") {
                        $sql .= " AND eType = 'Ride' ";
                    } else if ($app_type == "Delivery") {
                        $sql .= " AND eType = 'Deliver' ";
                    }

                    $vehicleTypes = $obj->MySQLSelect($sql);

                    $priceRatio = get_value('currency', 'Ratio', 'vName', $row[0]['vCurrencyPassenger'], '', 'true');

                    if (count($vehicleTypes) > 0) {
                        #Logger($vehicleTypes);


                        for ($i = 0; $i < count($vehicleTypes); $i++) {
                            $vehicleTypes[$i]['fPricePerKM'] = round($vehicleTypes[$i]['fPricePerKM'] * $priceRatio, 0);
                            $vehicleTypes[$i]['fPricePerMin'] = round($vehicleTypes[$i]['fPricePerMin'] * $priceRatio, 0);
                            $vehicleTypes[$i]['iBaseFare'] = round($vehicleTypes[$i]['iBaseFare'] * $priceRatio, 0);
                            $vehicleTypes[$i]['fCommision'] = round($vehicleTypes[$i]['fCommision'] * $priceRatio, 0);
                            $vehicleTypes[$i]['iMinFare'] = round($vehicleTypes[$i]['iMinFare'] * $priceRatio, 0);
                            $vehicleTypes[$i]['FareValue'] = round($vehicleTypes[$i]['fFixedFare'] * $priceRatio, 0);
                            $vehicleTypes[$i]['vVehicleType'] = $vehicleTypes[$i]["vVehicleType_" . $row[0]['vLang']];
                        }

                        #Logger($vehicleTypes);

                        $returnArr['Action'] = "1";
                        $returnArr['message']['VehicleTypes'] = $vehicleTypes;
                    } else {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = "LBL_NO_VEHICLE_IN_THIS_AREA_ERROR";
                    }

                } else {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_YOU_ARE_NOT_IN_AREA_ERROR";
                }
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_YOU_ARE_NOT_IN_AREA_ERROR";
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }


    }

    //Logger($returnArr);
    echo json_encode($returnArr);
}

if ($type == 'sendsmstodriver') {

    $driverId = $_REQUEST['DriverID'];
    $riderId = $_REQUEST['PassengerID'];
    $message = $_REQUEST['message'];
    $enum_type = $_REQUEST['enum_type'];
    $iMsgCode = $_REQUEST['iMsgCode'];
    $date_ = date("Y-m-d H:i:s");

    $sql = "SELECT * FROM `register_driver` WHERE `iDriverId` = '{$driverId}'";
    $db_login = $obj->MySQLSelect($sql);

    $driverMobile = "";
    if (count($db_login) > 0) {

        $driverMobile = $db_login[0]['vPhone'];
    } else {

        echo "wrong driver!";
        exit;
    }

    if ($message == '') {
        $query = " INSERT INTO `sentsms`(`smsId`, `iMsgCode`, `riderId`, `driverId`, `messege`, `type`, `date`) VALUES (' ','$iMsgCode','$riderId','$driverId','$message','$enum_type','$date_')";
        $obj->sql_query($query);
        echo "sent";
        exit;

    } else {

        echo "sent";
        // $url = "37.130.202.188/services.jspd";
        // $param = array
        // 			(
        // 				'uname'=>'tavasoli321',
        // 				'pass'=>'tavasoli26250',
        // 				'from'=>'100020400',
        // 				'message'=>$message,
        // 				'to'=>json_encode($driverMobile),
        // 				'op'=>'send'
        // 			);
        //
        // $handler = curl_init($url);
        // curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        // curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        // $response2 = curl_exec($handler);
        //
        // $response2 = json_decode($response2);
        // $res_code = $response2[0];
        // $res_data = $response2[1];
        // //echo $res_data;
        // if($res_data > 0)
        // {
        // 	$query = " INSERT INTO `sentsms`(`smsId`, `iMsgCode`, `riderId`, `driverId`, `messege`, `type`, `date`)
        // 	VALUES ('$res_data','$iMsgCode','$riderId','$driverId','$message','$enum_type','$date_')";
        // 	$obj->sql_query($query);
        // 	echo "sent";
        // }
        // else {
        // 	//nop - lost data
        // }
    }
}


//Mamad H . A . M (Start)


if ($type == 'pminapp') {
    $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
    $user = isset($_REQUEST['user']) ? trim($_REQUEST['user']) : '';
    $check_payment = get_value('savar_pminapp', '*', 'receiver_type', 'all', " or (`receiver_type` = '$user' and `receiver` = $id) or `receiver_type` = 'all_$user' limit 10", '');
    // print_r($check_payment);
    //all_driver
    //all_passenger
    //driver
    //passenger
    $row[0]['savar_pminapp'] = $check_payment;
    echo json_encode($row[0]);


    exit;
}

if ($type == 'testing') {

    $startLatitude = 32.662139;
    $startLongitude = 51.658604;
    $endLatitude = 32.663945;
    $endLongitude = 51.655889;

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
        throw new \Exception("didn't get error");
    }
    echo print_r($responseData['routes'][0]['duration'] / 60 . " " . $responseData['routes'][0]['distance'] / 1000);
}


/*


  if($type == 'pminapp_passenger') {
    $id 		= isset($_REQUEST['id'])?trim($_REQUEST['id']):'';
    $check_payment = get_value('savar_pminapp', '*', 'receiver_type', 'passenger', " AND receiver = '$id' limit 10",'');
    // print_r($check_payment);
  //all_driver
  //all_passenger
  //driver
  //passenger
    $row[0]['savar_pminapp'] = $check_payment;
    echo json_encode($row[0]);
  //$type 		= isset($_REQUEST['type'])?trim($_REQUEST['type']):'';
    /*$resultdb = mysqli_query($condbc,"SELECT * FROM savar_pminapp limit 10");

                                                while($rowdb = mysqli_fetch_array($resultdb))
                                                  {
                                                  $result['title'] = $rowdb['title'];
                                                  $result['pmtext'] = $rowdb['pmtext'];
                                                  $result['image'] = $rowdb['image'];
                                                  //$result['sender'] = $rowdb['sender'];
                                                  $result['receiver_type'] = $rowdb['receiver_type'];
                                                  $result['receiver'] = $rowdb['receiver'];
                                                  $result['date'] = $rowdb['date'];

                                                  }
  echo json_encode($result);


    exit;
  }






  if($type == 'pminapp_driver') {
    $id 		= isset($_REQUEST['id'])?trim($_REQUEST['id']):'';
    $check_payment = get_value('savar_pminapp', '*', 'receiver_type', 'driver', " AND receiver = '$id' limit 10",'');
    // print_r($check_payment);
  //all_driver
  //all_passenger
  //driver
  //passenger
    $row[0]['savar_pminapp'] = $check_payment;
    echo json_encode($row[0]);
  //$type 		= isset($_REQUEST['type'])?trim($_REQUEST['type']):'';
    /*$resultdb = mysqli_query($condbc,"SELECT * FROM savar_pminapp limit 10");

                                                while($rowdb = mysqli_fetch_array($resultdb))
                                                  {
                                                  $result['title'] = $rowdb['title'];
                                                  $result['pmtext'] = $rowdb['pmtext'];
                                                  $result['image'] = $rowdb['image'];
                                                  //$result['sender'] = $rowdb['sender'];
                                                  $result['receiver_type'] = $rowdb['receiver_type'];
                                                  $result['receiver'] = $rowdb['receiver'];
                                                  $result['date'] = $rowdb['date'];

                                                  }
  echo json_encode($result);


    exit;
  }










  if($type == 'pminapp_all_passenger') {
    $check_payment = get_value('savar_pminapp', '*', 'receiver_type', 'all_passenger',' limit 10');
    // print_r($check_payment);
  //all_driver
  //all_passenger
  //driver
  //passenger
    $row[0]['savar_pminapp'] = $check_payment;
    echo json_encode($row[0]);
  //$type 		= isset($_REQUEST['type'])?trim($_REQUEST['type']):'';
    /*$resultdb = mysqli_query($condbc,"SELECT * FROM savar_pminapp limit 10");

                                                while($rowdb = mysqli_fetch_array($resultdb))
                                                  {
                                                  $result['title'] = $rowdb['title'];
                                                  $result['pmtext'] = $rowdb['pmtext'];
                                                  $result['image'] = $rowdb['image'];
                                                  //$result['sender'] = $rowdb['sender'];
                                                  $result['receiver_type'] = $rowdb['receiver_type'];
                                                  $result['receiver'] = $rowdb['receiver'];
                                                  $result['date'] = $rowdb['date'];

                                                  }
  echo json_encode($result);


    exit;
  }





  if($type == 'pminapp_all_driver') {
    $check_payment = get_value('savar_pminapp', '*', 'receiver_type', 'all_driver',' limit 10');
    // print_r($check_payment);
  //all_driver
  //all_passenger
  //driver
  //passenger
    $row[0]['savar_pminapp'] = $check_payment;
    echo json_encode($row[0]);
  //$type 		= isset($_REQUEST['type'])?trim($_REQUEST['type']):'';
    /*$resultdb = mysqli_query($condbc,"SELECT * FROM savar_pminapp limit 10");

                                                while($rowdb = mysqli_fetch_array($resultdb))
                                                  {
                                                  $result['title'] = $rowdb['title'];
                                                  $result['pmtext'] = $rowdb['pmtext'];
                                                  $result['image'] = $rowdb['image'];
                                                  //$result['sender'] = $rowdb['sender'];
                                                  $result['receiver_type'] = $rowdb['receiver_type'];
                                                  $result['receiver'] = $rowdb['receiver'];
                                                  $result['date'] = $rowdb['date'];

                                                  }
  echo json_encode($result);


    exit;
  }



  if($type == 'pminapp_all') {
    $check_payment = get_value('savar_pminapp', '*', 'receiver_type', 'all',' limit 10');
    // print_r($check_payment);
//all_driver
//all_passenger
//driver
//passenger
    $row[0]['savar_pminapp'] = $check_payment;
    echo json_encode($row[0]);
  //$type 		= isset($_REQUEST['type'])?trim($_REQUEST['type']):'';
    /*$resultdb = mysqli_query($condbc,"SELECT * FROM savar_pminapp limit 10");

                                                while($rowdb = mysqli_fetch_array($resultdb))
                                                  {
                                                  $result['title'] = $rowdb['title'];
                                                  $result['pmtext'] = $rowdb['pmtext'];
                                                  $result['image'] = $rowdb['image'];
                                                  //$result['sender'] = $rowdb['sender'];
                                                  $result['receiver_type'] = $rowdb['receiver_type'];
                                                  $result['receiver'] = $rowdb['receiver'];
                                                  $result['date'] = $rowdb['date'];

                                                  }
echo json_encode($result);


    exit;
  }*/
//Mamad H . A . M (End)


###########################################################################
###########################################################################

function cab_bookink_exec($iCabBookingId)
{
    global $obj, $booking_test;

    if (intval($iCabBookingId) <= 0)
        return -1;

    $sql = "SELECT * FROM `cab_booking` WHERE `iCabBookingId` = '${iCabBookingId}'";
    $res = $obj->MySQLSelect($sql);
    if (count($res) == 0)
        return -2;

    $cab = $res[0];
    $iUserId = $cab['iUserId'];
    $cab_iDriverId = $cab['iDriverId'];
    $driver_req_archive = $cab['tDriverReqArchive'];

    if ($driver_req_archive == '') {
        $driver_req_archive = [];
    } else {
        $driver_req_archive = @unserialize($driver_req_archive);
    }
    $sql = "SELECT * FROM `register_user` WHERE iUserId = {$iUserId}";
    $res = $obj->MySQLSelect($sql);
    if (count($res) == 0)
        return -3;

    $user = $res[0];


    $tReturn = 'false';
    $delayId = '0';
    $tSecDestination = 'false';

    $passengerLat = $cab['vSourceLatitude'];
    $passengerLon = $cab['vSourceLongitude'];
    $destLat = $cab['vDestLatitude'];
    $destLon = $cab['vDestLongitude'];
    $iVehicleTypeId = $cab['iVehicleTypeId'];
    $iUserId = $cab['iUserId'];
    $cashPayment = ($cab['ePayType'] == 'Cash' ? 'true' : 'false');
    $vCouponCode = $cab['vCouponCode'];
    $tTripComment = $cab['tTripComment'];
    $fGDdistance = $cab['fGDdistance'];
    $fGDtime = $cab['fGDtime'];

    $passengerName = $user['vName'] . ' ' . $user['vLastName'];
    $pPhone = $user['vPhone'];
    $pPhoneCode = $user['vPhoneCode'];
    $vAvgRating = $user['vAvgRating'];
    $iGcmRegId = $user['iGcmRegId'];
    $vFbId = $user['vFbId'];

    $driver_array = getOnlineDriverArr($passengerLat, $passengerLon);
    $driver_list_array = '';

    foreach ($driver_array as $driver) {
        $vehicleTypes = get_value("driver_vehicle", 'vCarType', "iDriverVehicleId", $driver['iDriverVehicleId'], '', 'true');
        if ($vehicleTypes != '') {
            $vehicleTypes = explode(",", $vehicleTypes);

            if (in_array($iVehicleTypeId, $vehicleTypes)) {
                $distance = 'd_' . $driver['distance'] . "_" . time();
                $driver_list_array[$distance] = $driver;
            }
        }
    }

    if ($cab_iDriverId > 0) {

        $driver_list_array = '';
        $sqli = "SELECT * FROM `register_driver` WHERE `iDriverId` = '${cab_iDriverId}'";
        $resi = $obj->MySQLSelect($sqli);
        $driver_array = $resi;
        $distancei = 'd_' . "0" . "_" . time();
        $driver_list_array[$distancei] = $driver_array[0];
        $cab_iDriverId == '0';
    }

    ksort($driver_list_array);
    $driver_list_array = array_values($driver_list_array);
    $driver_list_len = count($driver_list_array);

    if (isset($driver_req_archive["last"]))
        $driver_last_ids = $driver_req_archive['last'];
    else
        $driver_last_ids = array(0);

    if ($driver_list_len > 0) {
        $driver_item_index = -1;
        if ($cab_iDriverId == '0')
            $driver_item_index = 0;
        else {
            for ($i = 0; $i < $driver_list_len; $i++) {
                $driver = $driver_list_array[$i];
                if (in_array($driver['iDriverId'], $driver_last_ids))
                    continue;

                $driver_item_index = $i;
                break;
            }

            if ($driver_item_index == -1) {
                $driver_item_index = 0;
                $driver_req_archive["last"] = array();
            } else
                $driver_item_index = $driver_item_index % $driver_list_len;
        }

        $driver_ok_id = $driver_list_array[$driver_item_index]['iDriverId'];

        $driver_req_archive["last"][] = $driver_ok_id;
        $driver_req_archive["archive"][] = $driver_ok_id;

        if ($driver_ok_id == '')
            return -4;

        $message = [
            'Message' => 'CabRequested',
            'sourceLatitude' => $passengerLat,
            'sourceLongitude' => $passengerLon,
            'PassengerId' => $iUserId,
            'PName' => $passengerName,
            'PPicName' => 'NONE',
            'PFId' => $vFbId,
            'PRating' => $vAvgRating,
            'PPhone' => $pPhone,
            'PPhoneC' => $pPhoneCode,
            'REQUEST_TYPE' => 'Ride',
            'destLatitude' => $destLat,
            'destLongitude' => $destLon,
            'iBookingId' => $iCabBookingId,
            'TripComment' => $tTripComment,
            'fixedDistance' => $fGDdistance,
            'fixedTime' => $fGDtime,
            'tSecDestination' => $tSecDestination,
            'tReturn' => $tReturn,
            'delayId' => $delayId,
        ];

        $params = array
        (
            'type' => 'sendRequestToDrivers',
            'userId' => $iUserId,
            'SelectedCarTypeID' => $iVehicleTypeId,
            'DestAddress' => '',
            'CashPayment' => $cashPayment,
            'PromoCode' => $vCouponCode,
            'PickUpLatitude' => $passengerLat,
            'PickUpLongitude' => $passengerLon,
            'eType' => 'Ride',
            'driverIds' => $driver_ok_id,
            'DestLatitude' => $destLat,
            'DestLongitude' => $destLon,
            'vDeviceToken' => $iGcmRegId,
            'message' => json_encode($message, JSON_UNESCAPED_UNICODE),
            'iBookingId' => $iCabBookingId,
            'TripComment' => $tTripComment,
            'fixedDistance' => $fGDdistance,
            'fixedTime' => $fGDtime,
            'tSecDestination' => $tSecDestination,
            'tReturn' => $tReturn,
            'delayId' => $delayId,
        );

        foreach ($params as $key => $val) {
            $_POST[$key] = $_REQUEST[$key] = $val;
        }
    } else {
        $driver_req_archive["archive"][] = 'driver-not-found';
        $driver_ok_id = '0';
    }

    // add last try datetime to dadabase
    $datetime = date('Y-m-d H:i:s');
    $iDriverId = $driver_ok_id;
    $tDriverReqArchive = (serialize($driver_req_archive));

    $sql = "UPDATE cab_booking SET dLastTry = '{$datetime}' , iDriverId = {$iDriverId} , tDriverReqArchive = '{$tDriverReqArchive}'  WHERE iCabBookingId = {$iCabBookingId}";
    $obj->sql_query($sql);
}


###########################################################################

function Logger($data)
{
    $text = '';

    if (is_array($data) || is_object($data))
        $text = print_r($data, true);
    else
        $text = $data;

    file_put_contents("webservicelogiphone.txt", $text . "\r\n................................\r\n", FILE_APPEND);
}

function TLOG($data)
{
    $tgb = new TelegramBot();
    $tgb->sendMessage(print_r($data, true));
}

function SupportLogger($data)
{
    $text = '';

    if (is_array($data) || is_object($data))
        $text = print_r($data, true);
    else
        $text = $data;

    file_put_contents("webservicelog_support.txt", $text . "\r\n................................\r\n", FILE_APPEND);
}

//die(microtime(true) - $start);
?>
