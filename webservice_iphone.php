<?php
//error_reporting(0);

ob_start("ob_gzhandler");

include_once('common.php');
//require_once('assets/libraries/stripe/config.php');
//require_once('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
require_once('assets/libraries/pubnub/autoloader.php');
include_once(TPATH_CLASS . 'Imagecrop.class.php');
include_once(TPATH_CLASS . 'twilio/Services/Twilio.php');
include_once('generalFunctions.php');
include_once('send_invoice_receipt.php');
require_once(TPATH_CLASS . 'savar/jalali_date.php');
require_once(TPATH_CLASS . 'savar/class.telegrambot.php');

Logger($_REQUEST);
//echo "<pre>";print_r($_REQUEST);exit;

/* creating objects */
$thumb = new thumbnail;

$type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : '';

/* If no type found */
if ($type == '') {
    $result['result'] = 0;
    $result['message'] = 'Required parameter missing.';

    echo json_encode($result);
    exit;
}

/* Paypal supported Currency Codes */
$currency_supported_paypal = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'TRY', 'USD');

$demo_site_msg = "Edit / Delete Record Feature has been disabled on the Demo Application. This feature will be enabled on the main script we will provide you.";

/* To Check App Version */
$appVersion = isset($_REQUEST['AppVersion']) ? trim($_REQUEST['AppVersion']) : '';

if ($appVersion != "") {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if ($UserType == "Passenger") {
        $newAppVersion = $generalobj->getConfigurations("configurations", "PASSENGER_IOS_APP_VERSION");
    } else {
        $newAppVersion = $generalobj->getConfigurations("configurations", "DRIVER_IOS_APP_VERSION");
    }

    if ($newAppVersion != $appVersion && $newAppVersion > $appVersion) {
        $returnArr['Action'] = "0";
        $returnArr['isAppUpdate'] = "true";
        $returnArr['Error'] = "LBL_NEW_UPDATE_MSG";
        echo json_encode($returnArr);
        exit;
    }

}

/* Return all Active language from language_master */
if ($type == 'Language') {

    $sql = "SELECT vCode, vTitle, iLanguageMasId, eDefault FROM language_master WHERE eStatus = 'Active' ORDER BY iDispOrder";
    $db_login = $obj->MySQLSelect($sql);

    $returnArr = array();

    if (count($db_login) > 0) {
        //$returnArr[0]['action'] = 1;
        $returnArr[0]['list_languages'] = $db_login;
    } else {
        $returnArr[0]['action'] = 0;
    }
    //$List_languages['List']=$returnArr[0];
    echo json_encode($returnArr[0]);
    exit;
}

