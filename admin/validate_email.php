<?php
	include_once('../common.php');
	$iCompanyId=$_REQUEST['iCompanyId'];
	if($iCompanyId !='')
	{
		$ssql=" AND iCompanyId !='".$iCompanyId."'";
	}
	else
	{
		$ssql=" ";
	}
	if(isset($_REQUEST['id']))
	{
			$email=$_REQUEST['id'];
		   $sql = "SELECT * FROM company WHERE vEmail = '".$email."'".$ssql;
			$db_comp = $obj->MySQLSelect($sql);
			
			$sql = "SELECT * FROM register_driver WHERE vEmail = '".$email."' ";
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