<?
include_once('common.php');
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$email = isset($_POST['vEmail'])?$_POST['vEmail']:'';
$pass = isset($_POST['vPassword'])?$_POST['vPassword']:'';
$npass = $generalobj->encrypt($pass);
$remember = isset($_POST['remember-me'])?$_POST['remember-me']:'';
if($action == 'driver')
{
	$tbl_d = '';
	$tbl = '';
	
	//$fields = '';

	$sql = "SELECT iDriverId, iCompanyId, vName, vLastName, vEmail, eStatus, vCurrencyDriver FROM register_driver WHERE vEmail = '".$email."' AND vPassword = '".$npass."'";
	$db_driver = $obj->MySQLSelect($sql);
	
	$sql = "SELECT iCompanyId, vName, vLastName, vEmail, eStatus from company WHERE vEmail = '".$email."'  AND vPassword = '".$npass."'";
	$db_comp = $obj->MySQLSelect($sql);
		
	if(count($db_driver) > 0)
	{		
		if($db_driver[0]['eStatus'] != 'Deleted')
		{
			$_SESSION["sess_iUserId"]=$db_driver[0]['iDriverId'];
			$_SESSION["sess_iCompanyId"]=$db_driver[0]['iCompanyId'];
			$_SESSION["sess_vName"]=$db_driver[0]['vName'];
			$_SESSION["sess_vLastName"]=$db_driver[0]['vLastName'];
			$_SESSION["sess_vEmail"]=$db_driver[0]['vEmail'];
			$_SESSION["sess_vCurrency"]=$db_driver[0]['vCurrencyDriver'];
			$_SESSION["sess_user"] = "driver";
			
			if(SITE_TYPE=='Demo'){
				$login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('".$_SESSION["sess_iUserId"]."', 'Driver', 'WebLogin','".$_SERVER['REMOTE_ADDR']."')";
				$obj->sql_query($login_sql);
        
        $update_sql = "UPDATE register_driver set tRegistrationDate='".date('Y-m-d H:i:s')."' WHERE iDriverId='" . $_SESSION["sess_iUserId"] . "'";
        $db_update = $obj->sql_query($update_sql);
			}  
			
			if($remember == "Yes")
			{
				setcookie ("member_login_cookie", $email, time()+2592000);
				setcookie ("member_password_cookie", $pass, time()+2592000);
			}
			else
			{
				setcookie ("member_login_cookie", "", time());
				setcookie ("member_password_cookie", "", time());
			}
			echo 2;
			exit;
		} 
		else {
		
			echo 1;
			exit;
		}
	}
	else
	{
		if(count($db_comp) > 0)
		{
			if($db_comp[0]['eStatus'] != 'Deleted')
			{
				$_SESSION["sess_iUserId"]=$db_comp[0]['iCompanyId'];
				$_SESSION["sess_iCompanyId"]=$db_comp[0]['iCompanyId'];
				$_SESSION["sess_vName"]=$db_comp[0]['vName'];
				$_SESSION["sess_vLastName"]=$db_comp[0]['vLastName'];
				$_SESSION["sess_vEmail"]=$db_comp[0]['vEmail'];
				$_SESSION["sess_user"] = "company";
				
				if($remember == "Yes")
				{
					setcookie ("member_login_cookie", $email, time()+2592000);
					setcookie ("member_password_cookie", $pass, time()+2592000);
				}
				else
				{
					setcookie ("member_login_cookie", "", time());
					setcookie ("member_password_cookie", "", time());
				}
				echo 2;
				exit;
			}
			else
			{
				echo 1;
				exit;
			}
			
		}
	}
}
if($action == 'rider')
{
	$tbl = 'register_user';
	$fields = 'iUserId, vName, vEmail, eStatus, vCurrencyPassenger';
	
	$sql = "SELECT $fields FROM $tbl WHERE vEmail = '".$email."' AND vPassword = '".$npass."'";
	$db_login = $obj->MySQLSelect($sql);
	if(count($db_login) > 0)
	{
		if($db_login[0]['eStatus'] != "Deleted"){
			$_SESSION['sess_iUserId']=$db_login[0]['iUserId'];
			$_SESSION["sess_vName"]=$db_login[0]['vName'];
			$_SESSION["sess_vEmail"]=$db_login[0]['vEmail'];
			$_SESSION["sess_user"] = "rider";
			$_SESSION["sess_vCurrency"]=$db_login[0]['vCurrencyPassenger'];
			
			if(SITE_TYPE=='Demo'){
				$login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('".$_SESSION["sess_iUserId"]."', 'Passenger', 'WebLogin','".$_SERVER['REMOTE_ADDR']."')";
				$obj->sql_query($login_sql);
        
			$update_sql = "UPDATE register_user set tRegistrationDate='".date('Y-m-d H:i:s')."' WHERE iUserId='" . $_SESSION["sess_iUserId"] . "'";
			$db_update = $obj->sql_query($update_sql);
			}
			
			if($remember == "Yes")
			{
				setcookie ("member_login_cookie", $vEmail, time()+2592000);
				setcookie ("member_password_cookie", $vPassword, time()+2592000);
			}
			else
			{
				setcookie ("member_login_cookie", "", time());
				setcookie ("member_password_cookie", "", time());
			}
			echo 2; // success registration
			exit;
		}	
		else{
			echo 1;
			exit;
		}
	}
	else
	{
		echo  3; //Invalid combination of username & Password
		exit;
	}
}
exit;
?>