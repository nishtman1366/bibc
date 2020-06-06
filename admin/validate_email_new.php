<?php
	include_once('../common.php');

	$usertype = isset($_REQUEST['utype']) ? $_REQUEST['utype'] : "";
	$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : "";
	
	$ssql1 = "";
	$ssql2 = "";
	if($usertype != ""){
		if($usertype == "driver"){
			$ssql1 = "and iDriverId != '".$uid."'";
		}else if($usertype == "company"){
			$ssql2 = "and iCompanyId != '".$uid."'";
		}
	}
	if(isset($_REQUEST['id']))
	{
			$email=$_REQUEST['id'];
			$sql = "SELECT * FROM company WHERE vEmail = '".$email."'".$ssql." ".$ssql2;
			$db_comp = $obj->MySQLSelect($sql);
			
			$sql = "SELECT * FROM register_driver WHERE vEmail = '".$email."' ".$ssql1;
			$db_driver = $obj->MySQLSelect($sql);
		if(count($db_comp)>0 or count($db_driver)>0)
		{
				if($db_comp[0]['eStatus']=='Deleted' or $db_driver[0]['eStatus']=='Deleted')
				{
						echo 1;
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