function GetUserDetail($id)
{
    global $obj, $generalobj, $demo_site_msg, $appVersion;

    $where = " iUserId = '" . $id . "'";
    $data_version['iAppVersion'] = "2";
    $obj->MySQLQueryPerform("register_user", $data_version, 'update', $where);


    $sql = "SELECT  * FROM  `register_user` WHERE iUserId = '" . $id . "' ";
    $Data = $obj->MySQLSelect($sql);
    //        $Data[0]=array();

    if (count($Data) > 0) {

        if ($Data[0]['vImgName'] != "" && $Data[0]['vImgName'] != "NONE") {
            $Data[0]['vImgName'] = "3_" . $Data[0]['vImgName'];
        }

        if ($Data[0]['eStatus'] == "Active") {

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

        $TripStatus = $Data[0]['vTripStatus'];
        $TripID = $Data[0]['iTripId'];

        if ($TripStatus != "NONE") {

            $TripID = $Data[0]['iTripId'];
            $row_result_trips = getTripPriceDetails($TripID, $id, "Passenger");

            $Data[0]['TripDetails'] = $row_result_trips;
            $Data[0]['DriverDetails'] = $row_result_trips['DriverDetails'];

            $row_result_trips['DriverCarDetails']['make_title'] = $row_result_trips['DriverCarDetails']['vMake'];
            $row_result_trips['DriverCarDetails']['model_title'] = $row_result_trips['DriverCarDetails']['vTitle'];
            $Data[0]['DriverCarDetails'] = $row_result_trips['DriverCarDetails'];

            $sql = "SELECT vPaymentUserStatus FROM `payments` WHERE iTripId='$TripID'";

            $row_result_payments = $obj->MySQLSelect($sql);

            if (count($row_result_payments) > 0) {

                if ($row_result_payments[0]['vPaymentUserStatus'] != 'approved') {
                    $Data[0]['PaymentStatus_From_Passenger'] = "Not Approved";
                } else {
                    $Data[0]['PaymentStatus_From_Passenger'] = "Approved";
                }

            } else {

                $Data[0]['PaymentStatus_From_Passenger'] = "No Entry";
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
                        $Data[0]['Ratings_From_Passenger'] = "Done";
                    } else if ($ContentWritten == "false") {
                        $Data[0]['Ratings_From_Passenger'] = "Not Done";
                    }

                    $count_row_rating++;
                }
            } else {

                $Data[0]['Ratings_From_Passenger'] = "No Entry";
            }


        }
        /*if ($TripStatus == "Active" || $TripStatus == "On Going Trip") {

                $TripID = $Data[0]['iTripId'];


                $row_result_trips = getTripPriceDetails($TripID,$id,"Driver");

            $Data[0]['TripDetails'] = $row_result_trips;


            $Data[0]['DriverDetails'] = $row_result_trips['DriverDetails'];


            $row_result_trips['DriverCarDetails']['make_title'] = $row_result_trips['DriverCarDetails']['vMake'];
            $row_result_trips['DriverCarDetails']['model_title'] = $row_result_trips['DriverCarDetails']['vTitle'];
            $Data[0]['DriverCarDetails'] = $row_result_trips['DriverCarDetails'];


				}else if($TripStatus == "Not Active"){

                $sql = "SELECT register_driver.vImage AS driver_img_lastTrip, trips.* FROM `trips` JOIN register_driver ON trips.iDriverId = register_driver.iDriverId  WHERE trips.iTripId='$TripID'";

                $row_result_trip = $obj->MySQLSelect($sql);

                if($row_result_trip[0]['driver_img_lastTrip']!="" && $row_result_trip[0]['driver_img_lastTrip']!="NONE"){
                    $row_result_trip[0]['driver_img_lastTrip']="3_".$row_result_trip[0]['driver_img_lastTrip'];
				}

                $Data[0]['Last_trip_data']=$row_result_trip[0];

                $sql = "SELECT vPaymentUserStatus FROM `payments` WHERE iTripId='$TripID'";
                $row_result_payments = $obj->MySQLSelect($sql);

                if(count($row_result_payments)>0){

                    if($row_result_payments[0]['vPaymentUserStatus']!='approved'){
                        $Data[0]['PaymentStatus_From_Passenger']="Not Approved";
						}else{
                        $Data[0]['PaymentStatus_From_Passenger']="Approved";
					}

					}else{

                    $Data[0]['PaymentStatus_From_Passenger']="No Entry";
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
                            $Data[0]['Ratings_From_Passenger']="Done";
							}else if($ContentWritten=="false"){
                            $Data[0]['Ratings_From_Passenger']="Not Done";
						}

                        $count_row_rating++;
					}

					}else{

                    $Data[0]['Ratings_From_Passenger']="No Entry";
				}
			}    */

        // $vehicleTypes = get_value('vehicle_type', '*', 'eType', 'Ride');
        //$vehicleTypes = get_value('vehicle_type', '*', '', '');


        $vehicleTypes = get_value('vehicle_type', '*', 'vSavarArea', '11', ' ORDER BY iVehicleTypeId ASC');

        $priceRatio = get_value('currency', 'Ratio', 'vName', $Data[0]['vCurrencyPassenger'], '', 'true');
        for ($i = 0; $i < count($vehicleTypes); $i++) {
            $vehicleTypes[$i]['fPricePerKM'] = strval(round($vehicleTypes[$i]['fPricePerKM'] * $priceRatio, 2));
            $vehicleTypes[$i]['fPricePerMin'] = strval(round($vehicleTypes[$i]['fPricePerMin'] * $priceRatio, 2));
            $vehicleTypes[$i]['iBaseFare'] = strval(round($vehicleTypes[$i]['iBaseFare'] * $priceRatio, 2));
            $vehicleTypes[$i]['fCommision'] = strval(round($vehicleTypes[$i]['fCommision'] * $priceRatio, 2));
            $vehicleTypes[$i]['iMinFare'] = strval(round($vehicleTypes[$i]['iMinFare'] * $priceRatio, 2));
            $vehicleTypes[$i]['vVehicleType'] = $vehicleTypes[$i]["vVehicleType_" . $Data[0]['vLang']];
        }
        $Data[0]['VehicleTypes'] = $vehicleTypes;

        $Data[0]['vSelectedLanguageTitle'] = getLanguageTitle($Data[0]['vLang']);

        if ($Data[0]['vPassword'] != '') {
            $Data[0]['Passenger_Password_decrypt'] = $generalobj->decrypt($Data[0]['vPassword']);
        } else {
            $Data[0]['Passenger_Password_decrypt'] = "";
        }

        $Data[0]['PayPalConfiguration'] = $generalobj->getConfigurations("configurations", "PAYMENT_ENABLED");
        $Data[0]['IPHONE_PAYMENT_ENABLED'] = $generalobj->getConfigurations("configurations", "IPHONE_PAYMENT_ENABLED");
        $Data[0]['DefaultCurrencySign'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_SIGN");
        $Data[0]['DefaultCurrencyCode'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_CODE");
        $Data[0]['RESTRICTION_KM_NEAREST_TAXI'] = $generalobj->getConfigurations("configurations", "RESTRICTION_KM_NEAREST_TAXI");

        // add by seyyed amir for prevent of error in ios app version 1.05 savar
        // will be delleted after new version
        if ($appVersion <= '1.05')
            $Data[0]['RESTRICTION_KM_NEAREST_TAXI'] = intval($Data[0]['RESTRICTION_KM_NEAREST_TAXI']) . '';

        //TLOG(array("Val"=> $Data[0]['RESTRICTION_KM_NEAREST_TAXI'],'type' => gettype($Data[0]['RESTRICTION_KM_NEAREST_TAXI']),'appVersion' => $appVersion ));
        ///////////////////////////////////////////////

        $Data[0]['ABOUT_US_PAGE_DESCRIPTION'] = "";
        $Data[0]['MOBILE_VERIFY_APP_ID'] = "";
        $Data[0]['MOBILE_VERIFY_ACCESS_TOKEN'] = "";
        $Data[0]['FACEBOOK_APP_ID'] = $generalobj->getConfigurations("configurations", "FACEBOOK_APP_ID");
        $Data[0]['CONFIG_CLIENT_ID'] = $generalobj->getConfigurations("configurations", "CONFIG_CLIENT_ID");
        $Data[0]['GOOGLE_SENDER_ID'] = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID");
        $Data[0]['STRIPE_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "STRIPE_PUBLISH_KEY");
        $Data[0]['ENABLE_TIP_MODULE'] = $generalobj->getConfigurations("configurations", "ENABLE_TIP_MODULE");
        $Data[0]['DRIVER_ARRIVED_MIN_TIME_PER_MINUTE'] = $generalobj->getConfigurations("configurations", "DRIVER_ARRIVED_MIN_TIME_PER_MINUTE");
        $Data[0]['LOCATION_ACCURACY_METERS'] = $generalobj->getConfigurations("configurations", "LOCATION_ACCURACY_METERS");
        $Data[0]['DRIVER_REQUEST_METHOD'] = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
        $Data[0]['SITE_POLICE_CONTROL_NUMBER'] = $generalobj->getConfigurations("configurations", "SITE_POLICE_CONTROL_NUMBER");
        $Data[0]['REFERRAL_SCHEME_ENABLE'] = $generalobj->getConfigurations("configurations", "REFERRAL_SCHEME_ENABLE");
        $Data[0]['WALLET_ENABLE'] = $generalobj->getConfigurations("configurations", "WALLET_ENABLE");
        $Data[0]['ENABLE_PUBNUB'] = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
        $Data[0]['WALLET_FIXED_AMOUNT_1'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_1");
        $Data[0]['WALLET_FIXED_AMOUNT_2'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_2");
        $Data[0]['WALLET_FIXED_AMOUNT_3'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_3");
        // $Data[0]['CurrencyList']=($obj->MySQLSelect("SELECT * FROM currency"));
        $Data[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        // $Data[0]['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$Data[0]['vCurrencyPassenger']."' ")[0]['vSymbol']);
        $Data[0]['CurrencySymbol'] = get_value('currency', 'vSymbol', 'vName', $Data[0]['vCurrencyPassenger'], '', 'true');
        $Data[0]['LIST_DRIVER_LIMIT_BY_DISTANCE'] = strval($generalobj->getConfigurations("configurations", "LIST_DRIVER_LIMIT_BY_DISTANCE"));
        $Data[0]['SITE_TYPE'] = strval(SITE_TYPE);
        $Data[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        $Data[0]['RIIDE_LATER'] = RIIDE_LATER;
        $Data[0]['PROMO_CODE'] = PROMO_CODE;
        $Data[0]['APP_TYPE'] = $generalobj->getConfigurations("configurations", "APP_TYPE");
        $Data[0]['APP_PAYMENT_MODE'] = $generalobj->getConfigurations("configurations", "APP_PAYMENT_MODE");

        $ConfigData['PUBNUB_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
        $ConfigData['PUBNUB_SUBSCRIBE_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");
        $ConfigData['PUBNUB_SECRET_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SECRET_KEY");

        $ConfigData['LINK_FORGET_PASS_PAGE_PASSENGER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_PASSENGER");
        $ConfigData['MOBILE_VERIFY_APP_ID_IPHONE'] = "";
        $ConfigData['MOBILE_VERIFY_ACCESS_TOKEN_IPHONE'] = "";
        $ConfigData['FACEBOOK_APP_ID'] = $generalobj->getConfigurations("configurations", "FACEBOOK_APP_ID");
        $ConfigData['DRIVER_LOC_FETCH_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "DRIVER_LOC_FETCH_TIME_INTERVAL");
        $ConfigData['ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL");
        $ConfigData['REFERRAL_SCHEME_ENABLE'] = $generalobj->getConfigurations("configurations", "REFERRAL_SCHEME_ENABLE");
        $ConfigData['WALLET_ENABLE'] = $generalobj->getConfigurations("configurations", "WALLET_ENABLE");

        $ConfigData['RIIDE_LATER'] = RIIDE_LATER;
        $ConfigData['SITE_TYPE'] = SITE_TYPE;

        $ConfigData['DESTINATION_UPDATE_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "DESTINATION_UPDATE_TIME_INTERVAL");
        $ConfigData['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");
        $Data[0]['GeneralConfigData'] = $ConfigData;
    }
    return $Data[0];
}

function GetDriverDetail($id)
{
    global $obj, $generalobj, $demo_site_msg;

    $where = " iDriverId = '" . $id . "'";
    $data_version['iAppVersion'] = "2";
    $obj->MySQLQueryPerform("register_driver", $data_version, 'update', $where);

    $sql = "SELECT rd.*,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='$id' AND cmp.iCompanyId=rd.iCompanyId";
    $Data = $obj->MySQLSelect($sql);
    //        $Data[0]=array();
    if ($Data[0]['vImage'] != "" && $Data[0]['vImage'] != "NONE") {
        $Data[0]['vImage'] = "3_" . $Data[0]['vImage'];
    }
    $Data[0]['vSelectedLanguageTitle'] = getLanguageTitle($Data[0]['vLang']);
    $Data[0]['Driver_Password_decrypt'] = $generalobj->decrypt($Data[0]['vPassword']);
    $Data[0]['RegDateForRideHis'] = strval(date('Y m d', strtotime("-1 month", strtotime($Data[0]['tRegistrationDate']))));

    if ($Data[0]['iDriverVehicleId'] != '' && $Data[0]['iDriverVehicleId'] != '0') {
        $data_vehicle_arr = get_value('driver_vehicle', 'iMakeId, iModelId', 'iDriverVehicleId', $Data[0]['iDriverVehicleId']);
        $Data[0]['vMake'] = get_value('make', 'vMake', 'iMakeId', $data_vehicle_arr[0]['iMakeId'], '', 'true');
        $Data[0]['vModel'] = get_value('model', 'vTitle', 'iModelId', $data_vehicle_arr[0]['iModelId'], '', 'true');
    }
    if ($Data[0]['eStatus'] == "active" && $Data[0]['cmpEStatus'] == "Active") {

    } else {
        //$returnArr['Error'] = "Failed. Status is not Active";
        //echo json_encode($returnArr);
        // echo "LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER";
        if ($Data[0]['cmpEStatus'] != "Active") {
            //                echo "LBL_CONTACT_US_STATUS_NOTACTIVE_COMPANY";
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_COMPANY";
            echo json_encode($returnArr);
            exit;
        } else if ($Data[0]['eStatus'] == "Deleted") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            echo json_encode($returnArr);
        } else {

            // echo "LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER";
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER";
            echo json_encode($returnArr);

            exit;
        }
        exit;
    }

    $TripStatus = $Data[0]['vTripStatus'];

    if ($TripStatus != "NONE") {
        $TripID = $Data[0]['iTripId'];

        $row_result_trips = getTripPriceDetails($TripID, $id, "Driver");
        //echo "<pre>";print_r($row_result_trips);

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

    $sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverId='$id' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'";

    $Data_car = $obj->MySQLSelect($sql);
    if (count($Data_car) > 0) {
        $Data[0]['IS_CAR_AVAILABLE'] = "YES";
        $sql = "SELECT make.vMake, model.vTitle, dv.iDriverVehicleId, dv.iMakeId, dv.iModelId, dv.vLicencePlate  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverId='$id' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` ";
        $Data_car_title = $obj->MySQLSelect($sql);
        $Data[0]['vSelectedCarName'] = $Data_car_title[0]['vMake'] . ' ' . $Data_car_title[0]['vTitle'];
        $Data[0]['vSelectedCarLicencePlate'] = $Data_car_title[0]['vLicencePlate'];
    } else {
        $Data[0]['IS_CAR_AVAILABLE'] = "NO";
    }

    $Data[0]['ABOUT_US_PAGE_DESCRIPTION'] = "";
    $Data[0]['PayPalConfiguration'] = $generalobj->getConfigurations("configurations", "PAYMENT_ENABLED");
    $Data[0]['DefaultCurrencySign'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_SIGN");
    $Data[0]['DefaultCurrencyCode'] = $generalobj->getConfigurations("configurations", "DEFAULT_CURRENCY_CODE");
    $Data[0]['REFERRAL_SCHEME_ENABLE'] = $generalobj->getConfigurations("configurations", "REFERRAL_SCHEME_ENABLE");
    $Data[0]['WALLET_ENABLE'] = $generalobj->getConfigurations("configurations", "WALLET_ENABLE");
    $Data[0]['ENABLE_PUBNUB'] = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");

    $Data[0]['ENABLE_TIP_MODULE'] = $generalobj->getConfigurations("configurations", "ENABLE_TIP_MODULE");

    $Data[0]['MOBILE_VERIFY_APP_ID'] = "";
    $Data[0]['MOBILE_VERIFY_ACCESS_TOKEN'] = "";
    $Data[0]['DRIVER_REFER_APP_SHARE_TXT'] = $generalobj->getConfigurations("configurations", "DRIVER_REFER_APP_SHARE_TXT");
    $Data[0]['STRIPE_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "STRIPE_PUBLISH_KEY");
    $Data[0]['WALLET_FIXED_AMOUNT_1'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_1");
    $Data[0]['WALLET_FIXED_AMOUNT_2'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_2");
    $Data[0]['WALLET_FIXED_AMOUNT_3'] = $generalobj->getConfigurations("configurations", "WALLET_FIXED_AMOUNT_3");
    // $Data[0]['CurrencyList']=($obj->MySQLSelect("SELECT * FROM currency"));
    $Data[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');

    //Mehrshad Added
//    $filei = "../app/admin/Modules/driver_max_owe.txt";
//    $max_owe = file_get_contents($filei);
    $max_owe = $generalobj->getConfigurations("configurations", "driver_max_owe");
    if ($max_owe == '') {

        $max_owe = '10000';
    }
    $Data[0]['max_owe'] = str_replace("\n", "", $max_owe);

    $Data[0]['user_available_balance'] = "" . $generalobj->get_user_available_balance($id, 'Driver') . "";
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
    $Data[0]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($id, '');
    //Mehrshad Added


    $Data[0]['SITE_TYPE'] = strval(SITE_TYPE);
    $Data[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
    $Data[0]['RIIDE_LATER'] = RIIDE_LATER;
    $Data[0]['APP_TYPE'] = $generalobj->getConfigurations("configurations", "APP_TYPE");
    $Data[0]['APP_PAYMENT_MODE'] = $generalobj->getConfigurations("configurations", "APP_PAYMENT_MODE");

    $str_date = @date('Y-m-d H:i:s', strtotime('-1 minutes'));

    $sql_request = "SELECT * FROM passenger_requests WHERE iDriverId='" . $id . "' AND dAddedDate > '" . $str_date . "' ";
    $data_requst = $obj->MySQLSelect($sql_request);

    $Data[0]['CurrentRequests'] = $data_requst;

    $ConfigData['LINK_FORGET_PASS_PAGE_DRIVER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_DRIVER");
    $ConfigData['MOBILE_VERIFY_APP_ID_IPHONE'] = "";
    $ConfigData['MOBILE_VERIFY_ACCESS_TOKEN_IPHONE'] = "";
    $ConfigData['FACEBOOK_APP_ID'] = $generalobj->getConfigurations("configurations", "FACEBOOK_APP_ID");
    $ConfigData['DRIVER_LOC_UPDATE_TIME_INTERVAL'] = $generalobj->getConfigurations("configurations", "DRIVER_LOC_UPDATE_TIME_INTERVAL");
    $ConfigData['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");
    $ConfigData['LOCATION_ACCURACY_METERS'] = $generalobj->getConfigurations("configurations", "LOCATION_ACCURACY_METERS");
    $ConfigData['PUBNUB_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $ConfigData['PUBNUB_SUBSCRIBE_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");
    $ConfigData['PUBNUB_SECRET_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SECRET_KEY");
    $ConfigData['SITE_TYPE'] = SITE_TYPE;

    $Data[0]['GeneralConfigData'] = $ConfigData;

    return $Data[0];
}

##########################################################################
if ($type == 'generalConfigData') {
    $langArr = getLanguageLabelsArr();
    $GeneralConfigData['LanguageLabels'] = $langArr['LanguageLabels'];
    $GeneralConfigData['Action'] = $langArr['Action'];
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';


    if ($UserType == "Passenger") {
        $ConfigData['LINK_FORGET_PASS_PAGE_PASSENGER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_PASSENGER");
    } else {
        $ConfigData['LINK_FORGET_PASS_PAGE_DRIVER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_FORGET_PASS_PAGE_DRIVER");
        $ConfigData['LINK_SIGN_UP_PAGE_DRIVER'] = $tconfig["tsite_url"] . $generalobj->getConfigurations("configurations", "LINK_SIGN_UP_PAGE_DRIVER");
    }

    $ConfigData['MOBILE_VERIFY_APP_ID_IPHONE'] = "";
    $ConfigData['MOBILE_VERIFY_ACCESS_TOKEN_IPHONE'] = "";
    $ConfigData['FACEBOOK_APP_ID'] = $generalobj->getConfigurations("configurations", "FACEBOOK_APP_ID");
    $ConfigData['MOBILE_VERIFICATION_ENABLE'] = $generalobj->getConfigurations("configurations", "MOBILE_VERIFICATION_ENABLE");
    $ConfigData['REFERRAL_SCHEME_ENABLE'] = $generalobj->getConfigurations("configurations", "REFERRAL_SCHEME_ENABLE");
    $ConfigData['WALLET_ENABLE'] = $generalobj->getConfigurations("configurations", "WALLET_ENABLE");
    $ConfigData['PUBNUB_PUBLISH_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $ConfigData['PUBNUB_SUBSCRIBE_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");
    $ConfigData['PUBNUB_SECRET_KEY'] = $generalobj->getConfigurations("configurations", "PUBNUB_SECRET_KEY");
    $ConfigData['ENABLE_PUBNUB'] = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $ConfigData['IPHONE_PAYMENT_ENABLED'] = $generalobj->getConfigurations("configurations", "IPHONE_PAYMENT_ENABLED");
    $ConfigData['SITE_TYPE'] = SITE_TYPE;

    $defLangValues = get_value('language_master', 'vCode, vGMapLangCode, eDirectionCode as eType', 'eDefault', 'Yes');

    $GeneralConfigData['DefaultLanguageValues'] = $defLangValues[0];

    $GeneralConfigData['GeneralConfigData'] = $ConfigData;

    echo json_encode($GeneralConfigData);
    exit;
}
##########################################################################

############################Send Sms Twilio####################################

if ($type == 'sendVerificationSMS') {

    $mobileNo = isset($_REQUEST['MobileNo']) ? clean($_REQUEST['MobileNo']) : '';
    $mobileNo = str_replace('+', '', $mobileNo);
    //$mobileNo = isset($_REQUEST['MobileNo'])?clean($_REQUEST['MobileNo']):'';
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
        $returnArr['message'] = "";
        if ($sendemail == 0 && $result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ACC_VERIFICATION_FAILED";
        } else {

            $returnArr['message_sms'] = $result == 0 ? "LBL_MOBILE_VERIFICATION_FAILED_TXT" : strval($verificationCode_sms);
            $returnArr['message_email'] = $sendemail == 0 ? "LBL_EMAIL_VERIFICATION_FAILED_TXT" : strval($verificationCode_email);
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
            $returnArr['message'] = strval($verificationCode_sms);
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
            $returnArr['message'] = strval($Data_Mail['CODE']);
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
			$returnArr['action'] ="0";
		}else{
			$returnArr['action'] ="1";
			$returnArr['verificationCode'] = strval($verificationCode);
		}

        echo json_encode($returnArr);
	} */

############################Send Sms Twilio END################################

if ($type == 'language_label') {

    echo json_encode(getLanguageLabelsArr());
    exit;
}
############################ country_list #############################
if ($type == 'countryList') {

    $sql = "SELECT * FROM  `country` WHERE eStatus = 'Active' ";
    $all_label = $obj->MySQLSelect($sql);
    $returnArr['countryList'] = $all_label;
    echo json_encode($returnArr);
    exit;
}
##########################################################################

######################### isUserExist #############################

if ($type == "isUserExist") {

    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '';
    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '0000000000000';

    $sql = "SELECT vEmail,vPhone,vFbId FROM `register_user` WHERE vEmail = '$Emid' OR vPhone = '$Phone' OR vFbId = '$fbid'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {

        $returnArr['Action'] = "0";

        if ($Emid == $Data[0]['vEmail']) {
            $returnArr['Error'] = "EMAIL_EXIST";
        } else if ($Phone == $Data[0]['vPhone']) {
            $returnArr['Error'] = "MOBILE_EXIST";
        } else {
            $returnArr['Error'] = "FB_ACC_EXIST";
        }
    } else {
        $returnArr['Action'] = "1";
    }

    echo json_encode($returnArr);
}

###########################################################################

if ($type == "LoginWithFB") {

    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
    $Fname = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
    $Lname = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
    $email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';


    $DeviceType = "Ios";

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
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang']);
                $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $Data[0]['vLang'], '', 'true');
                $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $Data[0]['vLang'], '', 'true');

            } else {
                $returnArr['changeLangCode'] = "No";
            }


            $returnArr['profileData'] = GetUserDetail($Data[0]['iUserId']);
            $returnArr['Action'] = "1";

            createUserLog("Passenger", "No", $Data[0]['iUserId'], "Ios");

            echo json_encode($returnArr);
        } else {

            $returnArr['Action'] = "0";

            if ($Data[0]['eStatus'] != "Deleted") {
                $returnArr['Error'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            } else {
                $returnArr['Error'] = "ACC_DELETED";
            }


            echo json_encode($returnArr);
            //                echo "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
        }

    } else {
        //            echo "DO_REGISTER";
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "DO_REGISTER";
        echo json_encode($returnArr);
    }
}

###########################################################################

if ($type == "signUp_User") {

    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '0';
    $firstName = isset($_REQUEST["firstName"]) ? $_REQUEST["firstName"] : '';
    $lastName = isset($_REQUEST["lastName"]) ? $_REQUEST["lastName"] : '';
    $email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
    $password = isset($_REQUEST["password"]) ? $_REQUEST["password"] : '';
    $mobile = isset($_REQUEST["mobile"]) ? $_REQUEST["mobile"] : '';
    $Language_Code = isset($_REQUEST["Language_Code"]) ? $_REQUEST["Language_Code"] : '';
    $PhoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $apnId = isset($_REQUEST["ApnId"]) ? $_REQUEST["ApnId"] : '';
    $vInviteCode = isset($_REQUEST["vInviteCode"]) ? $_REQUEST["vInviteCode"] : '';

    //TLOG($_REQUEST);

    $deviceType = "Ios";
    $fbIdCheckField = "";
    $password_encrypt = "";
    if ($password != '') {
        $password_encrypt = $generalobj->encrypt($password);
    }
    if ($fbid != '' && $fbid != '0') {
        $fbIdCheckField = " OR vFbId = '" . $fbid . "'";
    }
    if ($email != '')
        $sql = "SELECT  * FROM  `register_user` WHERE vEmail = '" . $email . "' OR vPhone = '" . $mobile . "'" . $fbIdCheckField;
    else
        $sql = "SELECT  * FROM  `register_user` WHERE vPhone = '" . $mobile . "'" . $fbIdCheckField;

    $Data_error = $obj->MySQLSelect($sql);

    if (count($Data_error) > 0) {
        $returnArr['Action'] = "0";

        if ($email == $Data_error[0]['vEmail']) {
            $returnArr['Error'] = "EMAIL_EXIST";
        } else if ($mobile == $Data_error[0]['vPhone']) {
            $returnArr['Error'] = "MOBILE_EXIST";
        } else if ($fbid == $Data_error[0]['vFbId']) {
            $returnArr['Error'] = "FB_ACC_EXIST";
        } else {
            $returnArr['Error'] = "ERROR";
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
                $returnArr['Error'] = "LBL_INVITE_CODE_INVALID";
                echo json_encode($returnArr);
                exit;
            } else {
                $inviteRes = explode("|", $check_inviteCode);
                $Data_signup['iRefUserId'] = $inviteRes[0];
                $Data_signup['eRefType'] = $inviteRes[1];
                $inviteSuccess = true;
            }
        }

        $Data_signup['vFbId'] = $fbid;
        $Data_signup['vName'] = $firstName;
        $Data_signup['vLastName'] = $lastName;
        $Data_signup['vEmail'] = $email;
        $Data_signup['vPassword'] = $password_encrypt;
        $Data_signup['vPhone'] = $mobile;
        $Data_signup['vLang'] = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $Data_signup['vPhoneCode'] = $PhoneCode;
        $Data_signup['iGcmRegId'] = $apnId;
        $Data_signup['vCountry'] = $CountryCode;
        $Data_signup['eDeviceType'] = $deviceType;
        $Data_signup['dRefDate'] = Date('Y-m-d H:i:s');
        $Data_signup['vRefCode'] = $generalobj->ganaraterefercode("Rider");
        // $Data_signup['vCurrencyPassenger']=($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
        $Data_signup['vCurrencyPassenger'] = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

        $id = $obj->MySQLQueryPerform("register_user", $Data_signup, 'insert');

        if ($id > 0) {

            if ($inviteSuccess == true) {
                $REFERRAL_AMOUNT = $generalobj->getConfigurations("configurations", "REFERRAL_AMOUNT");
                $eFor = "Referrer";
                $tDescription = "Referral amount credited";
                $dDate = Date('Y-m-d H:i:s');
                $ePaymentStatus = "Unsettelled";
                $generalobj->InsertIntoUserWallet($Data_signup['iRefUserId'], $Data_signup['eRefType'], $REFERRAL_AMOUNT, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate);
            }

            $returnArr['Action'] = "1";
            $returnArr['profileData'] = GetUserDetail($id);

            $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
            $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


            if ($Data_checkLangCode[0]['vCode'] != $Language_Code) {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Language_Code);
                $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $Language_Code, '', 'true');
                $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $Language_Code, '', 'true');
            } else {
                $returnArr['changeLangCode'] = "No";
            }

            $maildata['EMAIL'] = $email;
            $maildata['NAME'] = $firstName;
            $maildata['PASSWORD'] = $password;
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);

            echo json_encode($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "FAILED";
            echo json_encode($returnArr);
        }
    }


}

if ($type == "signIn_User") {

    $fbId = isset($_REQUEST["fbId"]) ? $_REQUEST["fbId"] : '';
    $email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
    $password = isset($_REQUEST["password"]) ? $_REQUEST["password"] : '';
    $apnId = isset($_REQUEST["ApnId"]) ? $_REQUEST["ApnId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $DeviceType = "Ios";

    $password_field = "";
    $email_field = "";
    $fbAcc_field = "";

    //TLOG($_REQUEST);


    if ($password != '' && ($fbId == '' || $fbId == "0")) {
        $password = $generalobj->encrypt($password);
        $password_field = " AND `vPassword` = '" . $password . "'";
    }


    if ($fbId == '' || $fbId == "0") {
        $email_field = " (vEmail='{$email}' OR vPhone='{$email}' ) ";
    } else {
        $fbAcc_field = " vFbId = '" . $fbId . "'";
    }

    if (($email == "" || $email == "0") && ($fbId == '' || $fbId == "0")) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }

    if ($UserType == "Passenger") {
        $sql = "SELECT  iUserId,vLang,vTripStatus,eStatus FROM  `register_user` WHERE " . $email_field . $fbAcc_field . $password_field;

        //die("TEST" . $sql);

        $Data = $obj->MySQLSelect($sql);

        if (count($Data) < 1 && ($fbId != '' && $fbId != "0")) {
            $sql = "SELECT  iUserId,vLang FROM  `register_user` WHERE vEmail = '" . $email . "'";
            $Data = $obj->MySQLSelect($sql);

            if (count($Data) > 0) {
                $where = " iUserId = '" . $Data[0]['iUserId'] . "' ";
                $Data_update_fb['vFbId'] = $fbId;
                $id = $obj->MySQLQueryPerform("register_user", $Data_update_fb, 'update', $where);
            }
        }
    } else {
        $sql = "SELECT  iDriverId,vLang,eStatus FROM  `register_driver` WHERE" . $email_field . $password_field;
        $Data = $obj->MySQLSelect($sql);
    }

    if (count($Data) > 0) {
        $returnArr['Action'] = "1";


        $Data_update_user['eDeviceType'] = $DeviceType;
        $Data_update_user['iGcmRegId'] = $apnId;
        if (SITE_TYPE == "Demo") {
            $Data_update_user['tRegistrationDate'] = date('Y-m-d H:i:s');
        }

        if ($UserType == "Passenger") {

            $where = " iUserId = '" . $Data[0]['iUserId'] . "' ";


            $id = $obj->MySQLQueryPerform("register_user", $Data_update_user, 'update', $where);


            if ($Data[0]['vTripStatus'] == "Requesting") {

                $Data_update_passenger['vTripStatus'] = "Not Requesting";

                $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
            }

            $returnArr['profileData'] = GetUserDetail($Data[0]['iUserId']);


            createUserLog($UserType, "No", $Data[0]['iUserId'], "Ios");
        } else {

            $where = " iDriverId = '" . $Data[0]['iDriverId'] . "' ";

            $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);

            $returnArr['profileData'] = GetDriverDetail($Data[0]['iDriverId']);

            createUserLog($UserType, "No", $Data[0]['iDriverId'], "Ios");
        }

        $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
        $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);


        if ($Data_checkLangCode[0]['vCode'] != $Data[0]['vLang']) {
            $returnArr['changeLangCode'] = "Yes";
            $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang']);
            $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $Data[0]['vLang'], '', 'true');
            $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $Data[0]['vLang'], '', 'true');
        } else {
            $returnArr['changeLangCode'] = "No";
        }

        echo json_encode($returnArr);
        exit;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WRONG_DETAIL";
        echo json_encode($returnArr);
        exit;


    }

}

if ($type == "GetUserDetail") {
    $UserId = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $apnId = isset($_REQUEST["ApnId"]) ? $_REQUEST["ApnId"] : '';
    $deviceType = "Ios";

    if ($UserType == "Passenger") {
        // if($apnId != ''){
        // $Data_update_passenger['iGcmRegId']=$apnId;
        // $Data_update_passenger['eDeviceType']=$deviceType;
        // $where = " iUserId = '".$UserId."' ";

        // $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
        // }

        $sql = "SELECT iGcmRegId,vTripStatus FROM `register_user` WHERE iUserId='$UserId'";
        $Data = $obj->MySQLSelect($sql);

        $iGCMregID = $Data[0]['iGcmRegId'];
        $vTripStatus = $Data[0]['vTripStatus'];

        if ($apnId != "" && $apnId != $iGCMregID) {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "SESSION_OUT";
            echo json_encode($returnArr);
            exit;
        }

        if ($vTripStatus == "Requesting") {
            $where = " iUserId = '" . $UserId . "' ";

            $Data_update_tripStatus['vTripStatus'] = "Not Requesting";

            $id = $obj->MySQLQueryPerform("register_user", $Data_update_tripStatus, 'update', $where);
        }
        $Data[0] = GetUserDetail($UserId);

        createUserLog($UserType, "Yes", $UserId, "Ios");
    } else {
        $sql = "SELECT iGcmRegId FROM `register_driver` WHERE iDriverId='$UserId'";
        $Data_Driver = $obj->MySQLSelect($sql);
        $iGCMregID = $Data_Driver[0]['iGcmRegId'];


        if ($apnId != "" && $apnId != $iGCMregID) {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "SESSION_OUT";
            echo json_encode($returnArr);
            exit;
        }

        $Data[0] = GetDriverDetail($UserId);

        createUserLog($UserType, "Yes", $UserId, "Ios");
    }

    if (count($Data[0]) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['profileData'] = $Data[0];
        echo json_encode($returnArr);
    } else {

        $returnArr['Action'] = "0";
        echo json_encode($returnArr);

    }
}

###################################################################################################################################################################
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

/*if ($type == "getBookingDetail_user") {

        $page        = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
        $user_id_auto = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
		$UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
		$eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride';

		$vLanguage=get_value('register_user', 'vLang', 'iUserId',$user_id_auto,'','true');
		if($vLanguage == "" || $vLanguage == NULL){
			$vLanguage = "EN";
		}

        $per_page=10;
        $sql_all  = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iUserId='$user_id_auto' AND (iActive='Canceled' || iActive='Finished') AND eType='".$eType."'";
        $data_count_all = $obj->MySQLSelect($sql_all);
        $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

        $start_limit = ($page - 1) * $per_page;
        $limit       = " LIMIT " . $start_limit . ", " . $per_page;

        // $sql = "SELECT * FROM `trips`  WHERE  iUserId='$user_id_auto' AND (iActive='Canceled' || iActive='Finished') ORDER BY iTripId DESC" . $limit;
		$sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$user_id_auto' AND tr.eType='$eType' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
        $Data = $obj->MySQLSelect($sql);
        $totalNum = count($Data);

        $i=0;
        if ( count($Data) > 0 ) {

            $row = $Data;
            while ( count($row)> $i ) {

                $rows_driver_data    = array();
                $rows_driver_vehicle = array();
                $row_driver_id = $row[$i]['iDriverId'];

                $row_trip_request_date = date('dS M \a\t h:i a',strtotime($row[$i]['tTripRequestDate']));
                $row[$i]['ConvertedTripRequestDate'] = $row_trip_request_date;

				$sql = "SELECT iBalance, eType FROM `user_wallet` WHERE iTripId='".$row[$i]['iTripId']."'";
				$user_debit_data = $obj->MySQLSelect($sql);

				$row[$i]['UserDebitAmount'] = "0";
				if(count($user_debit_data) > 0 && $user_debit_data[0]['eType'] == "Debit"){
					$row[$i]['UserDebitAmount'] = strval($user_debit_data[0]['iBalance']);
				}

                // $priceRatio=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$row[$i]['vCurrencyPassenger']."' ")[0]['Ratio']);

                $vehicleType=$row[$i]['iVehicleTypeId'];
                $sql = "SELECT vVehicleType_".$vLanguage." as vVehicleType FROM `vehicle_type`  WHERE  iVehicleTypeId='$vehicleType'";
                $vehicleType_data = $obj->MySQLSelect($sql);

                $row[$i]['vVehicleType']=$vehicleType_data[0]['vVehicleType'];

                $startDate=$row[$i]['tStartDate'];
                $endDateOfTrip=$row[$i]['tEndDate'];

                $totalTimeInMinutes_trip=@round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60,2);

                $diff = @abs(strtotime($endDateOfTrip) - strtotime($startDate));
                $years = floor($diff / (365*60*60*24)); $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
                $hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
                $minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
                $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));

                // $row[$i]['TripTimeInMinutes']=$hours.':'.$minuts.':'.$seconds;

                $row[$i]['TripFareOfMinutes']=strval(round($row[$i]['fPricePerMin']* $row[$i]['fRatioPassenger'],1));
                $row[$i]['TripFareOfDistance']=strval(round($row[$i]['fPricePerKM']* $row[$i]['fRatioPassenger'],1));

                $row[$i]['iFare']=strval(round($row[$i]['iFare'] * $row[$i]['fRatioPassenger'],1));
                $row[$i]['iBaseFare']=strval(round($row[$i]['iBaseFare'] * $row[$i]['fRatioPassenger'],1));
                $row[$i]['fDiscount']=strval(round($row[$i]['fDiscount'] * $row[$i]['fRatioPassenger'],1));
                $row[$i]['fCommision']= strval(round($row[$i]['fCommision']* $row[$i]['fRatioPassenger'],1));

                $row[$i]['fWalletDebit'] = strval(number_format(round( $row[$i]['fWalletDebit']* $row[$i]['fRatioPassenger'],1),2));
			         $row[$i]['fSurgePriceDiff'] = strval(number_format(round( $row[$i]['fSurgePriceDiff']* $row[$i]['fRatioPassenger'],1),2));

        			 $surgePrice = 1;
        			if($row[$i]['fPickUpPrice'] > 0){
        				$surgePrice= $row[$i]['fPickUpPrice'];
        			}else{
        				$surgePrice= $row[$i]['fNightPrice'];
        			}
        			 $row[$i]['SurgePriceFactor'] = strval($surgePrice);

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

                // $row[$i]['PassengerCurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$row[$i]['vCurrencyPassenger']."' ")[0]['vSymbol']);
               $row[$i]['PassengerCurrencySymbol']=get_value('currency', 'vSymbol', 'vName', $row[$i]['vCurrencyPassenger'],'','true');

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
            $returnData['getBookingDetail_user']=$row;
            if ($TotalPages > $page) {
                $returnData['NextPage'] = $page + 1;
				} else {
                $returnData['NextPage'] = 0;
			}
            echo json_encode($returnData);

			}else{
            $getBookingDetail_user =array();
            $returnData['getBookingDetail_user']=$getBookingDetail_user;
            $returnData['NextPage'] = 0;
            echo json_encode($returnData);
		}

	}   */

########################### Get Available Taxi ##############################
if ($type == "getAvailableTaxi") {

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';

    $Data = getOnlineDriverArr($passengerLat, $passengerLon);

    if (count($Data) > 0) {

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

            $Data[$i]['DriverCarDetails'] = $rows_driver_vehicle[0];

            $i++;
        }
    }

    $where = " iUserId=" . $iUserId;
    $data['vLatitude'] = $passengerLat;
    $data['vLongitude'] = $passengerLon;
    $data['tLastOnline'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("register_user", $data, 'update', $where);

    $returnArr['getAvailableTaxi'] = $Data;
    $returnArr['PassengerLat'] = strval($passengerLat);
    $returnArr['PassengerLon'] = strval($passengerLon);
    $returnArr['Address'] = strval($address);
    echo json_encode($returnArr);
}
###########################################################################

###########################################################################

if ($type == "updateUserProfileDetail") {

    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '';
    $vLastName = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $vPhone = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $phoneCode = isset($_REQUEST["vPhoneCode"]) ? $_REQUEST['vPhoneCode'] : '';
    $vCountry = isset($_REQUEST["vCountry"]) ? $_REQUEST['vCountry'] : '';
    $vEmail = isset($_REQUEST["vEmail"]) ? $_REQUEST['vEmail'] : '';
    $currencyCode = isset($_REQUEST["CurrencyCode"]) ? $_REQUEST['CurrencyCode'] : '';
    $languageCode = isset($_REQUEST["LanguageCode"]) ? $_REQUEST['LanguageCode'] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST['UserType'] : 'Passenger';

    if ($userType != "Driver") {
        $vEmail_userId_check = get_value('register_user', 'iUserId', 'vEmail', $vEmail, '', 'true');
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

    if ($vEmail_userId_check != "" && $vEmail_userId_check != $iMemberId) {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "LBL_ALREADY_REGISTERED_TXT";
        echo json_encode($returnArr);
        exit;
    }
    if ($vPhone_userId_check != "" && $vPhone_userId_check != $iMemberId) {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "LBL_MOBILE_EXIST";
        echo json_encode($returnArr);
        exit;
    }

    if ($vPhone_orig != $vPhone || $vPhoneCode_orig != $phoneCode) {
        $Data_update_User['ePhoneVerified'] = "No";
    }
    if ($vEmail_orig != $vEmail) {
        $Data_update_User['eEmailVerified'] = "No";
    }

    // $currentLanguageCode = ($obj->MySQLSelect("SELECT vLang FROM ".$tableName." WHERE".$where)[0]['vLang']);

    $Data_update_User['vName'] = $vName;
    $Data_update_User['vLastName'] = $vLastName;
    $Data_update_User['vPhone'] = $vPhone;
    $Data_update_User['vCountry'] = $vCountry;
    $Data_update_User['vLang'] = $languageCode;
    $Data_update_User['vEmail'] = $vEmail;


    $id = $obj->MySQLQueryPerform($tableName, $Data_update_User, 'update', $where);

    if ($currentLanguageCode != $languageCode) {
        $returnArr['changeLangCode'] = "Yes";
        $returnArr['LanguageLabels'] = getLanguageLabelsArr($languageCode, "1");
        $returnArr['vLanguageCode'] = $languageCode;
        $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode', $languageCode, '', 'true');
        $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode', $languageCode, '', 'true');
    } else {
        $returnArr['changeLangCode'] = "No";
    }
    if ($userType != "Driver") {
        $returnArr['profileData'] = GetUserDetail($iMemberId);
    } else {
        $returnArr['profileData'] = GetDriverDetail($iMemberId);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

}

/* if ($type == "updateUserDetail") {

        $UserType        = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : ''; // UserType = Driver/Passenger
        $Fname        = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
        $Lname        = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
        $Umobile      = isset($_REQUEST["mobile"]) ? $_REQUEST["mobile"] : '';
        $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
        $phoneCode = isset($_REQUEST["phoneCode"]) ? $_REQUEST["phoneCode"] : '';
		$currencyCode = isset($_REQUEST["CurrencyCode"]) ? $_REQUEST['CurrencyCode'] : '';
		$languageCode = isset($_REQUEST["LanguageCode"]) ? $_REQUEST['LanguageCode'] : '';

        $Data_update_user['vName']=$Fname;
        $Data_update_user['vLastName']=$Lname;
        $Data_update_user['vPhone']=$Umobile;
		$Data_update_user['vLang']=$languageCode;

        $id = 0;

        if($UserType == "Passenger"){
			$currentLanguageCode =  get_value('register_user', 'vLang', 'iUserId',$user_id_auto,'','true');

            $where = " iUserId = '$user_id_auto'";

            $Data_update_user['vPhoneCode']=$phoneCode;
			$Data_update_user['vCurrencyPassenger']=$currencyCode;
            $id = $obj->MySQLQueryPerform("register_user",$Data_update_user,'update',$where);

            if ($id >0) {

                $returnArr['Action']="1";
                $returnArr['profileData']=GetUserDetail($user_id_auto);

            } else {

                $returnArr['Action']="0";
                $returnArr['Error']="FAILED";
			}

		}else{
			$currentLanguageCode =  get_value('register_driver', 'vLang', 'iDriverId',$user_id_auto,'','true');

            $where = " iDriverId = '$user_id_auto'";

            $Data_update_user['vCode']=$phoneCode;
			$Data_update_user['vCurrencyDriver']=$currencyCode;

            $id = $obj->MySQLQueryPerform("register_driver",$Data_update_user,'update',$where);


            if ($id >0) {

                $returnArr['Action']="1";
                $returnArr['profileData']=GetDriverDetail($user_id_auto);

            } else {

                $returnArr['Action']="0";
                $returnArr['Error']="FAILED";

			}
		}

		if($currentLanguageCode != $languageCode){
		   $returnArr['changeLangCode'] ="Yes";
		   $returnArr['LanguageLabels'] = getLanguageLabelsArr($languageCode,"1");
		   $returnArr['vLanguageCode'] = $languageCode;
		   $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode',$languageCode,'','true');
		   $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode',$languageCode,'','true');
		}else{
			$returnArr['changeLangCode'] ="No";
		}

        echo json_encode($returnArr);

	} */

###########################################################################


if ($type == "UpdateLanguageCode") {

    $lCode = isset($_REQUEST['vCode']) ? clean(strtoupper($_REQUEST['vCode'])) : ''; // User's prefered language
    $UserID = isset($_REQUEST['UserID']) ? clean($_REQUEST['UserID']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : ''; // UserType = Driver/Passenger


    if ($UserType == "Passenger") {
        $where = " iUserId = '$UserID'";
        $Data_update_passenger['vLang'] = $lCode;

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

        if ($id < 0) {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "FAILED";
            echo json_encode($returnArr);
            exit;
        }
    } else if ($UserType == "Driver") {
        $where = " iDriverId = '$UserID'";
        $Data_update_driver['vLang'] = $lCode;

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

        if ($id < 0) {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "FAILED";
            echo json_encode($returnArr);
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

    $x['LanguageLabels'] = array();
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x['LanguageLabels'][$vLabel] = $vValue;
    }
    $x['LanguageLabels']['vCode'] = $lCode; // to check in which languge code it is loading
    $x['Action'] = "1";


    if ($UserType == "Passenger") {
        $x['profileData'] = GetUserDetail($UserID);
    } else if ($UserType == "Driver") {
        $x['profileData'] = GetDriverDetail($UserID);
    }


    echo json_encode($x);

}
###########################################################################

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
            $returnArr['profileData'] = GetUserDetail($user_id);
            echo json_encode($returnArr);

        } else {

            $returnArr['Action'] = "0";
            $returnArr['Error'] = "FAILED";
            echo json_encode($returnArr);
        }

    } else {
        $where = " iDriverId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);


        if ($id > 0) {

            $returnArr['Action'] = "1";
            $returnArr['profileData'] = GetDriverDetail($user_id);
            echo json_encode($returnArr);

        } else {

            $returnArr['Action'] = "0";
            $returnArr['Error'] = "FAILED";
            echo json_encode($returnArr);
        }
    }

}

###########################################################################

if ($type == "uploadProfileImage") {

    ini_set('memory_limit', '2048M');
    $target_path = "webimages/upload/";
    $UserType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger
    $user_id = isset($_REQUEST['UserID']) ? $_REQUEST['UserID'] : '';
    $base = isset($_REQUEST['image']) ? $_REQUEST['image'] : '';
    $name = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';

    if ($UserType == "Passenger") {
        $target_path_temp = $target_path . "Passenger/";
    } else {
        $target_path_temp = $target_path . "Driver/";
    }

    $time_val = time();
    $target_dir = $target_path_temp . $user_id . "/";
    $fileextension = "jpg";
    $Random_filename = mt_rand(11111, 99999);

    $ImgFileName = $time_val . "_" . $Random_filename . "." . $fileextension;

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_path = $target_dir . "/" . $ImgFileName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_path)) {

        $id = 0;
        if ($UserType == "Passenger") {
            $where = " iUserId = '" . $user_id . "'";
            $Data_user['vImgName'] = $ImgFileName;
            $id = $obj->MySQLQueryPerform("register_user", $Data_user, 'update', $where);
        } else {
            $where = " iDriverId = '" . $user_id . "'";
            $Data_user['vImage'] = $ImgFileName;
            $id = $obj->MySQLQueryPerform("register_driver", $Data_user, 'update', $where);
        }

        if ($id > 0) {

            $thumb->createthumbnail($target_dir . '/' . $ImgFileName); // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size1"]);    // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);
            $thumb->save($target_dir . "1" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_dir . "/" . $ImgFileName);   // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size2"]);       // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);      // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_dir . "2" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_dir . "/" . $ImgFileName);   // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size3"]);       // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);     // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_dir . "3" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $returnArrayImg['Action'] = "1";
            //                $returnArrayImg['ImgName']='3_'.$ImgFileName;
            if ($UserType == "Passenger") {
                $returnArrayImg['profileData'] = GetUserDetail($user_id);
            } else {
                $returnArrayImg['profileData'] = GetDriverDetail($user_id);
            }
            echo json_encode($returnArrayImg);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "FAILED";
            echo json_encode($returnArr);
        }


    } else {

        $returnArr['Action'] = "0";
        $returnArr['Error'] = "FAILED";
        echo json_encode($returnArr);

    }

    exit;

}

###########################################################################

if ($type == "uploadImage") {
    global $generalobj, $tconfig;

    $iMemberId = isset($_REQUEST['UserID']) ? clean($_REQUEST['UserID']) : '';
    $memberType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $image_name = "123.jpg";

    if ($memberType == "Driver") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_driver_path'] . "/" . $iMemberId . "/";
    } else {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_path'] . "/" . $iMemberId . "/";
    }

    if (!is_dir($Photo_Gallery_folder))
        mkdir($Photo_Gallery_folder, 0777);

    $vImageName = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);

    if ($vImageName != '') {
        if ($memberType == "Driver") {
            $where = " iDriverId = '" . $iMemberId . "'";
            $Data_passenger['vImage'] = $vImageName;
            $id = $obj->MySQLQueryPerform("register_driver", $Data_passenger, 'update', $where);
        } else {
            $where = " iUserId = '" . $iMemberId . "'";
            $Data_passenger['vImgName'] = $vImageName;
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where);
        }


        if ($id > 0) {
            $returnArrayImg['Action'] = "1";
            if ($memberType == "Driver") {
                $returnArrayImg['profileData'] = GetDriverDetail($iMemberId);
            } else {
                $returnArrayImg['profileData'] = GetUserDetail($iMemberId);
            }
            echo json_encode($returnArrayImg);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "FAILED";
            echo json_encode($returnArr);
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "FAILED";
        echo json_encode($returnArr);
    }

}

######################## Get Driver Car Detail ############################
if ($type == "getDriverCarDetail") {
    $Did = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverId='$Did' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'";

    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {

        $i = 0;
        while (count($Data) > $i) {

            $Data[$i]['vModel'] = $Data[$i]['vTitle'];
            $i++;
        }
        $returnArr['Action'] = "1";
        $returnArr['carList'] = $Data;

        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0"; //duplicate entry
        $returnArr['message'] = 'Fail';

        echo json_encode($returnArr);
    }

}
###########################################################################

########################### Set Driver CarID ############################
if ($type == "SetDriverCarID") {

    $Did = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Data['iDriverVehicleId'] = isset($_REQUEST["DriverVehicleId"]) ? $_REQUEST["DriverVehicleId"] : '';

    $where = " iDriverId = '" . $Did . "'";

    $sql = $obj->MySQLQueryPerform("register_driver", $Data, 'update', $where);
    if ($sql > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
    }

    echo json_encode($returnArr);
}
###########################################################################

###########################################################################

if ($type == "updateDriverStatus") {

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Status_driver = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $isUpdateOnlineDate = isset($_REQUEST["isUpdateOnlineDate"]) ? $_REQUEST["isUpdateOnlineDate"] : '';
    $iGCMregID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';

    checkmemberemailphoneverification($iDriverId, "Driver");

    $GCMID = get_value('register_driver', 'iGcmRegId', 'iDriverId', $iDriverId, '', 'true');
    if ($GCMID != "" && $iGCMregID != "" && $GCMID != $iGCMregID) {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "SESSION_OUT";
        echo json_encode($returnArr);
        exit;
    }

    $ServiceCity = "";


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
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "SELECT_CAR";
            echo json_encode($returnArr);
            exit;
        } else if ($status == "CARS_NOT_ACTIVE") {
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "CARS_NOT_ACTIVE";
            echo json_encode($returnArr);
            exit;
        }


    } else {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "NO_CARS_AVAIL";
        echo json_encode($returnArr);
        exit;
    }

    $where = " iDriverId='$iDriverId'";
    $Data_update_driver['vAvailability'] = $Status_driver;
    $Data_update_driver['vLatitude'] = $latitude_driver;
    $Data_update_driver['vLongitude'] = $longitude_driver;
    $Data_update_driver['vServiceLoc'] = $ServiceCity;


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

    if ($isUpdateOnlineDate == "true" && $Status_driver == "Available") {
        $Data_update_driver['tOnline'] = @date("Y-m-d H:i:s");
    }

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "FAILED";
    }
    echo json_encode($returnArr);
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

        /* if($status == "CARS_AVAIL"){
				echo "SELECT_CAR";
				exit;
				}else */
        if ($status == "CARS_NOT_ACTIVE") {
            //                 echo "CARS_NOT_ACTIVE";
            $returnArr['Action'] = "0";
            $returnArr['Error'] = "CARS_NOT_ACTIVE";
            echo json_encode($returnArr);
            exit;
        }
        $returnArr['Action'] = "1";
        $returnArr['carList'] = $Data_Car;

        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "NO_CARS_AVAIL";
        //            echo "NO_CARS_AVAIL";
        echo json_encode($returnArr);
        exit;
    }
}

###########################################################################

if ($type == "UpdateLastOnline_Driver") {

    $Did = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $availabilityStatus = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';

    $where = " iDriverId='$Did'";

    $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
    $Data_update_driver['vAvailability'] = $availabilityStatus;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
    }
    echo json_encode($returnArr);
}
###########################################################################

if ($type == "requestNearestCab") {

    $driver_id_auto = isset($_REQUEST["DriverIds"]) ? $_REQUEST["DriverIds"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $passengerId = isset($_REQUEST["UserID"]) ? $_REQUEST["UserID"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    $sourceLatitude = isset($_REQUEST["SourceLatitude"]) ? $_REQUEST["SourceLatitude"] : '';
    $sourceLongitude = isset($_REQUEST["SourceLongitude"]) ? $_REQUEST["SourceLongitude"] : '';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $DestAddress = isset($_REQUEST["DestAddress"]) ? $_REQUEST["DestAddress"] : '';
    $promoCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iPackageTypeId = isset($_REQUEST["iPackageTypeId"]) ? $_REQUEST["iPackageTypeId"] : '';
    $vReceiverName = isset($_REQUEST["vReceiverName"]) ? $_REQUEST["vReceiverName"] : '';
    $vReceiverMobile = isset($_REQUEST["vReceiverMobile"]) ? $_REQUEST["vReceiverMobile"] : '';
    $tPickUpIns = isset($_REQUEST["tPickUpIns"]) ? $_REQUEST["tPickUpIns"] : '';
    $tDeliveryIns = isset($_REQUEST["tDeliveryIns"]) ? $_REQUEST["tDeliveryIns"] : '';
    $tPackageDetails = isset($_REQUEST["tPackageDetails"]) ? $_REQUEST["tPackageDetails"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';

    $tReturn = isset($_REQUEST["tReturn"]) ? $_REQUEST["tReturn"] : 'false';
    $tSecDestination = isset($_REQUEST["tSecDestination"]) ? $_REQUEST["tSecDestination"] : 'false';
    $tSecDestLatitude = isset($_REQUEST["secDestLatitude"]) ? $_REQUEST["secDestLatitude"] : '';
    $tSecDestLongitude = isset($_REQUEST["secDestLongitude"]) ? $_REQUEST["secDestLongitude"] : '';
    $tSecDestAddress = isset($_REQUEST["secDestAddress"]) ? $_REQUEST["secDestAddress"] : '';

    $trip_status = "Requesting";

    checkmemberemailphoneverification($passengerId, "Passenger");

    $iGcmRegId = get_value('register_user', 'iGcmRegId', 'iUserId', $passengerId, '', 'true');
    $tripStatus = get_value('register_user', 'vTripStatus', 'iUserId', $passengerId, '', 'true');


    if ($tripStatus == "Active" || $tripStatus == "On Going Trip") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_RESTART";
        echo json_encode($returnArr);
        exit;
    }
    /*$vLangCode=get_value('register_driver', 'vLang', 'iDriverId',$driver_id_auto,'','true');
    if($vLangCode == "" || $vLangCode == NULL){
       $vLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
    }*/
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];

    if ($vDeviceToken != "" && $vDeviceToken != $iGcmRegId) {
        $returnArr['Action'] = "0";
        $returnArr['Error'] = "SESSION_OUT";
        echo json_encode($returnArr);
        exit;
    }

    $passengerFName = get_value('register_user', 'vName', 'iUserId', $passengerId, '', 'true');
    $passengerLName = get_value('register_user', 'vLastName', 'iUserId', $passengerId, '', 'true');
    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($sourceLatitude);
    $final_message['sourceLongitude'] = strval($sourceLongitude);
    $final_message['PassengerId'] = strval($passengerId);
    $final_message['PName'] = $passengerFName . " " . $passengerLName;
    $final_message['PPicName'] = get_value('register_user', 'vImgName', 'iUserId', $passengerId, '', 'true');
    $final_message['PFId'] = get_value('register_user', 'vFbId', 'iUserId', $passengerId, '', 'true');
    $final_message['PRating'] = get_value('register_user', 'vAvgRating', 'iUserId', $passengerId, '', 'true');
    $final_message['PPhone'] = get_value('register_user', 'vPhone', 'iUserId', $passengerId, '', 'true');
    $final_message['PPhoneC'] = get_value('register_user', 'vPhoneCode', 'iUserId', $passengerId, '', 'true');
    $final_message['REQUEST_TYPE'] = $eType;
    $final_message['PACKAGE_TYPE'] = $eType == "Deliver" ? get_value('package_type', 'vName', 'iPackageTypeId', $iPackageTypeId, '', 'true') : '';
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);

    $final_message['tSecDestination'] = strval($tSecDestination);
    $final_message['tReturn'] = strval($tReturn);
    $final_message['secDestLatitude'] = strval($tSecDestLatitude);
    $final_message['secDestLongitude'] = strval($tSecDestLongitude);
    $final_message['secDestAddress'] = strval($tSecDestAddress);

    $final_message['MsgCode'] = strval(mt_rand(1000, 9999));

    $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
    $ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');
    $eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');

    $Data = getOnlineDriverArr($sourceLatitude, $sourceLongitude);

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

    $str_date = @date('Y-m-d H:i:s', strtotime('-1440 minutes'));
    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLastOnline > '$str_date' AND vAvailability='Available'";
    $result = $obj->MySQLSelect($sql);

    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "NO_CARS";
        echo json_encode($returnArr);
        exit;
    }

    //$alertMsg = "Passenger is waiting for you";
    $alertMsg = $userwaitinglabel;

    if ($cashPayment == 'true') {
        $tripPaymentMode = "Cash";
    } else {
        $tripPaymentMode = "Card";
    }

    $where = " iUserId = '$passengerId'";

    $Data_update_passenger['vTripStatus'] = $trip_status;

    // if(($generalobj->getConfigurations("configurations","PAYMENT_ENABLED")) == 'Yes'){
    $Data_update_passenger['vTripPaymentMode'] = $tripPaymentMode;
    // }else{
    // $Data_update_passenger['vTripPaymentMode']="Cash";
    // }
    $Data_update_passenger['iSelectedCarType'] = $selectedCarTypeID;
    $Data_update_passenger['tDestinationLatitude'] = $DestLatitude;
    $Data_update_passenger['tDestinationLongitude'] = $DestLongitude;
    $Data_update_passenger['tDestinationAddress'] = $DestAddress;
    $Data_update_passenger['vCouponCode'] = $promoCode;
    $Data_update_passenger['fPickUpPrice'] = $fPickUpPrice;
    $Data_update_passenger['fNightPrice'] = $fNightPrice;
    $Data_update_passenger['eType'] = $eType;
    $Data_update_passenger['iPackageTypeId'] = $eType == "Deliver" ? $iPackageTypeId : '0';
    $Data_update_passenger['vReceiverName'] = $eType == "Deliver" ? $vReceiverName : '';
    $Data_update_passenger['vReceiverMobile'] = $eType == "Deliver" ? $vReceiverMobile : '';
    $Data_update_passenger['tPickUpIns'] = $eType == "Deliver" ? $tPickUpIns : '';
    $Data_update_passenger['tDeliveryIns'] = $eType == "Deliver" ? $tDeliveryIns : '';
    $Data_update_passenger['tPackageDetails'] = $eType == "Deliver" ? $tPackageDetails : '';

    $Data_update_passenger['tSecDestination'] = strval($tSecDestination);
    $Data_update_passenger['tReturn'] = strval($tReturn);
    $Data_update_passenger['secDestLatitude'] = strval($tSecDestLatitude);
    $Data_update_passenger['secDestLongitude'] = strval($tSecDestLongitude);
    $Data_update_passenger['secDestAddress'] = $tSecDestAddress;
//

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");

    if ($ENABLE_PUBNUB == "Yes") {
        $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);


        $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
        $driverIds_arr = explode(",", $filter_driver_ids);

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        for ($i = 0; $i < count($driverIds_arr); $i++) {

            addToUserRequest($passengerId, $driverIds_arr[$i], $msg_encode, $final_message['MsgCode']);
            addToDriverRequest($driverIds_arr[$i], $passengerId, 0, "Timeout");


            /* For PubNub Setting */
            $iAppVersion = get_value("register_driver", 'iAppVersion', "iDriverId", $driverIds_arr[$i], '', 'true');
            $eDeviceType = get_value("register_driver", 'eDeviceType', "iDriverId", $driverIds_arr[$i], '', 'true');
            $vDeviceToken = get_value("register_driver", 'iGcmRegId', "iDriverId", $driverIds_arr[$i], '', 'true');
            /* For PubNub Setting Finished */

            // if($iAppVersion > 1 /* && $eDeviceType == "Android" */){

            $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];
            $info = $pubnub->publish($channelName, $msg_encode);

            // }else{
            if ($eDeviceType != "Android") {
                array_push($deviceTokens_arr_ios, $vDeviceToken);
            }/* else{
						array_push($deviceTokens_arr_ios, $vDeviceToken);
					} */
            // }
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

//echo print_r($result);die();

        foreach ($result as $item) {
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
            }

            addToUserRequest($passengerId, $item['iDriverId'], $msg_encode, $final_message['MsgCode']);
            addToDriverRequest($item['iDriverId'], $passengerId, 0, "Timeout");
        }

        if (count($registation_ids_new) > 0) {
            // $Rmessage = array("message" => $message);
            $Rmessage = array("message" => $msg_encode);

            $result = send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            //            $message = stripslashes(preg_replace("/[\n\r]/","",$message));
            // sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0);
        }
    }
    $returnArr['Action'] = "1";
    echo json_encode($returnArr);
}

###########################################################################

if ($type == "cancelCabRequest") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    $vTripStatus = get_value('register_user', 'vTripStatus', 'iUserId', $iUserId, '', 'true');

#        $where = " iUserId='$iUserId'";
#        $Data_update_passenger['vTripStatus']="Not Requesting";
#
#        $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
#
#        if ($id) {CC
#            $returnArr['Action'] = "1";
#            echo json_encode($returnArr);
#			}else{
#            $returnArr['Action'] = "0";
#            echo json_encode($returnArr);
#	}


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

###########################################################################

if ($type == "addDestination") {

    $userId = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $Latitude = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $Longitude = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
    $Address = isset($_REQUEST["Address"]) ? $_REQUEST["Address"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    if ($userType != "Driver") {
        $sql = "SELECT ru.iTripId,tr.iDriverId,rd.vTripStatus as driverStatus,rd.iGcmRegId as regId,rd.eDeviceType as deviceType FROM register_user as ru,trips as tr,register_driver as rd WHERE ru.iUserId='$userId' AND tr.iTripId=ru.iTripId AND rd.iDriverId=tr.iDriverId";
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

            if ($ENABLE_PUBNUB == "Yes") {

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
    }

    echo json_encode($returnArr);

}


###########################################################################

if ($type == "GenerateTrip") {

    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $Source_point_latitude = isset($_REQUEST["start_lat"]) ? $_REQUEST["start_lat"] : '';
    $Source_point_longitude = isset($_REQUEST["start_lon"]) ? $_REQUEST["start_lon"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    //$iCabBookingId    = isset($_REQUEST["iBookingId"]) ? $_REQUEST["iBookingId"] : '';
    $iBookingId = isset($_REQUEST["iBookingId"]) ? $_REQUEST["iBookingId"] : '';

    if ($iCabBookingId != "") {
        $bookingData = get_value('cab_booking', 'iUserId,vSourceLatitude,vSourceLongitude,vSourceAddresss', 'iCabBookingId', $iCabBookingId);
        $passenger_id = $bookingData[0]['iUserId'];
        $Source_point_latitude = $bookingData[0]['vSourceLatitude'];
        $Source_point_longitude = $bookingData[0]['vSourceLongitude'];
        $Source_point_Address = $bookingData[0]['vSourceAddresss'];
    }

    $TripRideNO = rand(10000000, 99999999);
    $TripVerificationCode = rand(1000, 9999);
    $Active = "Active";
    $DriverMessage = "CabRequestAccepted";
    $Source_point_Address = "";

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $passenger_id, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $tripdriverarrivlbl = $languageLabelsArr['LBL_DRIVER_ARRIVING'];


    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $Source_point_latitude . "," . $Source_point_longitude . "&key=" . $GoogleServerKey . "&language=" . $vGMapLangCode;

    try {

        $jsonfile = file_get_contents($url);
        $jsondata = json_decode($jsonfile);
        $source_address = $jsondata->results[0]->formatted_address;

        $Source_point_Address = $source_address;

    } catch (ErrorException $ex) {

        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
    }

    //     if($Source_point_Address == ""){
    //
    //         $returnArr['Action'] = "0";
    //         echo json_encode($returnArr);
    //         exit;
    // }
    $sql = "SELECT tSecDestination,tReturn,secDestLatitude,secDestLongitude,secDestAddress,vCallFromDriver,vTripStatus,vTripPaymentMode,iSelectedCarType,tDestinationLatitude,tDestinationLongitude,tDestinationAddress,vCurrencyPassenger,vCouponCode,fPickUpPrice,fNightPrice,iAppVersion, eType,iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,tPackageDetails FROM register_user WHERE iUserId='$passenger_id'";
    $check_row = $obj->MySQLSelect($sql);

    $check_assign_driver = $check_row[0]['vCallFromDriver'];

    if ($check_assign_driver != "assign") {


        $check_trip_request = $check_row[0]['vTripStatus'];

        if ($check_trip_request == "Requesting" || $iCabBookingId != "") {

            $where = " iUserId = '$passenger_id'";

            $Data_update_passenger['vCallFromDriver'] = 'assign';

            $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

            //trip tabel//
            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion FROM `register_driver` WHERE iDriverId = '$driver_id'";
            $Data_vehicle = $obj->MySQLSelect($sql);

            $CAR_id_driver = $Data_vehicle[0]['iDriverVehicleId'];

            if ($iCabBookingId != "") {
                $sql_booking = "SELECT vDestLatitude,vDestLongitude,tDestAddress,ePayType,iVehicleTypeId,fPickUpPrice,fNightPrice,eType,iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,tPackageDetails,vCouponCode FROM cab_booking WHERE iCabBookingId='$iCabBookingId'";
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
                $vCouponCode = $check_row[0]['vCouponCode'];
            }

            $Data_trips['vRideNo'] = $TripRideNO;
            $Data_trips['iUserId'] = $passenger_id;
            $Data_trips['iDriverId'] = $driver_id;
            $Data_trips['tTripRequestDate'] = @date("Y-m-d H:i:s");
            $Data_trips['tStartLat'] = $Source_point_latitude;
            $Data_trips['tStartLong'] = $Source_point_longitude;
            $Data_trips['tSaddress'] = $Source_point_Address;
            $Data_trips['iActive'] = $Active;
            $Data_trips['iDriverVehicleId'] = $CAR_id_driver;
            $Data_trips['iVerificationCode'] = $TripVerificationCode;
            $Data_trips['iVehicleTypeId'] = $iSelectedCarType;
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

            if ($iCabBookingId != "") {

            } else {
                $Data_trips['tSecDestination'] = $check_row[0]['tSecDestination'];
                $Data_trips['tReturn'] = $check_row[0]['tReturn'];
                $Data_trips['secDestLatitude'] = $check_row[0]['secDestLatitude'];
                $Data_trips['secDestLongitude'] = $check_row[0]['secDestLongitude'];
                $Data_trips['secDestAddress'] = $check_row[0]['secDestAddress'];
            }
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

            //                $message=$DriverMessage.'::-'.$iTripId.'::-'.$TripVerificationCode;
            $message_arr = array();
            $message_arr['iDriverId'] = $driver_id;
            $message_arr['Message'] = $DriverMessage;
            $message_arr['iTripId'] = strval($iTripId);
            $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
            if ($iCabBookingId != "") {
                $message_arr['iCabBookingId'] = $iCabBookingId;
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

                if ($ENABLE_PUBNUB == "Yes") {

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

                $data['Action'] = "1";
                $data['iTripId'] = strval($iTripId);
                $data['Update_Trip_Status'] = "1";
                $data['PassengerId'] = strval($passenger_id);
                $data['tEndLat'] = $check_row[0]['tDestinationLatitude'];
                $data['tEndLong'] = $check_row[0]['tDestinationLongitude'];
                $data['tDaddress'] = $check_row[0]['tDestinationAddress'];
                $data['PAppVersion'] = $check_row[0]['iAppVersion'];
                $data['tSecDestination'] = $check_row[0]['tSecDestination'];
                $data['tReturn'] = $check_row[0]['tReturn'];
                $data['secDestLatitude'] = $check_row[0]['secDestLatitude'];
                $data['secDestLongitude'] = $check_row[0]['secDestLongitude'];
                $data['secDestAddress'] = $check_row[0]['secDestAddress'];

                if ($iCabBookingId != "") {
                    $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode', 'iUserId', $passenger_id);
                    $data['sourceLatitude'] = strval($Source_point_latitude);
                    $data['sourceLongitude'] = strval($Source_point_longitude);
                    $data['PassengerId'] = strval($passenger_id);
                    $data['PName'] = $passengerData[0]['vName'] . ' ' . $passengerData[0]['vLastName'];
                    $data['PPicName'] = $passengerData[0]['vImgName'];
                    $data['PFId'] = strval($passengerData[0]['vFbId']);
                    $data['PRating'] = strval($passengerData[0]['vAvgRating']);
                    $data['PPhone'] = strval($passengerData[0]['vPhone']);
                    $data['PPhoneC'] = strval($passengerData[0]['vPhoneCode']);
                    $data['TripId'] = strval($iTripId);
                    $data['DestLocLatitude'] = strval($tDestinationLatitude);
                    $data['DestLocLongitude'] = strval($tDestinationLongitude);
                    $data['DestLocAddress'] = $tDestinationAddress;

                }
                $data['REQUEST_TYPE'] = $eType;
                echo json_encode($data);

            } else {
                $returnArr['Action'] = "0";

                echo json_encode($returnArr);
                exit;
            }


            //echo $result;

        } else {
            //                echo "LBL_CAR_REQUEST_CANCELLED_TXT";
            $returnArr['Action'] = "2";
            $returnArr['Msg'] = "LBL_CAR_REQUEST_CANCELLED_TXT";
            echo json_encode($returnArr);
            exit;
        }

    } else {
        $returnArr['Action'] = "2";
        $returnArr['Msg'] = "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT";
        echo json_encode($returnArr);
    }

}

###########################################################################

if ($type == "RequestCancleTrip") {

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';

    if ($userType != "Driver") {

        $vTripStatus = get_value('register_user', 'vTripStatus', 'iUserId', $iUserId, '', 'true');

        if ($vTripStatus != "Cancelled" && $vTripStatus != "Active" && $vTripStatus != "Arrived") {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "DO_RESTART";
            echo json_encode($returnArr);
            exit;
        }
    }


    $active_status = "Canceled";
//        $message        = "TripCancelled";
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
    $message_arr['iUserId'] = $iUserId;
    if ($userType == "Driver") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "false";
    }

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    $where = " iTripId = '$iTripId'";
    $Data_update_trips['iStudentId'] = 0;
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

    if ($ENABLE_PUBNUB == "Yes") {

        $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);

        if ($userType != "Driver") {
            $channelName = "DRIVER_" . $iDriverId;
        } else {
            $channelName = "PASSENGER_" . $iUserId;
        }

        $info = $pubnub->publish($channelName, $message);

    }

//        $sql = "SELECT iGcmRegId,eDeviceType FROM register_driver WHERE iDriverId IN (".$iDriverId.")";
    if ($userType != "Driver") {
        $sql = "SELECT iGcmRegId,eDeviceType FROM register_driver WHERE iDriverId IN (" . $iDriverId . ")";
    } else {
        $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId IN (" . $iUserId . ")";
    }

    $result = $obj->MySQLSelect($sql);

    if (count($result) == 0) {
        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
        exit;
    }
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
        //            $message = stripslashes(preg_replace("/[\n\r]/","",$message));
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
        echo json_encode($returnArr);
    } else {

        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
    }

}

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
        $returnArr['Action'] = "1";
        if ($UserType == "Driver") {
            $returnArr['profileData'] = GetDriverDetail($Uid);
        } else {
            $returnArr['profileData'] = GetUserDetail($Uid);
        }
    } else {
        $returnArr['Action'] = "0";
    }
    echo json_encode($returnArr);
}

###########################################################################

###################### getAssignedDriverLocation ##########################
if ($type == "getAssignedDriverLocation") {
    $Did = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $latitude = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $longitude = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';

    $sql = "SELECT vLatitude, vLongitude, vTripStatus FROM `register_driver` WHERE iDriverId='$Did'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        #$returnArr['action'] = 0;
        $returnArr['DriverLatitude'] = $Data[0]['vLatitude'];
        $returnArr['DriverLongitude'] = $Data[0]['vLongitude'];
        $returnArr['vTripStatus'] = $Data[0]['vTripStatus'];

        $time_In_seconds = 0;


        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $Data[0]['vLatitude'] . "," . $Data[0]['vLongitude'] . "&destination=" . $latitude . "," . $longitude . "&key=" . $GoogleServerKey . "&sensor=true";

        try {

            $jsonfile = file_get_contents($url);
            $jsondata = json_decode($jsonfile);

            $time_value = $jsondata->routes[0]->legs[0]->duration->value;

            $time_In_seconds = $time_value;


        } catch (ErrorException $ex) {

            $returnArr['Action'] = "0";
            echo json_encode($returnArr);
            exit;
        }

        $returnArr['Action'] = "1";
        $returnArr['DriverArrivedTimeInMin'] = strval(round($time_In_seconds / 60));
        if (round($time_In_seconds / 60) < 3) {
            $returnArr['IsDriverArriving'] = "true";
        } else {
            $returnArr['IsDriverArriving'] = "false";
        }
        echo json_encode($returnArr);

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'Not Found';
        echo json_encode($returnArr);
    }

}


###########################################################################

if ($type == "requestStartTrip") {

    $iUserId = $_REQUEST["iUserId"];
    $iDriverId = $_REQUEST["iDriverId"];
    //        $TripID = $_REQUEST["iTripId"];

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $tripstartlabel = $languageLabelsArr['LBL_START_TRIP_DIALOG_TXT'];

    $message = "TripStarted";


    //Update passenger Table
    $where = " iUserId = '$iUserId'";

    $Data_update_passenger['vTripStatus'] = 'On Going Trip';

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    //Update Driver Table
    $where = " iDriverId = '$iDriverId'";

    $Data_update_driver['vTripStatus'] = 'On Going Trip';

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    $startDateOfTrip = @date("Y-m-d H:i:s");

    $sql = "SELECT iGcmRegId,eDeviceType,iTripId FROM register_user WHERE iUserId='$iUserId'";
    $result = $obj->MySQLSelect($sql);

    $Curr_TripID = $result[0]['iTripId'];

    $eType = get_value('trips', 'eType', 'iTripId', $Curr_TripID, '', 'true');
    $verificationCode = rand(10000000, 99999999);

    $message_arr = array();
    $message_arr['Message'] = $message;
    $message_arr['iDriverId'] = $iDriverId;
    if ($eType == "Deliver") {
        $message_arr['VerificationCode'] = strval($verificationCode);
    } else {
        $message_arr['VerificationCode'] = "";
    }

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    $where = " iTripId = '$Curr_TripID'";

    $Data_update_trips['iActive'] = 'On Going Trip';
    $Data_update_trips['tStartDate'] = $startDateOfTrip;

    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);


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


    if ($ENABLE_PUBNUB == "Yes") {

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
        // $alertMsg = "Your trip is started";
        $alertMsg = $tripstartlabel;
        array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);

        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
    }

    // Send SMS to receiver if trip type is delivery.

    if ($eType == "Deliver") {
        $receiverMobile = get_value('trips', 'vReceiverMobile', 'iTripId', $Curr_TripID, '', 'true');
        $receiverMobile1 = "+" . $receiverMobile;


        $where_trip_update = " iTripId = '$Curr_TripID'";
        $data_delivery['vDeliveryConfirmCode'] = $verificationCode;
        $obj->MySQLQueryPerform("trips", $data_delivery, 'update', $where);

        //$message_deliver = "SMS format goes here. Your verification code is ".$verificationCode." Please give this code to driver to end delivery process.";
        $message_deliver = deliverySmsToReceiver($Curr_TripID);

        $result = sendEmeSms($receiverMobile1, $message_deliver);
        if ($result == 0) {
            $isdCode = $generalobj->getConfigurations("configurations", "SITE_ISD_CODE");
            $receiverMobile = "+" . $isdCode . $receiverMobile;
            sendEmeSms($receiverMobile, $message_deliver);
        }

        $returnArr['message'] = $verificationCode;
        $returnArr['SITE_TYPE'] = SITE_TYPE;
    }

    $returnArr['Action'] = "1";
    $returnArr['SITE_TYPE'] = SITE_TYPE;
    echo json_encode($returnArr);

}

