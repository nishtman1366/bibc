<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
  require_once(TPATH_CLASS . "class.general_admin.php");
  $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$tbl_name = 'register_user';
$tbl_name1 = 'cab_booking';

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
$vDescription = isset($_POST['vDescription']) ? $_POST['vDescription'] : '';
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
$vImgName = isset($_POST['vImgName']) ? $_POST['vImgName'] : '';
$vCurrencyPassenger = isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
$tTripComment = isset($_POST['tTripComment']) ? $_POST['tTripComment'] : '';
$vPass = $generalobj->encrypt($vPassword);

$sql = "select * from currency where eStatus='Active' AND eDefault='Yes'";
$db_country = $obj->MySQLSelect($sql);

$sql1 = "select * from language_master where eStatus='Active' AND eDefault='Yes'";
$db_language= $obj->MySQLSelect($sql1);

$sql="select cn.vCountry,cn.vPhoneCode from country cn inner join
configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);

if (isset($_POST['submit'])) {

  $pickups = explode(',', $_POST['from_lat_long']);
  $dropoff = explode(',', $_POST['to_lat_long']);
  $vSourceLatitude = isset($pickups[0]) ? trim(str_replace("(","",$pickups[0])) : '';
  $vSourceLongitude = isset($pickups[1]) ? trim(str_replace(")","",$pickups[1])) : '';
  $vDestLatitude = isset($dropoff[0]) ? trim(str_replace("(","",$dropoff[0])) : '';
  $vDestLongitude = isset($dropoff[1]) ? trim(str_replace(")","",$dropoff[1])) : '';
  $vDistance = isset($_POST['distance']) ? (round(number_format($_POST['distance']/1000))) : '';
  $vDuration = isset($_POST['duration']) ? (round(number_format($_POST['duration']/60))) : '';
  $iUserId = isset($_POST['iUserId']) ? $_POST['iUserId'] : '';
  $iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
  $dBooking_date = isset($_POST['dBooking_date']) ? $_POST['dBooking_date'] : '';
  $vSourceAddresss = isset($_POST['vSourceAddresss']) ? $_POST['vSourceAddresss'] : '';
  $tDestAddress = isset($_POST['tDestAddress']) ? $_POST['tDestAddress'] : '';
  $eAutoAssign = isset($_POST['eAutoAssign']) ? $_POST['eAutoAssign'] : 'No';
  $eStatus1 = 'Assign';
  $iVehicleTypeId = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';
  $iCabBookingId = isset($_POST['iCabBookingId']) ? $_POST['iCabBookingId'] : '';

  $SQL1 = "SELECT vName,vLastName,vEmail,iUserId FROM $tbl_name WHERE vEmail = '$vEmail'";
  $email_exist = $obj->MySQLSelect($SQL1);
  $iUserId = $email_exist[0]['iUserId'];
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
    `vCurrencyPassenger` = '" . $db_country[0]['vName'] . "',
    `vLang` = '" . $db_language[0]['vCode']. "',
    `vDescription` = '" . $vDescription. "',
    `vAddress` = '" . $vAddress. "',
    `vInviteCode` = '" . $vInviteCode . "'";
    $obj->sql_query($query);
    $iUserId = mysql_insert_id();
  }

  if($iUserId == "" || $iUserId == "0" || $vSourceAddresss == "" || $tDestAddress == ""){
    $var_msg = "Booking details is not add/update because missing information";
    if($iCabBookingId == ""){
      header("location:add_booking.php?success=0&var_msg=".$var_msg); exit;
    }else{
      header("location:add_booking.php?booking_id=".$iCabBookingId."success=0&var_msg=".$var_msg); exit;
    }
  }

  $rand_num=rand ( 10000000 , 99999999 );
  $q1 = "INSERT INTO ";
  $whr = ",`vBookingNo`='".$rand_num."'";
  $edit = "";
  if($iCabBookingId != "" && $iCabBookingId != '0') {
    $q1 = "UPDATE ";
    $whr = " WHERE `iCabBookingId` = '" . $iCabBookingId . "'";
    $edit = '1';
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
  `dBooking_date` = '" . $dBooking_date . "',
  `vSourceAddresss` = '" . $vSourceAddresss . "',
  `tDestAddress` = '" . $tDestAddress . "',
  `eStatus`='" . $eStatus1 . "',
  `eAutoAssign`='" . $eAutoAssign . "',
  `tTripComment`='" . $tTripComment . "',
  `eCancelBy`='',
  `iVehicleTypeId` = '" . $iVehicleTypeId . "'".$whr;
  $obj->sql_query($query1);

  $sql="select vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode from register_driver where iDriverId=".$iDriverId;
  $driver_db=$obj->MySQLSelect($sql);

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
  $return = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER",$Data1);
  $return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER",$Data1);

  $query = "SELECT * FROM driver_vehicle WHERE iDriverVehicleId=".$driver_db[0]['iDriverVehicleId'];
  $db_driver_vehicles = $obj->MySQLSelect($query);

  $vPhone = $vPhone;
  $vcode = $db_con[0]['vPhoneCode'];
  $Booking_Date = @date('d-m-Y',strtotime($dBooking_date));
  $Booking_Time = @date('H:i:s',strtotime($dBooking_date));

  $query = "SELECT * FROM register_user WHERE iUserId=".$iUserId;
  $db_user= $obj->MySQLSelect($query);
  $Pass_name = $vName.' '.$vLastName;
  $vcode = $db_user[0]['vPhoneCode'];
  $maildata['DRIVER_NAME'] = $Data1['vDriver'];
  $maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];
  $maildata['BOOKING_DATE'] = $Booking_Date;
  $maildata['BOOKING_TIME'] =  $Booking_Time;
  $maildata['BOOKING_NUMBER'] = $Data1['vBookingNo'];

  //Send sms to User
  if($maildata['PLATE_NUMBER'] && $driver_db && $driver_db[0])
  {
    $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE",$maildata);
  }
  else
  {
    $message_layout = "به جهت درخواست رزرو شماره ی".$Data1['vBookingNo']."، راننده ای در تاریخ".$Booking_Date." و ساعت".$Booking_Time."شما را سوار خواهد کرد";
  }
  $return4 = $generalobj->sendUserSMS($vPhone,$vcode,$message_layout,"");

  //Send sms to Driver
  $vPhone = $driver_db[0]['vPhone'];
  $vcode1 = $driver_db[0]['vcode'];

  $maildata1['PASSENGER_NAME'] = $Pass_name;
  $maildata1['BOOKING_DATE'] = $Booking_Date;
  $maildata1['BOOKING_TIME'] =  $Booking_Time;
  $maildata1['BOOKING_NUMBER'] = $Data1['vBookingNo'];
  //$message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE",$maildata1);
  //$return5 = $generalobj->sendUserSMS($vPhone,$vcode1,$message_layout,"");

  if($return && $return1){
    $success = 1;
    $var_msg = "Booking Has Been Added Successfully.";
    header("location:add_booking.php?success=1&vassign=$edit"); exit;
  }else{
    $error = 1;
    $var_msg = $langage_lbl_admin['LBL_ERROR_OCCURED'];
  }
  header("location:add_booking.php?success=1&vassign=$edit"); exit;
}else {
  header("location:add_booking.php?success=1&vassign=$edit"); exit;
}
?>
