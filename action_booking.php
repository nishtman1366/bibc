<?php
ob_start();
$ISDEBUG = false;
include_once('common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
$generalobj->check_member_login();
$tbl_name = 'register_user';
$tbl_name1 = 'cab_booking';

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$vDescription = isset($_POST['vDescription']) ? $_POST['vDescription'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
$vImgName = isset($_POST['vImgName']) ? $_POST['vImgName'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$vCurrencyPassenger = isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
$tTripComment = isset($_POST['tTripComment']) ? $_POST['tTripComment'] : '';
$vPass = $generalobj->encrypt($vPassword);

if (isset($_POST['submit'])) {
	$pickups = explode(',', $_POST['from_lat_long']); // from latitude-Longitude
	$dropoff = explode(',', $_POST['to_lat_long']); // To latitude-Longitude
	$vSourceLatitude = isset($pickups[0]) ? trim(str_replace("(","",$pickups[0])) : '';
	$vSourceLongitude = isset($pickups[1]) ? trim(str_replace(")","",$pickups[1])) : '';
	$vDestLatitude = isset($dropoff[0]) ? trim(str_replace("(","",$dropoff[0])) : '';
	$vDestLongitude = isset($dropoff[1]) ? trim(str_replace(")","",$dropoff[1])) : '';
	$vDistance = isset($_POST['distance']) ? (round(number_format($_POST['distance']/1000))) : '';
	$vDuration = isset($_POST['duration']) ? (round(number_format($_POST['duration']/60))) : '';
	$iUserId = isset($_POST['iUserId']) ? $_POST['iUserId'] : '';
	$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
	//echo "<pre>";print_r($iDriverId); exit;

	$dBooking_date = isset($_POST['dBooking_date']) ? $_POST['dBooking_date'] : '';
	$dBooking_date = savar_request_date_to_gregorian($_REQUEST['dBooking_date']);
	$dBooking_date .= ' ' . $_POST['dBooking_time'];

	$vSourceAddresss = isset($_POST['vSourceAddresss']) ? $_POST['vSourceAddresss'] : '';
	$tDestAddress = isset($_POST['tDestAddress']) ? $_POST['tDestAddress'] : '';
	$tTripComment = isset($_POST['tTripComment']) ? $_POST['tTripComment'] : '';
	$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
	$eStatus1 = 'Assign';
	$iVehicleTypeId = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';
	$iCabBookingId = isset($_POST['iCabBookingId']) ? $_POST['iCabBookingId'] : '';
	$fGDdistance = isset($_POST['fGDdistance']) ? $_POST['fGDdistance'] : '';
	$fGDtime = isset($_POST['fGDtime']) ? $_POST['fGDtime'] : '';

	$SQL1 = "SELECT vName,vLastName,vEmail,vPhone FROM $tbl_name WHERE vPhone = '$vPhone'";
	$email_exist = $obj->MySQLSelect($SQL1);

	if($iCabBookingId == "") {
		$q = "INSERT INTO ";
		$where = '';
		$query = $q . " `" . $tbl_name . "` SET
		`vName` = '" . $vName . "',
		`vLastName` = '" . $vLastName . "',
		`vEmail` = '" . $vEmail . "',
		`vPassword` = 'DShj8tGU',
		`vPhone` = '" . $vPhone . "',
		`vCountry` = '" . $vCountry . "',
		`vPhoneCode` = '" . $vPhoneCode . "',
		`eStatus` = '" . $eStatus . "',
		`vImgName` = '" . $vImgName . "',
		`vDescription` = '" . $vDescription . "',
		`vAddress` = '" . $vAddress . "',
		`vCurrencyPassenger` = '" . $db_country[0]['vName'] . "',
		`vLang` = '" . $db_language[0]['vCode']. "',
		`vInviteCode` = '" . $vInviteCode . "'";

		//echo $query; die;
		$res = $obj->sql_query($query);
		$iUserId = mysql_insert_id();
		echo "UserID: " . $iUserId . "\r\n<br>";
		print_r($res);
	}
	if($iUserId == "" || $iUserId == "0" /* DISABLE BY SEYYED AMIR || $iDriverId == ""*/ || $iDriverId == "0"
/* || $vSourceAddresss == "" || $tDestAddress == "" */)
	{
			$var_msg = "Booking details is not add/update because missing information";
			if($iCabBookingId == ""){
					header("location:manual_dispatch.php?booking_id=".$iCabBookingId."&success=0&var_msg=".$var_msg); exit;
			}else{
					header("location:manual_dispatch.php?booking_id=".$iCabBookingId."&success=0&var_msg=".$var_msg); exit;
			}
	}

// add by seyyed amir
// find source address
if($vSourceAddresss == ""){
		$vGMapLangCode='fa';
		$GOOGLE_API_KEY=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");

		$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$vSourceLatitude.",".$vSourceLongitude."&key=".$GOOGLE_API_KEY."&language=".$vGMapLangCode;

		try {

			$jsonfile = file_get_contents($url);

			$jsondata = json_decode($jsonfile);
			$source_address=$jsondata->results[0]->formatted_address;

			$vSourceAddresss = $source_address ;

		} catch (Exception $ex) {
		}
	}

if($tDestAddress == ""){
	$vGMapLangCode='fa';
	$GOOGLE_API_KEY=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");

	$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$vDestLatitude.",".$vDestLongitude."&key=".$GOOGLE_API_KEY."&language=".$vGMapLangCode;

	try {

		$jsonfile = file_get_contents($url);
		$jsondata = json_decode($jsonfile);
		$dest_address=$jsondata->results[0]->formatted_address;

		$tDestAddress = $dest_address ;

	} catch (Exception $ex) {
	}
}
///////////////////////////////////////
// die($_REQUEST['numberOfCar'] . " asd");
$numberOfCar = intval($_REQUEST['numberOfCar']);

if($numberOfCar <= 0)
$numberOfCar = 1;

//if($_POST['rideType'] == "manual"){
$rand_num=rand ( 10000000 , 99999999 );
$q1 = "INSERT INTO ";
$whr = ",`vBookingNo`='".$rand_num."'";
$edit = "";
if($iCabBookingId != "" && $iCabBookingId != '0') {
	$q1 = "UPDATE ";
	$whr = " WHERE `iCabBookingId` = '" . $iCabBookingId . "'";
	$edit = '1';

	$numberOfCar = 1;
}
else
{
	$time_left_for_dispach_exec = $generalobj->getConfigurations("configurations","TIME_LEFT_FOR_EXEC_CAB_BOOKING"); // time by minute

	if($time_left_for_dispach_exec == '')
	$time_left_for_dispach_exec  = 5;

	$time = strtotime($dBooking_date);
	$now_time = time();

	if($now_time - $time > 0)
	{
		$dBooking_date = date("Y-m-d H:i:s", time() + ($time_left_for_dispach_exec * 60));
	}
	else if($time - $now_time < $time_left_for_dispach_exec * 60)
	{
		$dBooking_date = date("Y-m-d H:i:s", time() + ($time_left_for_dispach_exec * 60));
	}
}
$query1 = $q1 . " `" . $tbl_name1 . "` SET
`iUserId` = '" . $iUserId . "',
`iDriverId` = '" . $iDriverId . "',
`vSourceLatitude` = '" . $vSourceLatitude . "',
`vSourceLongitude` = '" . $vSourceLongitude . "',
`vDestLatitude` = '" . $vDestLatitude . "',
`vDestLongitude` = '" . $vDestLongitude . "',
`vDistance` = '" . $vDistance . "',
`vDuration` = '" . $vDuration . "',
`iCompanyId` = '" . $iCompanyId . "',
`dBooking_date` = '" . $dBooking_date . "',
`vSourceAddresss` = '" . $vSourceAddresss . "',
`tDestAddress` = '" . $tDestAddress . "',
`tTripComment` = '" . $tTripComment . "',
`eStatus`='" . $eStatus1 . "',
`fGDdistance`='" . $fGDdistance . "',
`fGDtime`='" . $fGDtime . "',
`eCancelBy`='',
`iVehicleTypeId` = '" . $iVehicleTypeId . "'".$whr;

if($iCabBookingId == "" || $iCabBookingId == '0')
{
	//die($numberOfCar . "asd");
	for($i = 0; $i < $numberOfCar ; $i++)
		$iCabBookingId = $obj->MySQLInsert($query1);
}
else
$obj->sql_query($query1);



$time_dif = strtotime($dBooking_date) - time();
if($time_dif < 0  || $time_dif < (5 * 60)  )
{
	echo "Booking ID: " . $iCabBookingId . "<br>\r\n";
	echo file_get_contents($tconfig["tsite_url"] . "webservice.php?type=startBooking&cabBookingId=" .$iCabBookingId);
}

$sql="select vName,vLastName,vEmail,iDriverVehicleId from register_driver where iDriverId=".$iDriverId;
$driver_db=$obj->MySQLSelect($sql);
//echo "<pre>";print_r($driver_db);

$Data1['vRider']=$email_exist[0]['vName']." ".$email_exist[0]['vLastName'];
$Data1['vDriver']=$driver_db[0]['vName']." ".$driver_db[0]['vLastName'];
$Data1['vDriverMail']=$driver_db[0]['vEmail'];
$Data1['vRiderMail']=$email_exist[0]['vEmail'];
$Data1['vSourceAddresss']=$vSourceAddresss;
$Data1['tDestAddress']=$tDestAddress;
$Data1['dBookingdate']=$dBooking_date;
$Data1['vBookingNo']=$rand_num;

if($edit == '1')
{
	$sql="select vBookingNo from cab_booking where `iCabBookingId` = '" . $iCabBookingId . "'";
	$cab_id=$obj->MySQLSelect($sql);
	$Data1['vBookingNo']=$cab_id[0]['vBookingNo'];
}
//$Data1['vDistance']=$vDistance;
//$Data1['vDuration']=$vDuration;

//echo "<pre>";print_r($Data1);exit;
$return == true;//$return  = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER",$Data1);
$return1 = true;//$return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER",$Data1);

// Start Send SMS

$query = "SELECT * FROM driver_vehicle WHERE iDriverVehicleId=".$driver_db[0]['iDriverVehicleId'];
$db_driver_vehicles = $obj->MySQLSelect($query);

$vPhone = $vPhone;
$vcode = $vPhoneCode;
$Booking_Date = @date('d-m-Y',strtotime($dBooking_date));
$Booking_Time = @date('H:i:s',strtotime($dBooking_date));

$query = "SELECT * FROM register_user WHERE iUserId=".$iUserId;
$db_user= $obj->MySQLSelect($query);
$Pass_name = $vName.' '.$vLastName;
$maildata['DRIVER_NAME'] = $Data1['vDriver'];
$maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];
$maildata['PASSENGER_NAME'] = $Pass_name;
$maildata['BOOKING_DATE'] = $Booking_Date;
$maildata['BOOKING_TIME'] =  $Booking_Time;
$maildata['BOOKING_NUMBER'] = $Data1['vBookingNo'];
//Send sms to User
//$message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE",$maildata);
//$return4 = $generalobj->sendUserSMS($vPhone,$vcode,$message_layout,"");
//Send sms to Driver
//$message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE",$maildata);
//$return5 = $generalobj->sendUserSMS($vPhone,$vcode,$message_layout,"");


echo "Return1: ";
var_dump($return);
echo "\r\n";
echo "Return2: ";
var_dump($return1);
echo "\r\n";

if($return || $return1){
	$success = 1;
	$var_msg = "Booking Has Been Added Successfully.";
	header("location:manual_dispatch.php?success=1&vassign=$edit&var_msg=$var_msg"); die($var_msg);
}else{
	$error = 1;
	$var_msg = $langage_lbl['LBL_ERROR_OCCURED'];
	header("location:manual_dispatch.php?success=0&vassign=$edit&var_msg=$var_msg"); die($var_msg);
}

$msg = "Booking Has Been Added Successfully..";
header("location:manual_dispatch.php?success=1&vassign=$edit&var_msg=$var_msg"); die($msg);
//}
//include_once("go_booking.php");
}
?>