###########################################################################

if ($type == "DriverArrived") {
    $user_id_auto = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $message = "DriverArrived";

    $where = " iDriverId = '$iDriverId'";

    $Data_update_driver['vTripStatus'] = 'Arrived';

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id > 0) {
        $sql = "SELECT tr.tSecDestination,tr.secDestLatitude,
            tr.secDestLongitude,tr.secDestAddress,tr.tReturn,tr.tEndLat,tr.tEndLong,tr.tDaddress,tr.iUserId
            FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
        $result = $obj->MySQLSelect($sql);

        if ($result[0]['tEndLat'] != '' && $result[0]['tEndLong'] != '') {
            $returnArr['DLatitude'] = $result[0]['tEndLat'];
            $returnArr['DLongitude'] = $result[0]['tEndLong'];
            $returnArr['DAddress'] = $result[0]['tDaddress'];

            $returnArr['tSecDestination'] = $result[0]['tSecDestination'];
            $returnArr['secDestLatitude'] = $result[0]['secDestLatitude'];
            $returnArr['tReturn'] = $result[0]['tReturn'];
            $returnArr['secDestAddress'] = $result[0]['secDestAddress'];
            $returnArr['secDestLongitude'] = $result[0]['secDestLongitude'];
        } else {
            $returnArr['DLatitude'] = "";
            $returnArr['DLongitude'] = "";
            $returnArr['DAddress'] = "";

            $returnArr['tSecDestination'] = "";
            $returnArr['secDestLatitude'] = "";
            $returnArr['tReturn'] = "";
            $returnArr['secDestAddress'] = "";
            $returnArr['secDestLongitude'] = "";
        }

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
        if ($ENABLE_PUBNUB == "Yes") {

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

        $returnArr['Action'] = "1";
        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
    }
}

