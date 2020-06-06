<?
include_once("common.php");

$dist_fare = isset($_REQUEST['dist_fare'])?$_REQUEST['dist_fare']:'';
$time_fare = isset($_REQUEST['time_fare'])?$_REQUEST['time_fare']:'';

if($dist_fare != '' && $time_fare != "")
{
	$priceRatio = 1;
	
	$sql = "select * from vehicle_type";
	$db_vType = $obj->MySQLSelect($sql);
	
	$cont = '';
	$cont .= '<ul>';
    for($i=0;$i<count($db_vType);$i++){
		
		$Minute_Fare =round($db_vType[$i]['fPricePerMin']*$time_fare,2) * $priceRatio;
		$Distance_Fare =round($db_vType[$i]['fPricePerKM']*$dist_fare,2)* $priceRatio;
		$iBaseFare =round($db_vType[$i]['iBaseFare'],2)* $priceRatio;
		$total_fare=$iBaseFare+$Minute_Fare+$Distance_Fare;
		
		$cont .= '<li><label>'.$db_vType[$i]['vVehicleType']." - ".$db_vType[$i]['eType'].'<img src="assets/img/question-icon.jpg" alt="" title="'.$langage_lbl['LBL_APPROX_DISTANCE_TXT'].' '.$langage_lbl['LBL_FARE_ESTIMATE_TXT'].'"><b>'.$generalobj->trip_currency($total_fare).'</b></label></li>';		
    }
	/*$cont .= '<li><p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since.</p></li>';*/
	if(!isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == "") {
		$cont .= '<li><strong><a href="sign-up-rider"><em>'.$langage_lbl['LBL_RIDER_SIGNUP1_TXT'].'</em></a></strong></li>';
	}
	$cont .= '</ul>';
    echo $cont; exit;
}
?>
