<?php

include_once('common.php');

exec_cron();
sleep(30);
exec_cron();

function exec_cron()
{
    global $generalobj,$obj,$tconfig;
    $time_left = $generalobj->getConfigurations("configurations","TIME_LEFT_FOR_EXEC_CAB_BOOKING");
    if($time_left != '')
        $time_left = 5;

    $sql = "SELECT * FROM `cab_booking` WHERE (eStatus <> 'Completed' AND eStatus <> 'Cancel') AND TIMESTAMPDIFF(MINUTE, NOW(),dBooking_date) < {$time_left} AND TIMESTAMPDIFF(MINUTE, NOW(),dBooking_date) > 0";
    $res = $obj->MySQLSelect($sql);

    if(count($res) > 0 )
    {
        foreach($res as $cab_booking)
        {
            $iCabBookingId = $cab_booking['iCabBookingId'];
            //echo "Booking ID: " . $iCabBookingId . "<br>\r\n";
            $uri = "http://www.k68.ir/app/webservice.php?type=startBooking&cabBookingId=" .$iCabBookingId;
            file_put_contents(__DIR__ . "/dispatcher_cronjob.txt",date('Y-m-d H:i:s')."    ".$uri);
            ////echo $uri . "<br>\r\n";
            $resdata = file_get_contents($uri);
            //echo $resdata  . "<br>\r\n";
            //file_put_contents(__DIR__ . "/dispatcher_cronjob.txt","\r\nURI: ".$uri,FILE_APPEND);
            //file_put_contents(__DIR__ . "/dispatcher_cronjob.txt","\r\nRES: ".$resdata,FILE_APPEND);
            //echo "<br>\r\n";
        }
    }
}