###########################################################################
if ($type == "ProcessEndTrip") {

    global $generalobj;

    $tripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $driverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $latitudes = isset($_REQUEST["latList"]) ? $_REQUEST["latList"] : '';
    $longitudes = isset($_REQUEST["lonList"]) ? $_REQUEST["lonList"] : '';
    $destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    $tReturn = isset($_REQUEST["tReturn"]) ? $_REQUEST["tReturn"] : 'false';
    $tSecDestination = isset($_REQUEST["tSecDestination"]) ? $_REQUEST["tSecDestination"] : 'false';
    $tSecDestLatitude = isset($_REQUEST["secDestLatitude"]) ? $_REQUEST["secDestLatitude"] : '';
    $tSecDestLongitude = isset($_REQUEST["secDestLongitude"]) ? $_REQUEST["secDestLongitude"] : '';
    $isTripCanceled = isset($_REQUEST["isTripCanceled"]) ? $_REQUEST["isTripCanceled"] : '';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    //$GoogleServerKey= isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $tripDistance = isset($_REQUEST["TripDistance"]) ? $_REQUEST["TripDistance"] : '0';
    $dAddress = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    $waitingTimeInTrip = isset($_REQUEST["waitingTimeInTrip"]) ? $_REQUEST["waitingTimeInTrip"] : '';

    // add by seyyed amir
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

    $sql = "SELECT tStartDate,iVehicleTypeId,tStartLat,tStartLong,fRatio_" . $vCurrencyDriver . " as fRatioDriver, vTripPaymentMode,fPickUpPrice,fNightPrice FROM trips WHERE iTripId='$tripId'";
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

    #TLOG("DIS: {$tripDistance}  TIME: {$totalTimeInMinutes_trip}");

    /////////////////////////////////////
    // add by seyyed amir for fixed fare
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
    //////////////

    #TLOG("DIS: {$tripDistance}  TIME: {$totalTimeInMinutes_trip}");

    ///////////////////////////
    // CALCULATE TRIP FARE
    $Fare_data = calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $userId, 1, $startDate, $endDateOfTrip, $couponCode, $tripId, $waitingTimeInTrip);

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

    $returnRate = 1;
    $returnDouble = 1;
    if ($tReturn == 'true') {

        $returnRate = 1.50;
        $returnDouble = 2;
    }

    $Data_update_trips['tDaddress'] = $dAddress;
    $Data_update_trips['iFare'] = ($returnRate * $Fare_data['total_fare']);
    $Data_update_trips['iActive'] = $Active;
    $Data_update_trips['fDistance'] = $tripDistance;
    $Data_update_trips['fWaitingTime'] = $waitingTimeInTrip;
    $Data_update_trips['fPricePerMin'] = $Fare_data['fPricePerMin'];
    $Data_update_trips['fPricePerKM'] = $Fare_data['fPricePerKM'];
    $Data_update_trips['iBaseFare'] = $Fare_data['iBaseFare'];
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
        $returnArr['TotalFare'] = round($returnRate * $Fare_data['total_fare'] * $trip_start_data_arr[0]['fRatioDriver'], 0);
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

