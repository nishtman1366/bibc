<?
include_once("../common.php");
$phone = isset($_REQUEST['phone'])?$_REQUEST['phone']:'';
//$vehicleId = isset($_REQUEST['vehicleId'])?$_REQUEST['vehicleId']:'';
if($phone != '')
{
	$sql = "select * from register_driver where vPhone like '%$phone%' or vName LIKE '%$phone%' or vLastName LIKE '%$phone%'";
	$db_model = $obj->MySQLSelect($sql);
	$cont = '';
    for($i=0;$i<count($db_model);$i++){
		$cont .= $db_model[$i]['vName'].":";
		$cont .= $db_model[$i]['vLastName'].":";
		$cont .= $db_model[$i]['iDriverId'].":~";
    }


    echo $cont; exit;
}

?>
