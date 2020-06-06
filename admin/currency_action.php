<?
	include_once('../common.php');

	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	
	$generalobjAdmin->check_member_login();
	unset($_POST['dataTables-example_length']);
	unset($_POST['submit']);
	$ratio = $_REQUEST['Ratio'];
	$thresholdamount = $_REQUEST['fThresholdAmount'];
	$vSymbol = $_REQUEST['vSymbol'];
	//$iDispOrder = $_REQUEST['iDispOrder'];
	//$eStatus=$_REQUEST['eStatus'];
	$iCurrencyId=$_REQUEST['iCurrencyId'];
	//echo '<pre>';print_r($_POST);echo '</pre>';exit;
	$sql= "select * from currency WHERE eStatus = 'Active' order by iCurrencyId";
  $db_sq = $obj->MySQLSelect($sql);
  //print_r($db_sq);exit;
  
 if(SITE_TYPE=='Demo')
 {
	 header("location:currency.php?success=2");
	exit;
 }
 else
 {
  for($i=0;$i<count($db_sq);$i++)
  {
		//echo '<pre>';print_r($db_sq);echo '</pre>';
   $name=$db_sq[$i]["vName"];
   $j=0;
    $str="UPDATE currency SET ";
    foreach($db_sq as  $arr) {
     //$str.= $arr['vName'].'='.$_POST[$arr['vName']][$i].',';
     $str.= "vSymbol"."='".$vSymbol[$i]."',";
		 $str.= "Ratio"."='".$ratio[$i]."',";
		 $str.= "fThresholdAmount"."='".$thresholdamount[$i]."',";
		 //$str.= "iDispOrder=".$iDispOrder[$i].',';
     //$str.= "eStatus"."='".$eStatus[$i]."',";
     }
   $str=substr_replace($str ," ",-1);
     $id= $db_sq[$i]['iCurrencyId'];
      $str.="where iCurrencyId=".$iCurrencyId[$i];
			//echo $str;exit;
        $db_update = $obj->sql_query($str);
  }
	header("location:currency.php?success=1");
	exit;
 }

?>