// if($type == "ProcessEndTrip"){
//
//     global $generalobj;
//
//     $tripId     = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
// 		$userId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
// 		$driverId     = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
// 		$latitudes  = isset($_REQUEST["latList"]) ? $_REQUEST["latList"] : '';
// 		$longitudes = isset($_REQUEST["lonList"]) ? $_REQUEST["lonList"] : '';
// 		$destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
// 		$destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
// 		$isTripCanceled = isset($_REQUEST["isTripCanceled"]) ? $_REQUEST["isTripCanceled"] : '';
// 		$driverComment   = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
// 		$driverReason   = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
//     $GoogleServerKey= isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
// 		$waitingTimeInTrip   = isset($_REQUEST["waitingTimeInTrip"]) ? $_REQUEST["waitingTimeInTrip"] : '';
//     $tripDistance     = isset($_REQUEST["TripDistance"]) ? $_REQUEST["TripDistance"] : '0';
//     $dAddress        = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
//
//     $tReturn    =isset($_REQUEST["tReturn"]) ? $_REQUEST["tReturn"] : 'false';
// 		$tSecDestination    =isset($_REQUEST["tSecDestination"]) ? $_REQUEST["tSecDestination"] : 'false';
// 		$tSecDestLatitude    =isset($_REQUEST["secDestLatitude"]) ? $_REQUEST["secDestLatitude"] : '';
// 		$tSecDestLongitude    =isset($_REQUEST["secDestLongitude"]) ? $_REQUEST["secDestLongitude"] : '';
//
//             // add by seyyed amir
//             $vDriverEndLocation = $destination_lat . ',' . $destination_lon;
//
//             $vDriverSecEndLocation = "";
//             if ($tSecDestination == "true") {
//               $vDriverSecEndLocation = $tSecDestLatitude . ',' . $tSecDestLongitude;
//             }
//
//             if($waitingTimeInTrip == '')
//                 $waitingTimeInTrip = 0;
//             else
//             {
//                 $waitingTimeInTrip = round($waitingTimeInTrip,2);
//             }
//             ///////////////////
//
//
//     		$Active="Finished";
//     		$vLangCode=get_value('register_user', 'vLang', 'iUserId',$userId,'','true');
//     		if($vLangCode == "" || $vLangCode == NULL){
//     		   $vLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
//     		}
//
//     		$languageLabelsArr= getLanguageLabelsArr($vLangCode,"1");
//     		$tripcancelbydriver = $languageLabelsArr['LBL_TRIP_CANCEL_BY_DRIVER'];
//     		$tripfinish = $languageLabelsArr['LBL_TRIP_FINISH'];
//
//             if($isTripCanceled == "true"){
//     			$message      = "TripCancelledByDriver";
//     		}else{
//     			$message      = "TripEnd";
//     		}
//
//             $message_arr = array();
//             $message_arr['Message'] = $message;
//             $message_arr['iDriverId'] = $driverId;
//
//     		if($isTripCanceled == "true"){
//     			$message_arr['Reason'] = $driverReason;
//     			$message_arr['isTripStarted'] = "true";
//     		}
//
//             $message = json_encode($message_arr,JSON_UNESCAPED_UNICODE);
//
//     		$couponCode=get_value('trips', 'vCouponCode', 'iTripId',$tripId,'','true');
//     		$discountValue = 0;
//     		$discountValueType= "cash";
//     		if( $couponCode != ''){
//     			$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode,'','true');
//     			$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode,'','true');
//     		}
//
//
//     		if($latitudes != '' && $longitudes != ''){
//     			processTripsLocations($tripId,$latitudes,$longitudes);
//     		}
//
//     		$vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId,'','true');
//     		$currencySymbolDriver = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver,'','true');
//
//     		$sql = "SELECT tStartDate,iVehicleTypeId,tStartLat,tStartLong,fRatio_".$vCurrencyDriver." as fRatioDriver, vTripPaymentMode,fPickUpPrice,fNightPrice FROM trips WHERE iTripId='$tripId'";
//     		$trip_start_data_arr = $obj->MySQLSelect($sql);
//
//     		$tripDistance=calcluateTripDistance($tripId);
//
//     		$sourcePointLatitude	= $trip_start_data_arr[0]['tStartLat'];
//     		$sourcePointLongitude	= $trip_start_data_arr[0]['tStartLong'];
//     		$startDate				= $trip_start_data_arr[0]['tStartDate'];
//     		$vehicleTypeID			= $trip_start_data_arr[0]['iVehicleTypeId'];
//     		$eFareType				= $trip_start_data_arr[0]['eFareType'];
//     		//$vTripPaymentMode		= $trip_start_data_arr[0]['vTripPaymentMode'];
//
//     		$endDateOfTrip=@date("Y-m-d H:i:s");
//
//     		$totalTimeInMinutes_trip=@round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60,2);
//
//     		if($totalTimeInMinutes_trip <= 1){
//
//     			$FinalDistance= $tripDistance;
//
//     		}else{
//
//     			$FinalDistance=checkDistanceWithGoogleDirections($tripDistance,$sourcePointLatitude,$sourcePointLongitude,$destination_lat,$destination_lon);
//
//           if ($tSecDestination == 'true') {
//
//             $temp = $FinalDistance;
//             $FinalDistance=$temp+checkDistanceWithGoogleDirections(0,$destination_lat,$destination_lon,$tSecDestLatitude,$tSecDestLongitude);
//           }
//
//     		}
//
//     		$tripDistance=$FinalDistance;
//             #TLOG("DIS: {$tripDistance}  TIME: {$totalTimeInMinutes_trip}");
//
//             /////////////////////////////////////
//             // add by seyyed amir for fixed fare
//             $typeOfFare = $generalobj->getConfigurations("configurations","TYPE_OF_FARE_CALCULATION");
//             if($typeOfFare == "Fixed")
//             {
//                 $tripItem = get_value('trips', '*', 'iTripId',$tripId,'');
//                 //TLOG($tripItem);
//                 if(count($tripItem) > 0)
//                 {
//                     $tripItem = $tripItem[0];
//                     if($tripItem['fGDtime'] != "")
//                         $totalTimeInMinutes_trip = $tripItem['fGDtime'];
//                     if($tripItem['fGDdistance'] != "")
//                         $tripDistance = $tripItem['fGDdistance'];
//                 }
//             }
//             //////////////
//
//             #TLOG("DIS: {$tripDistance}  TIME: {$totalTimeInMinutes_trip}");
//
//             ///////////////////////////
//             // CALCULATE TRIP FARE
//     		$Fare_data = calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $userId, 1, $startDate, $endDateOfTrip, $couponCode, $tripId, $waitingTimeInTrip);
//
//             //if($waitingTimeInTrip > 0)
//             //    TLOG($Fare_data);
//
//             $where = " iTripId = '" . $tripId . "'";
//
//     		$Data_update_trips['tEndDate']=$endDateOfTrip;
//
//             // disable by seyyed amir
//     		//$Data_update_trips['tEndLat']=$destination_lat;
//     		//$Data_update_trips['tEndLong']=$destination_lon;
//
//             // add by seyyed amir
//             if ($tSecDestination == "true") {
//         			$Data_update_trips['vDriverEndLocation']=$vDriverSecEndLocation;
//         		}
//         		else {
//         			$Data_update_trips['vDriverEndLocation']=$vDriverEndLocation;
//         		}
//
//         $returnRate = 1;
//         $returnDouble = 1;
//         if ($tReturn == 'true') {
//
//           $returnRate = 1.50;
//           $returnDouble = 2;
//         }
//
//     		$Data_update_trips['tDaddress']=$dAddress;
//     		$Data_update_trips['iFare']=$returnRate*$Fare_data['total_fare'];
//     		$Data_update_trips['iActive']=$Active;
//     		$Data_update_trips['fDistance']=$returnDouble*$tripDistance;
//     		$Data_update_trips['fWaitingTime']=$waitingTimeInTrip;
//     		$Data_update_trips['fPricePerMin']=$Fare_data['fPricePerMin'];
//     		$Data_update_trips['fPricePerKM']=$Fare_data['fPricePerKM'];
//     		$Data_update_trips['iBaseFare']=$Fare_data['iBaseFare'];
//     		$Data_update_trips['fCommision']=$Fare_data['fCommision'];
//             $Data_update_trips['fDiscount']=$Fare_data['fDiscount'];
//        	    $Data_update_trips['vDiscount'] =$Fare_data['vDiscount'] ;
//             $Data_update_trips['fMinFareDiff']=$Fare_data['MinFareDiff'];
//             $Data_update_trips['fSurgePriceDiff']=$Fare_data['fSurgePriceDiff'];
//             $Data_update_trips['fWalletDebit']=$Fare_data['user_wallet_debit_amount'];
//             $Data_update_trips['fTripGenerateFare']=$Fare_data['fTripGenerateFare'];
//
//             $Data_update_trips['iPriceZoneRatio'] = $Fare_data['priceZoneRatio'];
//
//
//     		if($isTripCanceled == "true"){
//     			$Data_update_trips['vCancelReason'] = $driverReason;
//     			$Data_update_trips['vCancelComment'] = $driverComment;
//     			$Data_update_trips['eCancelled'] = "Yes";
//     		}
//
//     		$id = $obj->MySQLQueryPerform("trips",$Data_update_trips,'update',$where);
//
//     		$trip_status    = "Not Active";
//
//     		$where = " iUserId = '$userId'";
//     		$Data_update_passenger['iTripId']=$tripId;
//     		$Data_update_passenger['vTripStatus']=$trip_status;
//     		$Data_update_passenger['vCallFromDriver']='Not Assigned';
//
//     		$id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
//
//     		$where = " iDriverId = '$driverId'";
//     		$Data_update_driver['iTripId']=$tripId;
//     		$Data_update_driver['vTripStatus']=$trip_status;
//
//     		$id = $obj->MySQLQueryPerform("register_driver",$Data_update_driver,'update',$where);
//
//     		if($id>0){
//
//     			$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
//     			$PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
//     			$PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");
//
//     			/* For PubNub Setting */
//     			$tableName = "register_user";
//     			$iMemberId_VALUE = $userId;
//     			$iMemberId_KEY = "iUserId";
//     			$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
//     			$eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');
//     			/* For PubNub Setting Finished */
//
//     			if($ENABLE_PUBNUB == "Yes" /* && $iAppVersion > 1 && $eDeviceType == "Android" */){
//
//     				$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
//
//     				$channelName = "PASSENGER_".$userId;
//
//     				$info = $pubnub->publish($channelName, $message);
//
//     			}
//
//     			$sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$userId'";
//     			$result = $obj->MySQLSelect($sql);
//
//     			$deviceTokens_arr_ios = array();
//     			$registation_ids_new = array();
//
//     			if($result[0]['eDeviceType'] == "Android" && $ENABLE_PUBNUB != "Yes"){
//     				array_push($registation_ids_new, $result[0]['iGcmRegId']);
//     				$Rmessage  = array("message" => $message);
//     				$result = send_notification($registation_ids_new, $Rmessage);
//
//     			}else if($result[0]['eDeviceType'] != "Android"){
//     				// $alertMsg = "Your trip is finished";
//     				if($isTripCanceled == "true"){
//     					//$alertMsg = "Your trip is cancelled by driver.";
//               $alertMsg = $tripcancelbydriver;
//     				}else{
//     					//$alertMsg = "Your trip is finished";
//               $alertMsg = $tripfinish;
//     				}
//     				array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
//
//     				if($ENABLE_PUBNUB == "Yes"){
//     					$message  = "";
//     				}
//
//     				sendApplePushNotification(0,$deviceTokens_arr_ios,$message,$alertMsg,0);
//     			}
//
//
//     			$returnArr['Action'] = "1";
//     			$returnArr['iTripsLocationsID']=$id;
//     			// $returnArr['TotalFare']=round($Fare_data[0]['total_fare'] * $trip_start_data_arr[0]['fRatioDriver']);
//     			$returnArr['TotalFare']=round($returnRate * $Fare_data['total_fare'] * $trip_start_data_arr[0]['fRatioDriver'],0);
//     			// $returnArr['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$trip_start_data_arr[0]['vCurrencyDriver']."' ")[0]['vSymbol']);
//     			$returnArr['CurrencySymbol']=$currencySymbolDriver;
//     			$returnArr['tripStartTime']=$startDate;
//     			$returnArr['TripPaymentMode']=$trip_start_data_arr[0]['vTripPaymentMode'];
//     			$returnArr['Discount']=round($Fare_data['fDiscount'] * $trip_start_data_arr[0]['fRatioDriver'],0);
//     			$returnArr['Message']="Data Updated";
//     			$returnArr['FormattedTripDate']=jdate('jS F  g:i a',strtotime($startDate));
//
//     			// Code for Check last logout date is update in driver_log_report
//                 $query = "SELECT * FROM driver_log_report WHERE iDriverId = '".$driverId."' ORDER BY iDriverLogId DESC LIMIT 0,1";
//                 $db_driver = $obj->MySQLSelect($query);
//                 if(count($db_driver) > 0) {
//                    $driver_lastonline = @date("Y-m-d H:i:s");
//                    $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='".$driver_lastonline."' WHERE iDriverLogId = ".$db_driver[0]['iDriverLogId'];
//           		     $obj->sql_query($updateQuery);
//                 }
//                 // Code for Check last logout date is update in driver_log_report Ends
//
//     		}else{
//     			$returnArr['Action'] = "0";
//     			$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
//     		}
//
//     		// added by seyyed amir
//     		// Referrals
//     		@SavarProcessReferrals($tripId);
//
//     		#Logger($returnArr);
//     		echo json_encode($returnArr);

