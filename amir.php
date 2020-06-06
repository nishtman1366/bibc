<?php

echo date("H:m:s");
return;

$ISDEBUG = true;
include_once('common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
include_once(TPATH_CLASS.'configuration.php');










die(1);
$url = $tconfig["tsite_url"] . "webservice.php?type=startBooking&cabBookingId=51";
echo 'URL: ' . $url;
echo file_get_contents($url);

// اصلاح کد رفرال برای مسافران
/*
$sql = "SELECT * FROM `register_user` WHERE vRefCode = ''";
$result = $obj->MySQLSelect($sql);
	
foreach($result as $user)
{
	$code = $generalobj->ganaraterefercode('Rider',$user['iUserId']);	
	#var_dump($code);	
	#var_dump($generalobj->CheckReferralCodeExists($code));
 
	$updateQuery = "UPDATE `register_user` SET `vRefCode`='${code}' WHERE iUserId = ${user['iUserId']}";
	$obj->sql_query($updateQuery);
}
// بررسی تکرار در کد دعوت
// SELECT iUserId, vRefCode, count(*) FROM `register_user` group by vRefCode having count(*) > 1



$sql = "SELECT * FROM `register_driver` WHERE vRefCode = ''";
$result = $obj->MySQLSelect($sql);
	
foreach($result as $user)
{
	$code = $generalobj->ganaraterefercode('Driver',$user['iDriverId']);	
	#var_dump($code);	
	#var_dump($generalobj->CheckReferralCodeExists($code));
 
	$updateQuery = "UPDATE `register_driver` SET `vRefCode`='${code}' WHERE iDriverId = ${user['iDriverId']}";
	//die($updateQuery);
	$obj->sql_query($updateQuery);
}
// بررسی تکرار در کد دعوت
// SELECT iDriverId, vRefCode, count(*) FROM `register_driver` group by vRefCode having count(*) > 1
*/
echo "END";	
	
	
	
	?>