<?php
/**
* Sending Push Notification
*/

function send($message,$dddd)
{
  global $obj;

  switch ($message['tokenId']) {
    case '/topics/global':
    {
        $ios_tokens_drivers = array();
        $ios_tokens_passengers = array();

        $sql1 = "SELECT * FROM `register_driver` WHERE 1 limit 5000";
        if (isset($dddd)) {
          $sql1 = "SELECT * FROM `register_driver` WHERE iCompanyId = " . $dddd . "" . " limit 5000";
        }

        $res1  = $obj->MySQLSelect($sql1);
        if (count($res1) > 0) {
          foreach ($res1 as $driver) {
              if ($driver['eDeviceType'] == "Ios") {
                  array_push($ios_tokens_drivers, $driver['iGcmRegId']);
              }
          }
        }

        $sql1 = "SELECT * FROM `register_user` WHERE 1 limit 5000";
        $res2  = $obj->MySQLSelect($sql1);
        if (count($res2) > 0) {
          foreach ($res2 as $driver) {
              if ($driver['eDeviceType'] == "Ios") {
                  array_push($ios_tokens_passengers, $driver['iGcmRegId']);
              }
          }
        }

        //send for ios devices
        $result1 = sendApplePushNotification(1,$ios_tokens_drivers,$message,$message['title'],0);
        $result2 = sendApplePushNotification(0,$ios_tokens_passengers,$message,$message['title'],0);

        //send for android devices
        $result3 = 3;
        $fcmData = $message;
        $res = sendFCM($message['tokenId'] ,$fcmData);
        $resObj = json_decode($res,true);
        if(isset($resObj['message_id']))
        {
            $result3 = 1;
        }
        else
        {
            $result3 = 3;
        }

        if ($result2 == 1 && $result1 == 1 && $result3 == 1) {

          return 1;
        }
        return 3;
    }
    break;
    case '/topics/company':
    {
        $sql = "SELECT * FROM `register_driver` WHERE iCompanyId = " . $dddd . ""." limit 5000";
        $res  = $obj->MySQLSelect($sql);
        if (count($res) > 0) {

          //send for ios devices
          $ios_tokens = array();
          foreach ($res as $driver) {
              if ($driver['eDeviceType'] == "Ios") {
                  array_push($ios_tokens, $driver['iGcmRegId']);
              }
          }
          $result1 = sendApplePushNotification(1,$ios_tokens,$message,$message['title'],0);

          //send for android devices
          $result2 = 3;
          $fcmData = $message;
          $res = sendFCM($message['tokenId'] ,$fcmData);
          $resObj = json_decode($res,true);
          if(isset($resObj['message_id']))
          {
              $result2 = 1;
          }
          else
          {
              $result2 = 3;
          }

          if ($result2 == 1 && $result1 == 1) {

            return 1;
          }
          return 3;
        }
        else {
          return 3;
        }
    }
    break;

    case '/topics/driver':
    {
        $sql = "SELECT * FROM `register_driver` WHERE 1 limit 5000";
        $res  = $obj->MySQLSelect($sql);
        if (count($res) > 0) {

          //send for ios devices
          $ios_tokens = array();
          foreach ($res as $driver) {
              if ($driver['eDeviceType'] == "Ios") {
                  array_push($ios_tokens, $driver['iGcmRegId']);
              }
          }
          $result1 = sendApplePushNotification(1,$ios_tokens,$message,$message['title'],0);

          //send for android devices
          $result2 = 3;
          $fcmData = $message;
          $res = sendFCM($message['tokenId'] ,$fcmData);
          $resObj = json_decode($res,true);
          if(isset($resObj['message_id']))
          {
              $result2 = 1;
          }
          else
          {
              $result2 = 3;
          }

          if ($result2 == 1 && $result1 == 1) {

            return 1;
          }
          return 3;
        }
        else {
          return 3;
        }
    }
    break;

    case '/topics/passenger':
    {
        $sql = "SELECT * FROM `register_user` WHERE 1 limit 5000";
        $res  = $obj->MySQLSelect($sql);
        if (count($res) > 0) {

          //send for ios devices
          $result1 = 1;
          $ios_tokens = array();
          foreach ($res as $driver) {
              if ($driver['eDeviceType'] == "Ios") {
                  array_push($ios_tokens, $driver['iGcmRegId']);
              }
          }

          $result1 = sendApplePushNotification(0,$ios_toks,$message,$message['title'],0);

          //send for android device
          $result2 = 3;
          $fcmData = $message;
          $res = sendFCM($message['tokenId'] ,$fcmData);
          $resObj = json_decode($res,true);
          if(isset($resObj['message_id']))
          {
              $result2 = 1;
          }
          else
          {
              $result2 = 3;
          }

          if ($result2 == 1 && $result1 == 1) {

            return 1;
          }
          return 3;
        }
        else {
          return 3;
        }
    }
    break;

    case '/topics/indivisual':

        $iGcmRegId = $message['iGcmRegId'];
        if ($iGcmRegId == '') {

          return 3;
        }

        $sql = "SELECT * FROM `register_driver` WHERE iGcmRegId='$iGcmRegId'";
        $res  = $obj->MySQLSelect($sql);

        if (count($res) > 0) {

          if ($res[0]['eDeviceType'] == "Android") {

            $fcmData = $message;
            $res = sendFCM($message['tokenId'] ,$fcmData);
            $success = 3;
            $resObj = json_decode($res,true);
            if(isset($resObj['message_id']))
            {
              $success = 1;
            }
            else
            {
              $success = 3;
            }
            return $success;
          }
          else if ($res[0]['eDeviceType'] == "Ios")
          {
              //echo '<pre>'; print_r($res[0]['eDeviceType']); exit;
              $deviceTokens = array();
              array_push($deviceTokens, $iGcmRegId);
              $result = sendApplePushNotification(1,$deviceTokens,$message,$message['title'],0);
              return $result;
          }
      }
      default:
      break;
    }
  }

  function sendFCM($tokenId,$fcmData,$fcmNotification = '',$ttl_s='')
  {
    global $generalobj,$obj;
    $FCM_API_ACCESS_SERVER_KEY=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_GCM_API_KEY");

    $registrationIDs = array(
      $tokenId,
    );

    $fcmFields = array(
      'to' => $tokenId,
      'priority' => 'high',
      'data'         => $fcmData
    );

    if($fcmNotification !== '')
    {
      $fcmFields['notification'] = $fcmNotification;
    }

    if($ttl_s !== '')
    {
      $fcmFields['time_to_live'] = $ttl_s;
    }

    $headers = array(
      'Authorization: key=' . $FCM_API_ACCESS_SERVER_KEY,
      'Content-Type: application/json'
    );

    //echo '<pre>'; print_r($fcmFields); exit;

    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
    $result = curl_exec($ch );
    curl_close( $ch );
    return $result;
  }

  function send_notification($registatoin_ids, $message,$filterMsg = 0) {

    // include config
    // include_once './config.php';
    global $generalobj,$obj;
    $GOOGLE_API_KEY=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_GCM_API_KEY");
    // Set POST variables
    //$url = 'https://android.googleapis.com/gcm/send';
    $url = 'https://fcm.googleapis.com/fcm/send';

    $fields = array(
      'registration_ids' => $registatoin_ids,
      'priority' => 'high',
      'data' => $message,
    );

    $headers = array(
      'Authorization: key=' .$GOOGLE_API_KEY,
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

    $finalFields = json_encode($fields,JSON_UNESCAPED_UNICODE);


    if($filterMsg == 1){
      $finalFields= stripslashes(preg_replace("/[\n\r]/","",$finalFields));
    }


    curl_setopt($ch, CURLOPT_POSTFIELDS, $finalFields);

    require_once(TPATH_CLASS .'savar/class.telegrambot.php');

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
      $returnArr['ERROR'] =  curl_error($ch);
      echo json_encode($returnArr);
      exit;
    }

    // Close connection
    curl_close($ch);
    return $result;
  }

  function sendApplePushNotification($PassengerToDriver = 1,$deviceTokens,$message,$alertMsg,$filterMsg){

    global $generalobj,$obj;

    $passphrase = $generalobj->getConfigurations("configurations","IPHONE_PEM_FILE_PASSPHRASE");
    $APP_MODE = $generalobj->getConfigurations("configurations","APP_MODE");

    $prefix = "";
    $url_apns ='ssl://gateway.sandbox.push.apple.com:2195';
    if($APP_MODE == "Production"){
      $prefix = "PRO_";
      $url_apns ='ssl://gateway.push.apple.com:2195';
    }

    if($PassengerToDriver == 1){
      $name=$generalobj->getConfigurations("configurations",$prefix."PARTNER_APP_IPHONE_PEM_FILE_NAME");
    }else{
      $name=$generalobj->getConfigurations("configurations",$prefix."PASSENGER_APP_IPHONE_PEM_FILE_NAME");
    }

    $ctx = stream_context_create();

    stream_context_set_option($ctx, 'ssl', 'local_cert', $name);

    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client(
      $url_apns, $err,
      $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

      $LogMessage = array('code' => "APPLE PUSH NOTIFICATION");
      $LogMessage = array('TOKEN' => $deviceTokens);

      if (!$fp){

        $returnArr['Action'] = "0";
        $returnArr['message'] = "APNS_FAILED";
        $returnArr['ERROR'] =  PHP_EOL;

        echo json_encode($returnArr);

        $LogMessage["RETURN"] = $returnArr;
        //if(function_exists('TLOG')) TLOG($LogMessage);

        return 3;
        //exit;
        // exit("Failed to connect: $err $errstr" . PHP_EOL);
      }

      // Create the payload body
      $body['aps'] = array(
        'alert' => $alertMsg,
        'content-available' => 1,
        'body'  => $message,
        'sound' => 'notification.mp3'
      );

      // Encode the payload as JSON
      $payload = json_encode($body,JSON_UNESCAPED_UNICODE);
      //        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
      if($filterMsg == 1){
        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
      }

      for($device=0; $device < count($deviceTokens); $device++){
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
      }

      $LogMessage["BODY"] = $body['aps'];
      $LogMessage["RES"] = $result;

      fclose($fp);
      return 1;
    }

    function Logger($data)
    {
      $text = '';

      if(is_array($data) || is_object($data))
      $text = print_r($data, true);
      else
      $text = $data;

      file_put_contents("notification_test.txt", $text . "\r\n................................\r\n",FILE_APPEND);
    }

    ?>
