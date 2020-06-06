<?php
	ini_set('error_reporting', E_ALL);
	error_reporting(E_ALL);
    require_once('assets/libraries/pubnub/autoloader.php');
	
	$pubnub = new Pubnub\Pubnub("pub-c-e00ce66a-d8e9-4110-a9dc-36ba7e0856fe", "sub-c-18a5a7f2-83a0-11e6-974e-0619f8945a4f");
	// $pubnub = new Pubnub(
    // "pub-c-e00ce66a-d8e9-4110-a9dc-36ba7e0856fe",  ## PUBLISH_KEY
    // "sub-c-18a5a7f2-83a0-11e6-974e-0619f8945a4f",  ## SUBSCRIBE_KEY
    // "sec-c-NGI2ZWJkMjUtMjI2OC00MmFmLTk1YTEtMGI3YTQ5NmMwMjU5",      ## SECRET_KEY
    // false    ## SSL_ON?
// );

// $info = $pubnub->publish('ONLINE_DRIVER_LOC_38', 'Hey World!');

// $message_arr = array();
// $message_arr['MsgType'] = "LocationUpdate";
// $message_arr['iDriverId'] = "156";
// $message_arr['vLatitude'] = $_REQUEST['vLatitude'];
// $message_arr['vLongitude'] = $_REQUEST['vLongitude'];
// $message_arr['ChannelName'] = "ONLINE_DRIVER_LOC_156";
		
		// $message= json_encode($message_arr);
// $info = $pubnub->publish('ONLINE_DRIVER_LOC_156', $message);

// $history = $pubnub->history('ONLINE_DRIVER_LOC_156',100);

$pubnub->subscribe('ONLINE_DRIVER_LOC_156', function ($msg) {
	print_r($msg);
});
  
  // echo "History==<BR><PRE/>";
// print_r($history);
// print_r($info);
?>
