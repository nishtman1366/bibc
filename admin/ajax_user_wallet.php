<?php
	include_once('../common.php');
		
	if(isset($_REQUEST['name']))
	{
	
		if($_REQUEST['name'] != "")
		{		
			if($_REQUEST['name'] == 'Driver'){
				
				$user_name = $_REQUEST['name'];
				$sql = "SELECT * FROM register_driver"; 
				$db_comp = $obj->MySQLSelect($sql);		
				 echo "<option value=''>Search By Driver type</option>";
				for($i=0;$i<count($db_comp);$i++){	
					
					echo "<option value=".$db_comp[$i]['iDriverId'].">".$db_comp[$i]['vName']." ".$db_comp[$i]['vLastName']."</option>";			
					
				}
				 exit;
				
				
			}else{				
				
				$sql = "SELECT * FROM register_user ";
				$db_register_user = $obj->MySQLSelect($sql);
					
				 echo "<option value=''>Search By Passanger type</option>";
				for($i=0;$i<count($db_register_user);$i++){		
					
					echo "<option value=".$db_register_user[$i]['iUserId'].">".$db_register_user[$i]['vName']." ".$db_register_user[$i]['vLastName']."</option>";			
					
				}
				 exit;
			}	
		}
	}
	
		
		
		
	 
		
	
?>