<?php

date_default_timezone_set("Asia/Tehran");

//if(isset($_GET['timezone']))
//{
//    echo date_default_timezone_get();
//    die(date("H:i:s"));
//}

function customError($errno, $errstr,$file,$line,$context) {
  echo "<b>Error:</b> [$errno] $errstr<br>";
  echo "<b>File:</b> $file Line: [$line]<br>";
  //print_r($context);

  //echo "Ending Script";
  //die();
    flush();
    ob_flush();
}

if(isset($ISDEBUG) && $ISDEBUG == true)
{
	error_reporting(E_ALL);
    ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
    set_error_handler("customError");
}
else
{
	error_reporting(0);
	ini_set('display_errors',0);
	ini_set('display_startup_errors',0);
}
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);
//error_reporting(0);
//$host_arr = array();  // This is for online server setting
//$host_arr = explode(".",$_SERVER["HTTP_HOST"]); // This is for online server setting
//$host_system = $host_arr[0]; // This is for online server setting

defined( '_TEXEC' ) or die( 'Restricted access' );
$parts = explode( DS, TPATH_BASE );
define( 'TPATH_ROOT', TPATH_BASE );
define( 'TPATH_CLASS', TPATH_ROOT.DS.'assets'.DS.'libraries/' );


//include('db.php');



define('PAYPAL_CLIENT_ID', 'AXE55Ggx7B1NpuhxfmKTcYipHIen2Lc1l9ZTU5Qt-4LbTpNmRm0vqCivgr1xkJF5uvg5rrzDwvB_30U-'); // Paypal client id
define('PAYPAL_SECRET', 'EMRwFwWhwXOQbD085uJN-3lugC00D2A2OGH-jQkowzwqQGiY14kwnsxrEuOu0dXmbZZ_xAR547Q1tghd'); // Paypal secret


#Payment Option Settings
$date_before = date('Y-m-d');
$date_new = date('Y-m-d 00:00:00', strtotime('-1 week', strtotime($date_before)));
define('WEEK_DATE',$date_new);

define('SITE_TYPE','Live'); //Live  //Demo
define('PAYMENT_OPTION','Manual');
define('SITE_COLOR','#1fbad6');
#define('PAYMENT_OPTION','PayPal');
#define('PAYMENT_OPTION','Contact');
/*Language Label*/
if(!isset($_SESSION['sess_lang']) || $_SESSION['sess_lang']==""){
	$_SESSION['sess_lang']=$generalobj->get_default_lang();
}

    $sql="select vLabel,vValue from language_label where vCode='".$_SESSION['sess_lang']."'";
    $db_lbl=$obj->MySQLSelect($sql);

    foreach ($db_lbl as $key => $value) {
    	$langage_lbl[$value['vLabel']] = $value['vValue'];
}

/*Language Label Other*/
$sql="select vLabel,vValue from language_label_other where vCode='".$_SESSION['sess_lang']."'";
$db_lbl=$obj->MySQLSelect($sql);
foreach ($db_lbl as $key => $value) {
	$langage_lbl[$value['vLabel']] = $value['vValue'];
//	$langage_lbl[$value['vLabel']] = $value['vValue']."  <span style='font-size:9px;'>".$value['vLabel'].'</span>';
}

$sql="select vLabel,vValue from language_label where vCode='EN'";
$db_lbl_admin=$obj->MySQLSelect($sql);

foreach ($db_lbl_admin as $key => $value) {
    	$langage_lbl_admin[$value['vLabel']] = $value['vValue'];
//	$langage_lbl[$value['vLabel']] = $value['vValue']."  <span style='font-size:9px;'>".$value['vLabel'].'</span>';
}

/*Language Label Other*/
$sql="select vLabel,vValue from language_label_other where vCode='EN'";
$db_lbl_admin=$obj->MySQLSelect($sql);

foreach ($db_lbl_admin as $key => $value) {

    	$langage_lbl_admin[$value['vLabel']] = $value['vValue'];
    	//$langage_lbl_admin[$value['vLabel']] = $value['vValue']."  <span style='font-size:9px;'>".$value['vLabel'].'</span>';
}



define('RIIDE_LATER','YES');
define('PROMO_CODE','YES');
$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
$WALLET_ENABLE = $generalobj->getConfigurations("configurations","WALLET_ENABLE");
$REFERRAL_SCHEME_ENABLE = $generalobj->getConfigurations("configurations","REFERRAL_SCHEME_ENABLE");

// define('Datatablelang',' "lengthMenu": "نمایش دادن _MENU_ رکورد در هر صفحه",
            // "zeroRecords": "چیزی پیدا نشد - متاسف",
            // "info": "صفحه نمایش _PAGE_ از _PAGES_",
            // "infoEmpty": "هیچ ثبتی در دسترس",
            // "infoFiltered": "(فیلتر از _MAX_ تعداد کل رکوردها)"');

	define('Datatablelang','
	"decimal":        "",
    "emptyTable":     "'.$langage_lbl['LBL_DATATABLE_NO_DATA'].'",
    "info":           "'.$langage_lbl['LBL_DATATABLE_SHOWING'].' _START_ '.$langage_lbl['LBL_DATATABLE_TO'].' _END_ '.$langage_lbl['LBL_DATATABLE_FROM'].' _TOTAL_ '.$langage_lbl['LBL_DATATABLE_ENTRIES'].'",
    "infoEmpty":      "'.$langage_lbl['LBL_DATATABLE_SHOWING'].' 0 '.$langage_lbl['LBL_DATATABLE_TO'].' 0 '.$langage_lbl['LBL_DATATABLE_OF'].' 0 '.$langage_lbl['LBL_DATATABLE_ENTRIES'].'",
    "infoFiltered":   "('.$langage_lbl['LBL_DATATABLE_FILTEREDFROM'].' _MAX_ '.$langage_lbl['LBL_DATATABLE_TOTALENTRIES'].')",
    "infoPostFix":    "",
    "thousands":      ",",
    "lengthMenu":     "'.$langage_lbl['LBL_DATATABLE_SHOW'].' _MENU_ '.$langage_lbl['LBL_DATATABLE_ENTRIES'].'",
    "loadingRecords": "'.$langage_lbl['LBL_DATATABLE_LOADING'].'",
    "processing":     "'.$langage_lbl['LBL_DATATABLE_PROCESSING'].'",
    "search":         "'.$langage_lbl['LBL_DATATABLE_SEARCH'].'",
    "zeroRecords":    "'.$langage_lbl['LBL_NO_MATCH_RECORD'].'",
    "paginate": {
        "first":      "'.$langage_lbl['LBL_DATATABLE_FIRST'].'",
        "last":       "'.$langage_lbl['LBL_DATATABLE_LAST'].'",
        "next":       "'.$langage_lbl['LBL_DATATABLE_NEXT'].'",
        "previous":   "'.$langage_lbl['LBL_DATATABLE_PREVIOUS'].'"
    },
	');
?>
