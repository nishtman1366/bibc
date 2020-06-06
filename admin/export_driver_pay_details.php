<?php            
include_once('../common.php');
//ini_set('display_errors',1); 
//error_reporting(E_ALL);
$tbl_name 	= 'trips';
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$abc = 'admin,company';

#echo "<pre>"; print_r($_REQUEST); exit;
//-----------------------------------------------

$action = $_REQUEST['action'];
$ssql = "";
$startDate = date("Y-m-d",strtotime($_REQUEST['prev_start']));
$endDate = date("Y-m-d",strtotime($_REQUEST['prev_end']));
$iCountryCode = $_REQUEST['prev_country'];

if($action != '' && $action == "export")
{
	//echo "come"; die;
	if($startDate!=''){
		$ssql.=" AND Date(tEndDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(tEndDate) <='".$endDate."'";
	}
	
	$sql = "select register_driver.iDriverId,eDriverPaymentStatus,concat(vName,' ',vLastName) as dname,vCountry,vBankAccountHolderName,vAccountNumber,vBankLocation,vBankName,vBIC_SWIFT_Code from register_driver 
	LEFT JOIN trips ON trips.iDriverId=register_driver.iDriverId
	WHERE vCountry = '".$iCountryCode."' AND eDriverPaymentStatus='Unsettelled' $ssql GROUP BY register_driver.iDriverId";
	$db_payment = $obj->MySQLSelect($sql);
	
	for($i=0;$i<count($db_payment);$i++) {
		$db_payment[$i]['cashPayment'] = $generalobjAdmin->getAllCashCountbyDriverId($db_payment[$i]['iDriverId'],$ssql);
		$db_payment[$i]['cardPayment'] = $generalobjAdmin->getAllCardCountbyDriverId($db_payment[$i]['iDriverId'],$ssql);
		$db_payment[$i]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($db_payment[$i]['iDriverId'],$ssql);
	}
	
	#echo "<pre>";print_r($db_payment);exit;
$header .= "Driver Code"."\t";
$header .= "Driver Name."."\t";
$header .= "Driver Account Name"."\t";
$header .= "Bank Name"."\t";
$header .= "Account Number"."\t";
$header .= "Sort Code"."\t";
$header .= "Total Cash Payment"."\t";
$header .= "Total Card Payment"."\t";
$header .= "Amount to Transfer"."\t";
$header .= "Driver Payment Status"."\t";


for($j=0;$j<count($db_payment);$j++)
{
    $data .= $db_payment[$j]['iDriverId']."\t";
    $data .= $db_payment[$j]['dname']."\t";
    $data .= ($db_payment[$i]['vBankAccountHolderName'] != "")?$db_payment[$i]['vBankAccountHolderName']:'---';
	$data .= "\t";
	$data .= ($db_payment[$i]['vBankName'] != "")?$db_payment[$i]['vBankName']:'---';
	$data .= "\t";
	$data .= ($db_payment[$i]['vAccountNumber'] != "")?$db_payment[$i]['vAccountNumber']:'---';
	$data .= "\t";
	$data .= ($db_payment[$i]['vBIC_SWIFT_Code'] != "")?$db_payment[$i]['vBIC_SWIFT_Code']:'---';
	$data .= "\t";
    $data .= $db_payment[$j]['cashPayment']."\t";
    $data .= $db_payment[$j]['cardPayment']."\t";
    $data .= $db_payment[$j]['transferAmount']."\t";
	$data .= $db_payment[$j]['eDriverPaymentStatus']."\n";
}
}
$data = str_replace( "\r" , "" , $data );
#echo "<br>".$data; exit;
ob_clean();
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=payment_reports.xls");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$data";
exit;
?>
