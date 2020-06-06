<?
include_once("../common.php");
$phone = isset($_REQUEST['phone'])?$_REQUEST['phone']:'';
$vehicleId = isset($_REQUEST['vehicleId'])?$_REQUEST['vehicleId']:'';
if($phone != '')
{
	$sql = "select * from register_user where vPhone = '".$phone."' LIMIT 1";
	$db_model = $obj->MySQLSelect($sql);
	$cont = '';
    for($i=0;$i<count($db_model);$i++){
		$cont .= $db_model[$i]['vName'].":";
		$cont .= $db_model[$i]['vLastName'].":";
		$cont .= $db_model[$i]['vEmail'].":";
		$cont .= $db_model[$i]['iUserId'].":";
		$cont .= $db_model[$i]['vAddress'].":";
		$cont .= $db_model[$i]['vDescription'].":";
    }
    echo $cont; exit;
}

if($vehicleId != '')
{
	$sql = "select * from vehicle_type where iVehicleTypeId = '".$vehicleId."' LIMIT 1";
	$db_model = $obj->MySQLSelect($sql);
	$cont = '';
    for($i=0;$i<count($db_model);$i++){
		$cont .= $db_model[$i]['iBaseFare'].":";
		$cont .= $db_model[$i]['fPricePerKM'].":";
		$cont .= $db_model[$i]['fPricePerMin'].":";
		$cont .= $db_model[$i]['iMinFare'];
    }
    echo $cont; exit;
}
?>