//         // add by seyyed amir
//         $vDriverEndLocation = $destination_lat . ',' . $destination_lon;
//
//         if($waitingTimeInTrip == '')
//             $waitingTimeInTrip = 0;
//         else
//         {
//             $waitingTimeInTrip = round($waitingTimeInTrip,2);
//         }
//         ///////////////////
//
//         $Active="Finished";
//         $vLangCode=get_value('register_user', 'vLang', 'iUserId',$userId,'','true');
//         if($vLangCode == "" || $vLangCode == NULL){
//            $vLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');
//         }
//
//         $languageLabelsArr= getLanguageLabelsArr($vLangCode,"1");
//         $tripcancelbydriver = $languageLabelsArr['LBL_TRIP_CANCEL_BY_DRIVER'];
//         $tripfinish = $languageLabelsArr['LBL_TRIP_FINISH'];
//
// //        $message      = "TripEnd";
//         if($isTripCanceled == "true"){
//             $message      = "TripCancelledByDriver";
//         }else{
//             $message      = "TripEnd";
//         }
//
//         $message_arr = array();
//         $message_arr['Message'] = $message;
//         $message_arr['iDriverId'] = $driverId;
//
//         if($isTripCanceled == "true"){
//             $message_arr['Reason'] = $driverReason;
//             $message_arr['isTripStarted'] = "true";
//         }
//
//         $message = json_encode($message_arr,JSON_UNESCAPED_UNICODE);
//
//         $couponCode=get_value('trips', 'vCouponCode', 'iTripId',$tripId,'','true');
//         $discountValue = 0;
//         $discountValueType= "cash";
//         if( $couponCode != ''){
// 			$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode,'','true');
// 			$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode,'','true');
// 		}
//
// 		if($latitudes != '' && $longitudes != ''){
// 			processTripsLocations($tripId,$latitudes,$longitudes);
// 		}
//
//         $dAddress  = getAddressFromLocation($destination_lat,$destination_lon,$GoogleServerKey);
//
// 		$vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId,'','true');
// 		$currencySymbolDriver = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver,'','true');
//
//         $sql = "SELECT tStartDate,iVehicleTypeId,tStartLat,tStartLong,iVerificationCode,fRatio_".$vCurrencyDriver." as fRatioDriver,vCurrencyDriver, vTripPaymentMode,fPickUpPrice,fNightPrice FROM trips WHERE iTripId='$tripId'";
//         $trip_start_data_arr = $obj->MySQLSelect($sql);
//
//         $tripDistance=calcluateTripDistance($tripId);
//
//         $sourcePointLatitude=$trip_start_data_arr[0]['tStartLat'];
//         $sourcePointLongitude=$trip_start_data_arr[0]['tStartLong'];
//         $FinalDistance=checkDistanceWithGoogleDirections($tripDistance,$sourcePointLatitude,$sourcePointLongitude,$destination_lat,$destination_lon);
//
//         $tripDistance=$FinalDistance;
//
//         $startDate=$trip_start_data_arr[0]['tStartDate'];
//         $vehicleTypeID=$trip_start_data_arr[0]['iVehicleTypeId'];
//
//         $endDateOfTrip=@date("Y-m-d H:i:s");
//
//         $totalTimeInMinutes_trip=@round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60,2);
//
// 		    $Fare_data=calculateFare($totalTimeInMinutes_trip,$tripDistance,$vehicleTypeID,$userId,1,$startDate,$endDateOfTrip,$couponCode,$tripId);
//         $where = " iTripId = '" . $tripId . "'";
//
//         $Data_update_trips['tEndDate']=$endDateOfTrip;
//         $Data_update_trips['tEndLat']=$destination_lat;
//         $Data_update_trips['tEndLong']=$destination_lon;
//         $Data_update_trips['tDaddress']=$dAddress;
//         $Data_update_trips['iFare']=$Fare_data['total_fare'];
//         $Data_update_trips['iActive']=$Active;
//         $Data_update_trips['fDistance']=$tripDistance;
//         $Data_update_trips['fPricePerMin']=$Fare_data['fPricePerMin'];
//         $Data_update_trips['fPricePerKM']=$Fare_data['fPricePerKM'];
//         $Data_update_trips['iBaseFare']=$Fare_data['iBaseFare'];
//         $Data_update_trips['fCommision']=$Fare_data['fCommision'];
//         $Data_update_trips['fDiscount']=$Fare_data['fDiscount'];
//    			$Data_update_trips['vDiscount'] =$Fare_data['vDiscount'] ;
//         $Data_update_trips['fMinFareDiff']=$Fare_data['MinFareDiff'];
//         $Data_update_trips['fSurgePriceDiff']=$Fare_data['fSurgePriceDiff'];
//         $Data_update_trips['fWalletDebit']=$Fare_data['user_wallet_debit_amount'];
//         $Data_update_trips['fTripGenerateFare']=$Fare_data['fTripGenerateFare'];
//         if($isTripCanceled == "true"){
//             $Data_update_trips['vCancelReason'] = $driverReason;
//             $Data_update_trips['vCancelComment'] = $driverComment;
//             $Data_update_trips['eCancelled'] = "Yes";
//         }
//
//         $id = $obj->MySQLQueryPerform("trips",$Data_update_trips,'update',$where);
//
//         $trip_status    = "Not Active";
//
//         $where = " iUserId = '$userId'";
//         $Data_update_passenger['iTripId']=$tripId;
//         $Data_update_passenger['vTripStatus']=$trip_status;
//         $Data_update_passenger['vCallFromDriver']='Not Assigned';
//
//         $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
//
//         $where = " iDriverId = '$driverId'";
//         $Data_update_driver['iTripId']=$tripId;
//         $Data_update_driver['vTripStatus']=$trip_status;
//
//         $id = $obj->MySQLQueryPerform("register_driver",$Data_update_driver,'update',$where);
//
//
// 		$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
// 		$PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
// 		$PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");
//
// 		/* For PubNub Setting */
// 		$tableName = "register_user";
// 		$iMemberId_VALUE = $userId;
// 		$iMemberId_KEY = "iUserId";
// 		$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
// 		$eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');
// 		/* For PubNub Setting Finished */
//
// 		if($ENABLE_PUBNUB == "Yes"){
//
// 			$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
//
// 			$channelName = "PASSENGER_".$userId;
//
// 			$info = $pubnub->publish($channelName, $message);
//
// 		}
//
//         $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$userId'";
//         $result = $obj->MySQLSelect($sql);
//
//         $deviceTokens_arr_ios = array();
//         $registation_ids_new = array();
//
//         if($result[0]['eDeviceType'] == "Android" && $ENABLE_PUBNUB != "Yes"){
//             array_push($registation_ids_new, $result[0]['iGcmRegId']);
//             $Rmessage         = array("message" => $message);
//             $result = send_notification($registation_ids_new, $Rmessage,0);
//
//         }else if($result[0]['eDeviceType'] != "Android"){
//
//             array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
//             if($isTripCanceled == "true"){
//                 //$alertMsg = "Your trip is cancelled by driver.";
//                 $alertMsg = $tripcancelbydriver;
//             }else{
//                 //$alertMsg = "Your trip is finished";
//                 $alertMsg = $tripfinish;
//             }
//
// 			if($ENABLE_PUBNUB == "Yes"){
// 				$message  = "";
// 			}
//
//             sendApplePushNotification(0,$deviceTokens_arr_ios,$message,$alertMsg,0);
//         }
//
//         if($id>0){
//
// 			$totalTime=0;
// 			 $hours= dateDifference($startDate,$endDateOfTrip,'%h');
// 			 $minutes= dateDifference($startDate,$endDateOfTrip,'%i');
// 			 $seconds= dateDifference($startDate,$endDateOfTrip,'%s');
//
// 			 if($hours>0){
// 				 $totalTime = $hours*60;
// 			}if($minutes>0){
// 				 $totalTime = $totalTime+$minutes;
// 			}
// 			$totalTime = $totalTime.".".$seconds;
//
//             $returnArr['Action'] = "1";
//             $returnArr['TotalFare']=strval(round($Fare_data['total_fare'] * $trip_start_data_arr[0]['fRatioDriver'],1));
//             $returnArr['TripFareOfMinute']=strval(round($Fare_data['fPricePerMin'] * $trip_start_data_arr[0]['fRatioDriver'],1));
//             $returnArr['TripTime']=strval($totalTime);
//             $returnArr['TripDistance']=strval($tripDistance);
//             $returnArr['TripFareOfDistance']=strval(round($Fare_data['fPricePerKM'] * $trip_start_data_arr[0]['fRatioDriver'],1));
//             $returnArr['BaseFare']=strval(round($Fare_data['iBaseFare'] * $trip_start_data_arr[0]['fRatioDriver'],1));
//             $returnArr['CurrencySymbol']=$currencySymbolDriver;
//             $returnArr['tripStartTime']=$startDate;
//             $returnArr['TripPaymentMode']=$trip_start_data_arr[0]['vTripPaymentMode'];
//             $returnArr['Discount']=strval(round($Fare_data['fDiscount'] * $trip_start_data_arr[0]['fRatioDriver'],1));
//             $returnArr['TripVerificationCode']=$trip_start_data_arr[0]['iVerificationCode'];
//             $returnArr['FormattedTripDate']=date('dS M \a\t h:i a',strtotime($startDate));
// 			$returnArr['UserDebitAmount'] = strval($Fare_data['user_wallet_debit_amount']);
// 			$returnArr['fWalletDebit'] = strval(round($Fare_data['user_wallet_debit_amount'] * $trip_start_data_arr[0]['fRatioDriver'],1));
// 			$returnArr['fSurgePriceDiff'] = strval(round($Fare_data['fSurgePriceDiff'] * $trip_start_data_arr[0]['fRatioDriver'],1));
// 			$returnArr['SurgePriceFactor'] = strval($Fare_data['SurgePriceFactor']);
//             if($Fare_data['MinFareDiff'] > 0){
// 				$returnArr['fMinFareDiff']=strval(round($Fare_data['MinFareDiff'] * $trip_start_data_arr[0]['fRatioDriver'],1));
// 			}else{
// 				$returnArr['fMinFareDiff']="0";
// 			}
//
// 			// Code for Check last logout date is update in driver_log_report
//             $query = "SELECT * FROM driver_log_report WHERE iDriverId = '".$driverId."' ORDER BY iDriverLogId DESC LIMIT 0,1";
//             $db_driver = $obj->MySQLSelect($query);
//             if(count($db_driver) > 0) {
//                $driver_lastonline = @date("Y-m-d H:i:s");
//                $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='".$driver_lastonline."' WHERE iDriverLogId = ".$db_driver[0]['iDriverLogId'];
//       		     $obj->sql_query($updateQuery);
//             }
//             // Code for Check last logout date is update in driver_log_report Ends
//
//             echo json_encode($returnArr);
// 			}else{
//             $returnArr['Action'] = "0";
//             echo json_encode($returnArr);
//
// 		}

//}

###########################################################################

if ($type == "updateTripLocations") {

    $tripId = isset($_POST["iTripId"]) ? $_POST["iTripId"] : '';
    $latitudes = isset($_POST["latList"]) ? $_POST["latList"] : '';
    $longitudes = isset($_POST["lonList"]) ? $_POST["lonList"] : '';

    if ($tripId != '' && $latitudes != '' && $longitudes != '') {
        $latitudes = preg_replace("/[^0-9,.-]/", "", $latitudes);
        $longitudes = preg_replace("/[^0-9,.-]/", "", $longitudes);
        processTripsLocations($tripId, $latitudes, $longitudes);
    }
    $returnArr['Action'] = "1";
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

    echo json_encode($returnArr);

}

/*  if($type=='displayFareToPassenger'){
		global $currency_supported_paypal;
        $iUserId          = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

		$vCurrencyCode=get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId,'','true');
		$vLang=get_value('register_user', 'vLang', 'iUserId', $iUserId,'','true');
		if($vLang == "" || $vLanguage == NULL){
			$vLang = "EN";
		}
		$currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyCode,'','true');

		$sql="SELECT ru.iTripId,tr.iFare,tr.iDriverId,tr.tEndLat,tr.tEndLong,tr.tSaddress,tr.tDaddress,tr.tStartDate,tr.iVerificationCode,tr.vTripPaymentMode, rd.vImage as vDriverImage,tr.tStartDate,tr.tEndDate,tr.fPricePerKM,tr.iBaseFare,tr.fPricePerMin,tr.fCommision,tr.fDistance ,tr.fRatio_".$vCurrencyCode." as fareRatio, tr.eCancelled, tr.vCancelReason, tr.vCancelComment, tr.vCouponCode, tr.fDiscount,tr.iVehicleTypeId, tr.vDiscount, tr.fMinFareDiff, tr.eType, tr.fWalletDebit, tr.fSurgePriceDiff,vt.vLogo as carImageLogo,vt.vVehicleType_".$vLang." as carTypeName FROM `trips` tr,`register_user` ru,`register_driver` rd,`vehicle_type` as vt WHERE ru.iUserId = '".$iUserId."' AND tr.iTripId = ru.iTripId AND rd.iDriverId = tr.iDriverId AND vt.iVehicleTypeId=tr.iVehicleTypeId";

        $result_fare = $obj->MySQLSelect($sql);

        if(count($result_fare) > 0){
            $result_fare[0]['Action'] = "1";
            $result_fare[0]['FormattedTripDate']=date('dS M \a\t h:i a',strtotime($result_fare[0]['tStartDate']));
            $result_fare[0]['PayPalConfiguration']=$generalobj->getConfigurations("configurations","PAYMENT_ENABLED");
            $result_fare[0]['DefaultCurrencyCode']=$generalobj->getConfigurations("configurations","DEFAULT_CURRENCY_CODE");

            $result_fare[0]['TotalFare']= strval(round($result_fare[0]['iFare'] * $result_fare[0]['fareRatio'],1));
            $result_fare[0]['fDiscount']= strval(round($result_fare[0]['fDiscount'] * $result_fare[0]['fareRatio'],1));
            $result_fare[0]['CurrencySymbol']=$currencySymbol;

            $result_fare[0]['PaypalFare']=strval($result_fare[0]['TotalFare']);
            $result_fare[0]['PaypalCurrencyCode']=$vCurrencyCode;

            if($result_fare[0]['fMinFareDiff'] > 0){
				$result_fare[0]['fMinFareDiff']=strval(number_format(round($result_fare[0]['fMinFareDiff'] * $result_fare[0]['fareRatio'],1),2));
			}else{
				$result_fare[0]['fMinFareDiff']="0";
			}

            if(!in_array(strtoupper($vCurrencyCode),$currency_supported_paypal))
            {

                // $defaultCurrency=($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
                $defaultCurrency=get_value('currency', 'vName', 'eDefault', 'Yes','','true');

                $priceRatio_defaultCurrency=$generalobj->getConfigurations("configurations","CURRENCY_VAL_PAYPAL");
                // $result_fare[0]['PaypalFare']=strval(round($result_fare[0]['iFare'] * $priceRatio_defaultCurrency,1));
				$result_fare[0]['PaypalFare']=strval(round($result_fare[0]['iFare'] * $priceRatio_defaultCurrency,1));
                $result_fare[0]['PaypalCurrencyCode']=$generalobj->getConfigurations("configurations","CURRENCY_CODE_PAYPAL");
			}

            echo json_encode($result_fare[0]);
        }else{
            $returnArr['Action'] = "0";
            echo json_encode($returnArr);
		}

	} */

###########################################################################

if ($type == 'CheckVerificationCode') {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    $sql = "SELECT eVerified FROM trips as tr,register_user as ru WHERE tr.iTripId=ru.iTripId AND ru.iUserId = '" . $iUserId . "'";

    $result_eVerified = $obj->MySQLSelect($sql);

    if ($result_eVerified[0]['eVerified'] == "Verified") {
        $returnArr['Action'] = "1";
        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        echo json_encode($returnArr);
    }

}


###########################################################################

