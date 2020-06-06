<?
//echo "here";
include '../common.php';
//print_r($_REQUEST);
$sql = "select vPhoneCode from country where vCountryCode = '".$_REQUEST['id']."'";
$db_data = $obj->MySQLSelect($sql);
echo $db_data[0]['vPhoneCode'];
exit;
?>