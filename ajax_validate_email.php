<?php
	include_once('common.php');
	
	$ssql="";
	$ssql1="";
	$usertype = $_SESSION['sess_user'];
	if($_REQUEST['uid'] != "" && $usertype == 'company')
	{
		$ssql="and iCompanyId != '".$_REQUEST['uid']."'";
	}
	else if($_REQUEST['uid'] != "" && $usertype == 'driver'){
		$ssql1="and iDriverId != '".$_REQUEST['uid']."'";
	}
	
	if(isset($_REQUEST['id']))
	{
			$email=$_REQUEST['id'];
			$sql = "SELECT vEmail,eStatus FROM company WHERE vEmail = '".$email."' $ssql";
			$db_comp = $obj->MySQLSelect($sql);
			
			$sql = "SELECT vEmail,eStatus FROM register_driver WHERE vEmail = '".$email."' $ssql1";
			$db_driver = $obj->MySQLSelect($sql);
			
			//echo "<pre>";print_r($db_comp);print_r($db_driver);
		if(count($db_comp)>0 or count($db_driver)>0)
		{
				if($db_comp[0]['eStatus']=='Deleted' or $db_driver[0]['eStatus']=='Deleted')
				{
						echo 2;
				}
				else
				{
						echo 0;
				}
		}
	
		else 
		{
			echo 1;
		}
		
	}
?>