if ($type == "submitRating") {

    $iGeneralUserId = isset($_REQUEST["iGeneralUserId"]) ? $_REQUEST["iGeneralUserId"] : ''; // for both driver or passenger
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating = isset($_REQUEST["rating"]) ? $_REQUEST["rating"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $tripVerificationCode = isset($_REQUEST["verification_code"]) ? $_REQUEST["verification_code"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : ''; // Passenger or Driver


    $ENABLE_TIP_MODULE = $generalobj->getConfigurations("configurations", "ENABLE_TIP_MODULE");

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' and eUserType = '$userType'";
    $row_check = $obj->MySQLSelect($sql);


    if (count($row_check) > 0) {
        //$returnArr['Action'] = "2"; //LBL_RATING_EXIST
        $returnArr['Action'] = "1";
        echo json_encode($returnArr);
    } else {

        if ($userType == "Passenger") {
            $iDriverId = get_value('trips', 'iDriverId', 'iTripId', $tripID, '', 'true');
            $tableName = "register_driver";
            $where = "iDriverId='" . $iDriverId . "'";
            $iMemberId = $iDriverId;
            // $tableName = "register_user";
            // $where = "iUserId='".$iGeneralUserId."'";
        } else {
            // $tableName = "register_driver";
            // $where = "iDriverId='".$iGeneralUserId."'";

            $sql = "SELECT iVerificationCode FROM `trips`  WHERE  iTripId='$tripID'";
            $trip_verification_code = $obj->MySQLSelect($sql);

            $verificationCode = $trip_verification_code[0]['iVerificationCode'];
            $where = " iTripId = '$tripID'";

            $Data_update_trips['eVerified'] = "Verified";

            $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);

            $iUserId = get_value('trips', 'iUserId', 'iTripId', $tripID, '', 'true');
            $tableName = "register_user";
            $where = "iUserId='" . $iUserId . "'";
            $iMemberId = $iUserId;
        }

        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $userType;

        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

        /*$sql = "SELECT vAvgRating FROM ".$tableName.' WHERE '.$where;
            $fetchAvgRating= $obj->MySQLSelect($sql);

      			if($fetchAvgRating[0]['vAvgRating'] > 0){
      				$average_rating = round(($fetchAvgRating[0]['vAvgRating'] + $rating) / 2,1);
      			}else{
      				$average_rating = round($fetchAvgRating[0]['vAvgRating'] + $rating,1);
      			} */

        //$Data_update['vAvgRating']=$average_rating;
        $Data_update['vAvgRating'] = getUserRatingAverage($iMemberId, $userType);

        $id = $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $tripID, '', 'true');
            if ($vTripPaymentMode == "Card") {
                $returnArr['ENABLE_TIP_MODULE'] = $ENABLE_TIP_MODULE;
            } else {
                $returnArr['ENABLE_TIP_MODULE'] = "No";
            }

            echo json_encode($returnArr);
        } else {
            $returnArr['Action'] = "0";
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

if ($type == 'estimateFare') {

    $sourceLocation = isset($_REQUEST["SourceLocation"]) ? $_REQUEST["SourceLocation"] : '';
    $destinationLocation = isset($_REQUEST["DestinationLocation"]) ? $_REQUEST["DestinationLocation"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';

    $secDestination = isset($_REQUEST["SecDestination"]) ? $_REQUEST["SecDestination"] : 'false';
    $secDestinationLocation = isset($_REQUEST["SecDestinationLocation"]) ? $_REQUEST["SecDestinationLocation"] : '';
    $tReturn = isset($_REQUEST["return"]) ? $_REQUEST["return"] : 'false';

    $sourceLocationArr = explode(",", $sourceLocation);
    $destinationLocationArr = explode(",", $destinationLocation);
    $secDestinationLocationArr = explode(",", $secDestinationLocation);

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');

    $resultArr = checkDistanceWithGoogleDirections(0, $sourceLocationArr[0], $sourceLocationArr[1], $destinationLocationArr[0], $destinationLocationArr[1], "1", $vGMapLangCode);
    $resultArr1 = [];

    $distance = $resultArr['Distance'];
    $time = $resultArr['Time'];

    if ($secDestination == "true") {

        $resultArr1 = checkDistanceWithGoogleDirections(0, $destinationLocationArr[0], $destinationLocationArr[1], $secDestinationLocationArr[0], $secDestinationLocationArr[1], "1", $vGMapLangCode);
        $distance += $resultArr1['Distance'];
        $time += $resultArr1['Time'];
    }

    $returnPriceRate = 1;
    $returnDouble = 1;
    if ($tReturn == "true") {
        $returnPriceRate = 1.5;
        $returnDouble = 1;
    }

    $temp = $returnDouble * $resultArr['Distance'];
    $resultArr['Distance'] = $temp;

    $temp = $returnDouble * $resultArr['Time'];
    $resultArr['Time'] = $temp;

    $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    // $priceRatio=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$vCurrencyPassenger."' ")[0]['Ratio']);
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');

    $Fare_data = calculateFareEstimate($time, $distance, $SelectedCar, $iUserId, 1);
// echo $Fare_data[0]['total_fare'] * $priceRatio * $returnPriceRate;exit;

    $distance = $returnDouble * $distance;
    $time = $returnDouble * $time;

    $Fare_data[0]['Action'] = "1";
    $Fare_data[0]['Distance'] = strval(round($distance, 2));//$resultArr['Distance'] ==NULL ? "0" : strval(round($resultArr['Distance'],2));
    $Fare_data[0]['Time'] = strval(round($time, 2));//$resultArr['Time'] == NULL ? "0" : strval(round($resultArr['Time'],2));
    $Fare_data[0]['SAddress'] = $resultArr['SAddress'] == NULL ? "--" : $resultArr['SAddress'];
    $Fare_data[0]['DAddress'] = $resultArr['DAddress'] == NULL ? "--" : $resultArr['DAddress'];
    $Fare_data[0]['steps'] = $resultArr['steps'] == NULL ? array() : $resultArr['steps'];
    $Fare_data[0]['total_fare'] = strval(round($Fare_data[0]['total_fare'] * $priceRatio * $returnPriceRate, 1));
    $Fare_data[0]['iBaseFare'] = strval(round($Fare_data[0]['iBaseFare'] * $priceRatio, 1));
    $Fare_data[0]['fPricePerMin'] = strval(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1));
    $Fare_data[0]['fPricePerKM'] = strval(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1));
    $Fare_data[0]['fCommision'] = strval(round($Fare_data[0]['fCommision'] * $priceRatio, 1));
    if ($Fare_data[0]['MinFareDiff'] > 0) {
        $Fare_data[0]['MinFareDiff'] = strval(number_format(round($Fare_data[0]['MinFareDiff'] * $priceRatio, 1), 2));
    } else {
        $Fare_data[0]['MinFareDiff'] = "0";
    }
    $Fare_data[0]['MinFareDiff'] = "0";
    echo json_encode($Fare_data[0]);
}

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

    if ($languageCode == "" || $languageCode == NULL) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    //        $meta = $generalobj->getStaticPage($iPageId);
    $pageDesc = get_value('pages', 'tPageDesc_' . $languageCode, 'iPageId', $iPageId, '', 'true');
    $meta['page_desc'] = strip_tags($pageDesc);
    echo json_encode($meta);
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
        //Logger($data);
        if ($eForFirstTrip == 'Yes') {
            $query = "SELECT count(*) as count FROM `trips` WHERE `iUserId` = $iUserId";
            $res = $obj->MySQLSelect($query);

            if ($res[0]['count'] > 0)
                $returnArr['Action'] = "0";
        }

        if ($eOnePerUser == 'Yes') {
            $query = "SELECT count(*) as count FROM `trips` WHERE `iUserId` = $iUserId  AND vCouponCode = '$promoCode'";
            $res = $obj->MySQLSelect($query);

            if ($res[0]['count'] > 0)
                $returnArr['Action'] = "0";
        }

        ////////////////////////////////////////

    } else {
        $returnArr['Action'] = "0";// code is invalid
        //$returnArr['Action']="01";// code is used by this user
    }
    echo json_encode($returnArr);
}
###########################################################################

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
    if ($languageCode == "" || $languageCode == NULL) {
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

            $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer FROM `faqs` WHERE iFaqcategoryId='" . $iUniqueId . "'";
            $row_questions = $obj->MySQLSelect($sql);

            $j = 0;
            while (count($row_questions) > $j) {
                $rows_questions[$j] = $row_questions[$j];
                $j++;
            }
            $row[$i]['Questions'] = $rows_questions;
            $i++;
        }
    }
    $returnData['Action'] = "1";
    $returnData['getFAQ'] = $row;
    echo json_encode($returnData);
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
    $id = $generalobj->send_email_user("CONTACTUS", $Data);


    if ($id > 0) {
        $returnData['Action'] = "1";
        echo json_encode($returnData);
    } else {
        $returnData['Action'] = "0";
        echo json_encode($returnData);
    }

}

####################### for email receipt ##########################
if ($type == 'getReceipt') {
    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';

    $sendId = sendTripReceipt($iTripId);

    if ($sendId == true || $sendId == "true" || $sendId == "1") {

        $returnArr['Action'] = "1";
    } else {

        $returnArr['Action'] = "0";
    }
    echo json_encode($returnArr);
    exit;

}


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
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
    }
    echo json_encode($returnArr);
    exit;
}

###########################################################################

if ($type == "getCurrencyList") {
    // $returnArr['List']=($obj->MySQLSelect("SELECT * FROM currency"));
    $returnArr['List'] = get_value('currency', '*', 'eStatus', 'Active');
    echo json_encode($returnArr);
}

###########################################################################

