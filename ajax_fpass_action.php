<?
	include_once('common.php');
include_once(TPATH_CLASS.'configuration.php');
	include_once('generalFunctions.php');

	$email = isset($_POST['femail'])?$_POST['femail']:'';
	$action = isset($_POST['action'])?$_POST['action']:'';

	//echo SITE_TYPE;	

    // add new by seyyed amir
    if(isset($_GET['new']))
    {
        $status = '';
        $message = '';
        
        if(isset($_POST['vPhone']) && $_POST['vCode'] == '')
        {
            $vphone = htmlentities($_POST['vPhone']);
            $sql = "SELECT * from register_user where vPhone = '".$vphone."' and eStatus != 'Deleted'";
		    $db_users = $obj->MySQLSelect($sql);
            
            if(count($db_users) == 0)
            {
                $status = 'error';
                $message = $langage_lbl['LBL_WRONG_MOBILE_TXT'];
            }
            else
            {
                if(isset($_SESSION['reset_password_time']) && time() - $_SESSION['reset_password_time'] < 3*30)
                {
                    $status = 'error';
                    $message = $langage_lbl['LBL_LOADING_TXT'] ;
                }
                else
                {
                    $_SESSION['reset_password_code'] = rand(1000,9999);

                    $_SESSION['reset_password_user_id'] = $db_users[0]['iUserId'];
                    $_SESSION['reset_password_vphone'] = $db_users[0]['vPhone'];
                    $_SESSION['reset_password_time'] = time();


                    $sms = $langage_lbl['LBL_Confirm_New_Password'] . "\n";
                    $sms .= $_SESSION['reset_password_code'];
                    
                    sendEmeSms($_SESSION['reset_password_vphone'],$sms);

                    $status = 'step2';
                    $message = $langage_lbl['LBL_SMS_SENT_NOTE'];
                    $message .= "\n<br>" . $langage_lbl['LBL_ENTER_NEW_PASSWORD'];
                }
            }
        }
        else
        {
            if($_SESSION['reset_password_vphone'] == $_POST['vPhone'] &&
              $_SESSION['reset_password_code'] == $_POST['vCode'])
            {
                $passwd = $_POST['vPassword'];
                if(strlen($passwd) < 6)
                {
                    $status = 'error';
                    $message = $langage_lbl['LBL_ENTER_MIN_6'];
                }
                else
                {
                    $passwd = $generalobj->encrypt($passwd);
                    $iUserId = $_SESSION['reset_password_user_id'];

                    $sql = "UPDATE register_user SET vPassword = '$passwd' WHERE iUserId = '".$iUserId."'";
                    $db_users = $obj->MySQLSelect($sql);

                    
                    unset($_SESSION['reset_password_code']);

                    unset($_SESSION['reset_password_user_id']);
                    unset($_SESSION['reset_password_vphone']);
                    //unset($_SESSION['reset_password_time']);
                    
                    
                    $status = 'change';
                    $message = $langage_lbl['LBL_CHANGE_PASSWORD_OK_MSG_TXT'];
                }
            }
            else
            {
                $status = 'error';
                $message = $langage_lbl['LBL_VERIFICATION_CODE_INVALID'];
            }
        }
        
        $data['message'] = $message;
	    $data['status'] = $status;
	    $data['data'] = $_POST;
	    echo json_encode($data);
        die();
    }


	if($action == 'driver')
	{
		$sql = "SELECT * from company where vEmail = '".$email."' and eStatus != 'Deleted'";
		$db_login = $obj->MySQLSelect($sql);

		if(count($db_login)>0)
		{
			if(SITE_TYPE != 'Demo'){
				$status = $generalobj->send_email_user("CUSTOMER_FORGETPASSWORD",$db_login);	
			}
			else {
				$status = 1;
			}
			

			if($status == 1)
			{
				$var_msg =  $langage_lbl['LBL_PASSWORD_SENT_SUCCESS'];
				$error_msg = "1";
			}
			else
			{
				$var_msg = $langage_lbl['LBL_ERROR_SEND_PASS'];
				$error_msg = "0";
			}
		}
		else
		{
			$sql = "SELECT * from register_driver where vEmail = '".$email."' and eStatus != 'Deleted'";
			$db_login = $obj->MySQLSelect($sql);
			if(count($db_login)>0)
			{
				if(SITE_TYPE != 'Demo'){
					$status = $generalobj->send_email_user("CUSTOMER_FORGETPASSWORD",$db_login);
				}
				else {
					$status = 1;
				}
				//echo $status;exit;
				if($status == 1)
				{
					$var_msg = $langage_lbl['LBL_PASSWORD_SENT_SUCCESS'];
					$error_msg = "1";
				}
				else
				{
					$var_msg = $langage_lbl['LBL_ERROR_SEND_PASS'];
					$error_msg = "0";
				}
			}
			else
			{
				 $var_msg =  $langage_lbl['LBL_EMAIL_NPT_FOUND'];
				 $error_msg = "0";
			}
		}
		//echo $error_msg;
	}
	if($action == 'rider')
	{
		$sql = "SELECT * from register_user where vEmail = '".$email."' and eStatus != 'Deleted'";
		$db_login = $obj->MySQLSelect($sql);
		if(count($db_login)>0)
		{
			if(SITE_TYPE != 'Demo'){
				$status = $generalobj->send_email_user("CUSTOMER_FORGETPASSWORD",$db_login);
			}
			else {
				$status = 1;
			}
			if($status == 1)
			{
				$var_msg = $langage_lbl['LBL_PASSWORD_SENT_SUCCESS'];
				$error_msg = "1";
			}
			else
			{
				$var_msg = $langage_lbl['LBL_ERROR_SEND_PASS'];
				$error_msg = "0";
			}
		}
		else
		{
			$var_msg = $langage_lbl['LBL_EMAIL_NPT_FOUND'];
			$error_msg = "3";
		}
	}
	$data['msg'] = $var_msg;
	$data['status'] = $error_msg;
	echo json_encode($data);
?>
