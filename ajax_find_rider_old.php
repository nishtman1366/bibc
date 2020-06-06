<?php
include_once("common.php");


$res['status'] = 'error';
$res['message'] = 'error';


if(isset($_SESSION['sess_iUserId']) == false || $_SESSION['sess_iUserId'] == '')
{
    $res['message'] = 'Please Login..' ;
    echo json_encode($res);
    die();
}

$phone = isset($_REQUEST['phone'])?$_REQUEST['phone']:'';
$vehicleId = isset($_REQUEST['vehicleId'])?$_REQUEST['vehicleId']:'';
if($phone != '')
{
	$sql = "select * from register_user where vPhone = '".$phone."' LIMIT 1";
	$db_user = $obj->MySQLSelect($sql);
	$cont = '';
    $data = array();

    if(count($db_user) > 0)
    {
      $data['vName']     = $db_user[0]['vName'];
		$data['vLastName'] = $db_user[0]['vLastName'];
		$data['vEmail']    = $db_user[0]['vEmail'];
		$data['iUserId']   = $db_user[0]['iUserId'];
    $data['vAddress']    = $db_user[0]['vAddress'];
		$data['vDescription']   = $db_user[0]['vDescription'];

        $sql = "SELECT iTripId,tStartLat,tStartLong,tEndLat,tEndLong,vDriverStartLocation,vDriverEndLocation FROM `trips` WHERE iUserId = {$data['iUserId']} AND iActive = 'Finished' ORDER BY iTripId DESC LIMIT 10";

	    $db_trips_loc = $obj->MySQLSelect($sql);

        $data['trips'] = array();

        if(count($db_trips_loc) > 0)
        {
            $data['trips'] = $db_trips_loc;
        }

        $res['status'] = 'ok';
        $res['message'] = $data;
        echo json_encode($res);
        die();
    }
    else
    {
        $res['message'] = 'not found ' . $_REQUEST['phone'];
        echo json_encode($res);
        die();
    }

}
else
{
    $res['message'] = 'Please input phone...';
    echo json_encode($res);
    die();
}
?>