if ($type == "checkBookings") {
    global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride';

    $per_page = 10;

    if ($UserType == "Driver") {
        $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Assign' AND iDriverId='" . $iDriverId . "' AND eType='" . $eType . "'";
    } else {
        $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE  iUserId='$iUserId' AND eStatus != 'Completed' AND eType='" . $eType . "'";
    }

    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    if ($UserType == "Driver") {
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND cb.eStatus = 'Assign' AND cb.iDriverId='$iDriverId' AND cb.eType='" . $eType . "' ORDER BY cb.iCabBookingId DESC" . $limit;
    } else {
        // $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iUserId='$iUserId' AND (cb.eStatus = 'Assign' OR cb.eStatus = 'Pending') ORDER BY cb.iCabBookingId DESC" . $limit;
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iUserId='$iUserId' AND cb.eStatus != 'Completed' AND cb.eType='" . $eType . "' ORDER BY cb.iCabBookingId DESC" . $limit;
    }

    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);

    if (count($Data) > 0) {

        for ($i = 0; $i < count($Data); $i++) {
            $Data[$i]['dBooking_date'] = jdate('Y-m-d \س\ا\ع\ت g:i a', strtotime($Data[$i]['dBooking_date']));
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;

        if ($TotalPages > $page) {
            $returnArr['NextPage'] = strval($page + 1);
        } else {
            $returnArr['NextPage'] = "0";
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_BOOKINGS_AVAIL";
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
/* if($type=="cancelPassengerBooking"){
        $iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
        $iCabBookingId     = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
        $Reason     = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';

        $where = " iCabBookingId = '$iCabBookingId'";
        $data_update_booking['eStatus']="Cancel";
        $data_update_booking['vCancelReason']=$Reason;
        $id = $obj->MySQLQueryPerform("cab_booking",$data_update_booking,'update',$where);

        if($id){
            $returnArr['Action']="1";
        }else{
            $returnArr['Action']="0";
        }

        echo json_encode($returnArr);
    } */

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
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $vCouponCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    // $paymentMode =  isset($_REQUEST["paymentMode"]) ? $_REQUEST["paymentMode"] : ''; // Cash OR Card

    // $paymentMode = $eType == "Deliver" ?"Card":"Cash";

    if ($cashPayment == 'true') {
        $paymentMode = "Cash";
    } else {
        $paymentMode = "Card";
    }

    checkmemberemailphoneverification($iUserId, "Passenger");

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
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $Data['vBookingNo'] = rand(10000000, 99999999);
    $Data['dBooking_date'] = date('Y-m-d H:i', strtotime($scheduleDate));
    $Data['eCancelBy'] = "";
    $Data['eType'] = $eType;
    $Data['vCouponCode'] = $vCouponCode;
    if ($eType == "Deliver") {
        $Data['iPackageTypeId'] = $iPackageTypeId;
        $Data['vReceiverName'] = $vReceiverName;
        $Data['vReceiverMobile'] = $vReceiverMobile;
        $Data['tPickUpIns'] = $tPickUpIns;
        $Data['tDeliveryIns'] = $tDeliveryIns;
        $Data['tPackageDetails'] = $tPackageDetails;
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

/*if($type=="CollectPayment"){
		$iTripId     = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
		$isCollectCash     = isset($_REQUEST["isCollectCash"]) ? $_REQUEST["isCollectCash"] : '';

		$sql = "SELECT vTripPaymentMode,iUserId,iFare,vRideNo FROM trips WHERE iTripId='$iTripId'";
		$tripData = $obj->MySQLSelect($sql);

		 $vTripPaymentMode = $tripData[0]['vTripPaymentMode'];


		if($vTripPaymentMode == "Card" && $isCollectCash == ""){
		// echo "here";exit;
			$iUserId = $tripData[0]['iUserId'];
			$iFare = $tripData[0]['iFare'];
			$vRideNo = $tripData[0]['vRideNo'];

			$vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId,'','true');

			$price_new = $iFare * 100;
			$currency = get_value('currency', 'vName', 'eDefault', 'Yes','','true');

			//$description = "Payment received for trip number:".$vRideNo;
      $vLangCode = get_value('language_master', 'vCode', 'eDefault','Yes','','true');

      $languageLabelsArr= getLanguageLabelsArr($vLangCode,"1");
      $description = $languageLabelsArr['LBL_TRIP_PAYMENT_RECEIVED']." ".$vRideNo;
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
    			  //echo "<pre>";print_r($result);exit;
        }

        if($iFare == 0 || ($result['status']=="succeeded" && $result['paid']=="1")){
					$pay_data['tPaymentUserID']=$result['id'];
					$pay_data['vPaymentUserStatus']="approved";
					$pay_data['iTripId']=$iTripId;
					$pay_data['iAmountUser']=$iFare;

					$id = $obj->MySQLQueryPerform("payments",$pay_data,'insert');

				}else{
          //echo "<pre>";print_r($e);exit;
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
	}*/

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
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }

    /*
		$userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId,'','true');
		$currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes','','true');
		$currencyratio = get_value('currency', 'Ratio', 'vName', $currencyCode,'','true');
		$price = $fAmount * $currencyratio;
		$price_new = $price;
		$price_new = round($price_new);
		*/
    //$fAmount = round($fAmount);


    try {
        require_once(TPATH_CLASS . 'savar/class.factor.php');
        $token = SavarFactor::Create($iMemberId, $fAmount);

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
// 		$iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
// 		$eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
//     $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
// 		if($eMemberType == "Passenger"){
//       $tbl_name = "register_user";
//       $currencycode = "vCurrencyPassenger";
//       $iUserId = "iUserId";
//       $eUserType = "Rider";
//     }else{
//       $tbl_name = "register_driver";
//       $currencycode = "vCurrencyDriver";
//       $iUserId = "iDriverId";
//       $eUserType = "Driver";
//     }
//     $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId,'','true');
//     $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId,'','true');
//     $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId,'','true');
//     $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes','','true');
//     $currencyratio = get_value('currency', 'Ratio', 'vName', $currencyCode,'','true');
//     $price = $fAmount*$currencyratio;
//     $price_new = $price * 100;
//     $price_new = round($price_new);
//     if($vStripeCusId == "" || $vStripeToken == ""){
//       $returnArr["Action"] = "0";
//       $returnArr['message']="LBL_NO_CARD_AVAIL_NOTE";
//       echo json_encode($returnArr);exit;
//     }

//     $dDate = Date('Y-m-d H:i:s');
//   	$eFor = 'Deposit';
//   	$eType = 'Credit';
//   	$iTripId = 0;
//   	$tDescription = "Amount credited";
//   	$ePaymentStatus = 'Unsettelled';

//   	try{
// 			$charge_create = Stripe_Charge::create(array(
// 			  "amount" => $price_new,
// 			  "currency" => $currencyCode,
// 			  "customer" => $vStripeCusId,
// 			  "description" =>  $tDescription
// 			));

//       $details = json_decode($charge_create);
// 			$result = get_object_vars($details);
//       //echo "<pre>";print_r($result);exit;
//         if($result['status']=="succeeded" && $result['paid']=="1"){
// 					$generalobj->InsertIntoUserWallet($iMemberId,$eUserType,$price,'Credit',0,$eFor,$tDescription,$ePaymentStatus,$dDate);
//           $user_available_balance = $generalobj->get_user_available_balance($iMemberId,$eUserType);
//           $returnArr["Action"] = "1";
//           $returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$userCurrencyCode));
//           $returnArr['message']= "LBL_WALLET_MONEY_CREDITED";
//           echo json_encode($returnArr);exit;
// 				}else{
// 					$returnArr['Action'] = "0";
// 					$returnArr['message']="LBL_WALLET_MONEY_CREDITED_FAILED";

// 					echo json_encode($returnArr);exit;
// 				}

//      }catch(Exception $e){
//       //echo "<pre>";print_r($e);exit;
// 				$returnArr["Action"] = "0";
//         $returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";

// 				echo json_encode($returnArr);exit;
// 		}

}
###########################################################################

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
            $profileData = GetUserDetail($iUserId);
        } else {
            $profileData = GetDriverDetail($iUserId);
        }

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['profileData'] = $profileData;
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
                $profileData = GetUserDetail($iUserId);
            } else {
                $profileData = GetDriverDetail($iUserId);
            }

            if ($id > 0) {
                $returnArr['Action'] = "1";
                $returnArr['profileData'] = $profileData;
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

    //echo print_r($tripData);exit;
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

//Mehrshad commented
// if($type == "getDriverRideHistory"){
// 	$iDriverId     = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
// 	$date     = isset($_REQUEST["date"]) ? $_REQUEST["date"] : '';
//
// 	$date = date("Y-m-d",strtotime($date));
//
// 	$vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId,'','true');
// 	// $currencySymbol=get_value('currency', 'vSymbol', 'eDefault', 'Yes','','true');
// 	// $priceRatio=1;
// 	// $fRatioDriver = get_value('currency', 'Ratio', 'vName', $vCurrencyDriver,'','true');
// 	$currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver,'','true');
//
// 	$vLanguage=get_value('register_driver', 'vLang', 'iDriverId',$iDriverId,'','true');
// 	if($vLanguage == "" || $vLanguage == NULL){
// 		$vLanguage = "EN";
// 	}
//
// 	$sql = "SELECT tr.*, rate.vRating1, rate.vMessage,ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,ratings_user_driver as rate,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '".$date."%' AND tr.iActive='Finished' AND rate.iTripId = tr.iTripId AND rate.eUserType='Passenger' AND ru.iUserId=tr.iUserId";
// 	$tripData = $obj->MySQLSelect($sql);
//
// 	$totalEarnings = 0;
// 	$avgRating = 0;
//
// 	if(count($tripData) > 0){
//
// 		for($i=0;$i<count($tripData);$i++){
// 			$iFare = $tripData[$i]['fTripGenerateFare'];
// 			$fCommision = $tripData[$i]['fCommision'];
// 			$fDiscount = $tripData[$i]['fDiscount'];
// 			$vRating1 = $tripData[$i]['vRating1'];
// 			$priceRatio = $tripData[$i]['fRatio_'.$vCurrencyDriver];
//
// 			if(($iFare == "" || $iFare == 0) && $fDiscount > 0){
// 				$incValue = ($fDiscount - $fCommision);
// 				$totalEarnings = $totalEarnings + ($incValue * $priceRatio);
// 			}else if($iFare != "" && $iFare > 0){
// 				$incValue = ($iFare - $fCommision);
// 				$totalEarnings = $totalEarnings + ($incValue * $priceRatio);
// 			}
//
// 			$avgRating = $avgRating + $vRating1;
//
//       $returnArr = getTripPriceDetails($tripData[$i]['iTripId'],$iDriverId,"Driver");
//       $tripData[$i] = array_merge($tripData[$i], $returnArr);
// 		}
//
// 		$returnArr['Action'] = "1";
// 		$returnArr['message'] = $tripData;
//
// 	}else{
// 		$returnArr['Action'] = "0";
// 	}
// 	$returnArr['TotalEarning'] = strval(round($totalEarnings,1));
// 	$returnArr['TripDate'] = jdate('l, dS M Y',strtotime($date));
// 	$returnArr['TripCount'] = strval(count($tripData));
// 	$returnArr['AvgRating'] = strval(round(count($tripData) == 0? 0 : ($avgRating/count($tripData)),2));
// 	$returnArr['CurrencySymbol'] = $currencySymbol;
//
// 	echo json_encode($returnArr);
//
// }

/*if($type == "getDriverRideHistory"){
		$iDriverId     = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
		$date     = isset($_REQUEST["date"]) ? $_REQUEST["date"] : '';

		$date = date("Y-m-d",strtotime($date));

		$vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId,'','true');
		// $vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId,'','true');
		// $currencySymbol=get_value('currency', 'vSymbol', 'eDefault', 'Yes','','true');
		$currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver,'','true');
		// $priceRatio=1;
		// $fRatioDriver = get_value('currency', 'Ratio', 'vName', $vCurrencyDriver,'','true');
		// $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver,'','true');
		$vLanguage=get_value('register_driver', 'vLang', 'iDriverId',$iDriverId,'','true');
		if($vLanguage == "" || $vLanguage == NULL){
			$vLanguage = "EN";
		}

		$sql = "SELECT tr.*, rate.vRating1, rate.vMessage,ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,ratings_user_driver as rate,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '".$date."%' AND tr.iActive='Finished' AND rate.iTripId = tr.iTripId AND rate.eUserType='Passenger' AND ru.iUserId=tr.iUserId";

		$tripData = $obj->MySQLSelect($sql);

		$totalEarnings = 0;
		$avgRating = 0;

		if(count($tripData) > 0){

			for($i=0;$i<count($tripData);$i++){
				$iFare = $tripData[$i]['fTripGenerateFare'];
				$fCommision = $tripData[$i]['fCommision'];
				$fDiscount = $tripData[$i]['fDiscount'];
				$vRating1 = $tripData[$i]['vRating1'];
				$priceRatio = $tripData[$i]['fRatio_'.$vCurrencyDriver];

				if(($iFare == "" || $iFare == 0) && $fDiscount > 0){
					$incValue = ($fDiscount - $fCommision);
					$totalEarnings = $totalEarnings + ($incValue * $priceRatio);
				}else if($iFare != "" && $iFare > 0){
					$incValue = ($iFare - $fCommision);
					$totalEarnings = $totalEarnings + ($incValue * $priceRatio);
				}

				$avgRating = $avgRating + $vRating1;
				//$tripData[$i]['iFare'] = $tripData[$i]['iFare'] - $tripData[$i]['fCommision'];
        $tripData[$i]['iFare'] = $tripData[$i]['fTripGenerateFare'] - $tripData[$i]['fCommision'];
				// $tripData[$i]['iFare']=strval(round($tripData[$i]['iFare'] * $priceRatio,1));
				$tripData[$i]['fPricePerKM']=strval($tripData[$i]['fPricePerKM']);
				// $tripData[$i]['iBaseFare']=strval(round($tripData[$i]['iBaseFare'] * $priceRatio,1));
				$tripData[$i]['fPricePerMin']=strval($tripData[$i]['fPricePerMin']);
				// $tripData[$i]['fCommision']=strval(number_format(round($tripData[$i]['fCommision'] * $priceRatio,1),2));
				// $tripData[$i]['fDiscount']=strval(round($tripData[$i]['fDiscount'] * $priceRatio,1));

				if($tripData[$i]['fMinFareDiff'] > 0){
					$tripData[$i]['fMinFareDiff']=strval(number_format(round($tripData[$i]['fMinFareDiff'] * $priceRatio,1),2));
				}else{
					$tripData[$i]['fMinFareDiff']="0";
				}

				$tripData[$i]['TripTime']=date('h:iA',strtotime($tripData[$i]['tTripRequestDate']));

				$tripData[$i]['vVehicleType']=get_value('vehicle_type', 'vVehicleType_'.$vLanguage, 'iVehicleTypeId', $tripData[$i]['iVehicleTypeId'],'','true');

				$tripData[$i]['TripFareOfMinutes']=strval(number_format(round($tripData[$i]['fPricePerMin']* $priceRatio,1),2));
                $tripData[$i]['TripFareOfDistance']=strval(number_format(round($tripData[$i]['fPricePerKM']* $priceRatio,1),2));
				$tripData[$i]['iFare']=strval(number_format(round($tripData[$i]['iFare'] * $priceRatio,1),2));

				if($tripData[$i]['fDiscount'] != "" && $tripData[$i]['fDiscount'] != "0"&& $tripData[$i]['fDiscount'] != 0){
					$tripData[$i]['fDiscount']= strval(number_format(round($tripData[$i]['fDiscount'] * $priceRatio,1),2));
				}else{
					$tripData[$i]['fDiscount']= strval(round($tripData[$i]['fDiscount'] * $tripData[$i]['fRatioPassenger'],1));
				}
                // $row[$i]['fDiscount']=strval(number_format(round($row[$i]['fDiscount'] * $row[$i]['fRatioPassenger'],1),2));
				$tripData[$i]['iBaseFare']=strval(number_format(round($tripData[$i]['iBaseFare'] * $priceRatio,1),2));
				$tripData[$i]['fCommision']= strval(number_format(round($tripData[$i]['fCommision']* $priceRatio,1),2));

         $tripData[$i]['fWalletDebit'] = strval(number_format(round($tripData[$i]['fWalletDebit']* $priceRatio,1),2));
			 $tripData[$i]['fSurgePriceDiff'] = strval(number_format(round($tripData[$i]['fSurgePriceDiff']* $priceRatio,1),2));

			 $surgePrice = 1;
			if($tripData[$i]['fPickUpPrice'] > 0){
				$surgePrice=$tripData[$i]['fPickUpPrice'];
			}else{
				$surgePrice=$tripData[$i]['fNightPrice'];
			}
			$tripData[$i]['SurgePriceFactor'] = strval($surgePrice);


				$tripData[$i]['tTripRequestDate']=date('dS M \a\t h:i a',strtotime($tripData[$i]['tTripRequestDate']));

				$totalTime=0;
				 $hours= dateDifference($tripData[$i]['tStartDate'],$tripData[$i]['tEndDate'],'%h');
				 $minutes= dateDifference($tripData[$i]['tStartDate'],$tripData[$i]['tEndDate'],'%i');
				 $seconds= dateDifference($tripData[$i]['tStartDate'],$tripData[$i]['tEndDate'],'%s');

				 if($hours>0){
					 $totalTime = $hours*60;
				}if($minutes>0){
					 $totalTime = $totalTime+$minutes;
				}
				$totalTime = $totalTime.".".$seconds;

				$tripData[$i]['TripTimeInMinutes']=$totalTime;

				$tripData[$i]['TripRating'] = get_value('ratings_user_driver', 'vRating1', 'iTripId', $tripData[$i]['iTripId'],' AND eUserType="Driver"','true');
				$tripData[$i]['CurrencySymbol'] = $currencySymbol;
			}

			$returnArr['Action'] = "1";
			$returnArr['message'] = $tripData;

		}else{
			$returnArr['Action'] = "0";
		}
		$returnArr['TotalEarning'] = strval(round($totalEarnings,1));
		$returnArr['TripDate'] = date('l, dS M Y',strtotime($date));
		$returnArr['TripCount'] = strval(count($tripData));
		$returnArr['AvgRating'] = strval($avgRating/count($tripData));
		$returnArr['CurrencySymbol'] = $currencySymbol;

		echo json_encode($returnArr);

	}  */
###########################################################################

###########################################################################

if ($type == "loadDriverFeedBack") {
    global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

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
        if ($TotalPages > $page) {
            $returnData['NextPage'] = strval($page + 1);
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
        $returnArr['Action'] = "01";
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


        $account_sid = $generalobj->getConfigurations("configurations", "MOBILE_VERIFY_SID_TWILIO");
        $auth_token = $generalobj->getConfigurations("configurations", "MOBILE_VERIFY_TOKEN_TWILIO");
        $twilioMobileNum = $generalobj->getConfigurations("configurations", "MOBILE_NO_TWILIO");
        $isdCode = $generalobj->getConfigurations("configurations", "SITE_ISD_CODE");

        $client = new Services_Twilio($account_sid, $auth_token);

        $message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via projectName SOS. Please reach out to him/her urgently. The details of the ride are: Trip start time: ' . date('dS M \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Driver name: ' . $tripData[0]['vDriverName'] . '. Driver number:(' . $tripData[0]['DriverPhone'] . "). Driver's car number: " . $tripData[0]['vLicencePlate'];

        for ($i = 0; $i < count($dataArr); $i++) {
            $phone = preg_replace("/[^0-9]/", "", $dataArr[$i]['vPhone']);

            $toMobileNum = "+" . $phone;

            // $result = sendEmeSms($client,$twilioMobileNum,$toMobileNum,$message);
            // if($result ==0){
            // $toMobileNum = "+".$isdCode.$phone;
            // sendEmeSms($client,$twilioMobileNum,$toMobileNum,$message);
            // }

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
// if ($type == "getTransactionHistory")
// {
// 	global $generalobj;
// 	#echo "hello"; exit;
//
// 	$page        = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
// 	$iUserId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
// 	$UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
//
// 	if($UserType=="Passenger"){
// 		$UserType="Rider";
// 	}
//
// 	$ssql='';
// 	$per_page=10;
// 	$sql_all  = "SELECT COUNT(iUserWalletId) As TotalIds FROM user_wallet WHERE  iUserId='".$iUserId."' AND eUserType = '".$UserType."' ".$ssql." ";
// 	$data_count_all = $obj->MySQLSelect($sql_all);
// 	$TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
//
// 	$start_limit = ($page - 1) * $per_page;
// 	$limit       = " LIMIT " . $start_limit . ", " . $per_page;
//
// 	$user_available_balance = $generalobj->get_user_available_balance($iUserId,$UserType);
//
// 	//$sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
// 	$sql = "SELECT * from user_wallet where iUserId='".$iUserId."' AND eUserType = '".$UserType."' ".$ssql." ORDER BY iUserWalletId ASC";
// 	$Data = $obj->MySQLSelect($sql);
// 	$totalNum = count($Data);
//
// 	$vSymbol = get_value('currency', 'vSymbol', 'eDefault','Yes','','true');
// 	if($UserType == 'Driver')
// 	{
// 		$uservSymbol = get_value('register_driver', 'vCurrencyDriver', 'iDriverId',$iUserId,'','true');
// 	}
// 	else
// 	{
// 		$uservSymbol = get_value('register_user', 'vCurrencyPassenger', 'iUserId',$iUserId,'','true');
// 	}
//
// 	$userCurrencySymbol = get_value('currency', 'vSymbol', 'vName',$uservSymbol,'','true');
//
// 	$i=0;
// 	if ( count($Data) > 0 ) {
//
// 		$row = $Data;
// 		$prevbalance = 0;
//           while ( count($row)> $i ) {
//           	if($row[$i]['eType'] == "Credit"){
// 				$row[$i]['currentbal'] = $prevbalance+$row[$i]['iBalance'];
// 			}else{
// 			    $row[$i]['currentbal'] = $prevbalance-$row[$i]['iBalance'];
// 			}
//               $prevbalance = $row[$i]['currentbal'];
//               $row[$i]['dDate'] = date('d-M-Y',strtotime($row[$i]['dDate']));
//
// 			//$row[$i]['currentbal'] = $vSymbol.$row[$i]['currentbal'];
// 			//$row[$i]['iBalance'] = $vSymbol.$row[$i]['iBalance'];
// 			$row[$i]['currentbal'] = $generalobj->userwalletcurrency($row[$i]['fRatio_'.$uservSymbol],$row[$i]['currentbal'],$uservSymbol);
// 			$row[$i]['iBalance'] = $generalobj->userwalletcurrency($row[$i]['fRatio_'.$uservSymbol],$row[$i]['iBalance'],$uservSymbol);
// 			$i++;
// 		}
//
// 		$returnData['message'] = array_reverse($row);
// 		if ($TotalPages > $page) {
// 			$returnData['NextPage'] = $page + 1;
// 		} else {
// 			$returnData['NextPage'] = 0;
// 		}
//
// 		$returnData['user_available_balance_default']=$vSymbol.$user_available_balance;
// 		$returnData['user_available_balance'] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$uservSymbol));
// 		$returnData['Action']="1";
// 		#echo "<pre>"; print_r($returnData); exit;
// 		echo json_encode($returnData);
//
// 	}else{
// 		$returnData['Action']="0";
// 		$returnData['message']="LBL_NO_TRANSACTION_AVAIL";
// 		$returnData['user_available_balance']= $userCurrencySymbol."0";
// 		echo json_encode($returnData);
// 	}
//
// }

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
if ($type == "loadPackageTypes") {
    $vehicleTypes = get_value('package_type', '*', 'eStatus', 'Active');

    if (count($vehicleTypes) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $vehicleTypes;
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

    $sql = "SELECT tr.vReceiverName,tr.vReceiverMobile,tr.tPickUpIns,tr.tDeliveryIns,tr.tPackageDetails,pt.vName as packageType,concat(ru.vName,' ',ru.vLastName) as senderName, ru.vPhone as senderMobile from trips as tr, register_user as ru, package_type as pt WHERE ru.iUserId = tr.iUserId AND tr.iTripId = '" . $iTripId . "' AND pt.iPackageTypeId = tr.iPackageTypeId";
    $Data = $obj->MySQLSelect($sql);
    $Data[0]['tPickUpIns'] = stripslashes($Data[0]['tPickUpIns']);
    $Data[0]['tDeliveryIns'] = stripslashes($Data[0]['tDeliveryIns']);

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

if ($type == "collectTip") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';

    $tbl_name = "register_user";
    $currencycode = "vCurrencyPassenger";
    $iUserId = "iUserId";
    $eUserType = "Rider";

    if ($iMemberId == "") {
        $iMemberId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
    }

    $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId, '', 'true');
    $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId, '', 'true');
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId, '', 'true');
    $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode, '', 'true');
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

        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);

        $returnArr["Action"] = "1";
        echo json_encode($returnArr);
        exit;

    } else if ($price > 0.51) {
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

if ($type == "DeclineTripRequest") {
    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';

    $request_count = UpdateDriverRequest($driver_id, $passenger_id, "0", "Decline");

    echo $request_count;
}

###########################################################################

###########################################################################

###########################################################################

###########################################################################
###########################################################################
###########################################################################

###########################################################################

if ($type == "pushNotification") {

    echo $pass = $generalobj->decrypt("XcIZDZwoXA==");
    exit;
    $deviceToken = "0d68c45a3f731d53398785563fdf22418dfd0c73d0191f886b4ef2a63219c215";
    //5240381e085cf439d5bda4f322440fc0b9cd750315b91c725cfdc12996545eb1

    // Put your private key's passphrase here:
    $passphrase = '123456';

    // Put your alert message here:
    $message['key'] = 'push notification!';

    $message_json = json_encode($message);
    ////////////////////////////////////////////////////////////////////////////////

    $ctx = stream_context_create();
    //        stream_context_set_option($ctx, 'ssl', 'local_cert', 'apn-dev-uberapp.pem');
    stream_context_set_option($ctx, 'ssl', 'local_cert', 'driver_apns_dev.pem');
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

    // Open a connection to the APNS server
    $fp = stream_socket_client(
        'ssl://gateway.sandbox.push.apple.com:2195', $err,
        $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    if (!$fp)
        exit("Failed to connect: $err $errstr" . PHP_EOL);

    echo 'Connected to APNS' . PHP_EOL;
    $msg = "{\"iDriverId\":\"20\"}";
    // Create the payload body
    $body['aps'] = array(
        'alert' => 'Test',
        'content-available' => 1,
        'body' => $msg,
        'sound' => 'default'

    );

    // Encode the payload as JSON
    $payload = json_encode($body);

    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));

    if (!$result)
        echo 'Message not delivered' . PHP_EOL;
    else
        echo 'Message successfully delivered' . PHP_EOL;

    // Close the connection to the server
    fclose($fp);
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

// 		$iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
// 		$eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
// 		$fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
// 		if($eMemberType == "Passenger"){
// 			$tbl_name = "register_user";
// 			$currencycode = "vCurrencyPassenger";
// 			$iUserId = "iUserId";
// 			$eUserType = "Rider";
// 		}
// 		else
// 		{
// 			$returnArr["Action"] = "0";
// 			$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
// 			echo json_encode($returnArr);exit;
// 		}

// 		/*
// 		$userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId,'','true');
// 		$currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes','','true');
// 		$currencyratio = get_value('currency', 'Ratio', 'vName', $currencyCode,'','true');
// 		$price = $fAmount * $currencyratio;
// 		$price_new = $price;
// 		$price_new = round($price_new);
// 		*/
// 		//$fAmount = round($fAmount);


// 		try{
// 			require_once(TPATH_CLASS .'savar/class.factor.php');
// 			$token = SavarFactor::Create($iMemberId,$fAmount);

// 			if($token !== false)
// 			{
// 				$returnArr["Action"] = "1";
// 				$returnArr['message']= $tconfig["tsite_url"] . 'savar_payment/?token=' . $token;
// 				echo json_encode($returnArr);exit;
// 			}
// 			else
// 			{
// 				$returnArr["Action"] = "0";
// 				$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
// 				echo json_encode($returnArr);exit;
// 			}
// 		}catch(Exception $e){
// 				//echo "<pre>";print_r($e);exit;
// 				$returnArr["Action"] = "0";
// 				$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
// 				echo json_encode($returnArr);exit;
// 		}
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

if ($type == "getVhicleType") {

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $cLatitude = isset($_REQUEST["cLatitude"]) ? $_REQUEST["cLatitude"] : '0';
    $cLongitude = isset($_REQUEST["cLongitude"]) ? $_REQUEST["cLongitude"] : '0';

    if ($cLatitude == '0' || $cLongitude == '0') {
        $returnArr['Action'] = '0';
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    } else {
        try {

            $sql = "SELECT * FROM `register_user` WHERE iUserId='$iUserId'";
            $row = $obj->MySQLSelect($sql);

            if (count($row) > 0) {
                $sql = "SELECT * FROM savar_area WHERE MBRWITHIN( POINT($cLongitude, $cLatitude) , sPolygonArea )  ORDER BY `savar_area`.`sPriority` DESC";

                $res = $obj->MySQLSelect($sql);

                if (count($res) > 0) {
                    $areaType = $res[0]['aId'];
                    $sql = "SELECT * FROM `vehicle_type` WHERE `vSavarArea` = $areaType ";


                    $app_type = $generalobj->getConfigurations("configurations", "APP_TYPE");;
                    if ($app_type == "Ride") {
                        $sql .= " AND eType = 'Ride' ";
                    } else if ($app_type == "Delivery") {
                        $sql .= " AND eType = 'Deliver' ";
                    }

                    $vehicleTypes = $obj->MySQLSelect($sql);

                    //echo print_r($sql);die();

                    $priceRatio = get_value('currency', 'Ratio', 'vName', $row[0]['vCurrencyPassenger'], '', 'true');

                    if (count($vehicleTypes) > 0) {
                        #Logger($vehicleTypes);


                        for ($i = 0; $i < count($vehicleTypes); $i++) {
                            $vehicleTypes[$i]['fPricePerKM'] = "" . round($vehicleTypes[$i]['fPricePerKM'] * $priceRatio, 0) . "";
                            $vehicleTypes[$i]['fPricePerMin'] = "" . round($vehicleTypes[$i]['fPricePerMin'] * $priceRatio, 0) . "";
                            $vehicleTypes[$i]['iBaseFare'] = "" . round($vehicleTypes[$i]['iBaseFare'] * $priceRatio, 0) . "";
                            $vehicleTypes[$i]['fCommision'] = "" . round($vehicleTypes[$i]['fCommision'] * $priceRatio, 0) . "";
                            $vehicleTypes[$i]['iMinFare'] = "" . round($vehicleTypes[$i]['iMinFare'] * $priceRatio, 0) . "";
                            $vehicleTypes[$i]['FareValue'] = "" . round($vehicleTypes[$i]['fFixedFare'] * $priceRatio, 0) . "";
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

        $url = "37.130.202.188/services.jspd";
        $param = array
        (
            'uname' => 'tavasoli321',
            'pass' => 'tavasoli26250',
            'from' => '100020400',
            'message' => $message,
            'to' => json_encode($driverMobile),
            'op' => 'send'
        );

        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);

        $response2 = json_decode($response2);
        $res_code = $response2[0];
        $res_data = $response2[1];
        //echo $res_data;
        if ($res_data > 0) {
            $query = " INSERT INTO `sentsms`(`smsId`, `iMsgCode`, `riderId`, `driverId`, `messege`, `type`, `date`)
  			VALUES ('$res_data','$iMsgCode','$riderId','$driverId','$message','$enum_type','$date_')";
            $obj->sql_query($query);
            echo "sent";
        } else {
            //nop - lost data
        }
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

?>
