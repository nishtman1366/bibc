<?php include_once("common.php");
//echo "<pre>";print_r($_POST);exit;
if($_POST)
{
	/*if($_POST['vPassword'] != $_POST['vRPassword'])
	{
		$generalobj->getPostForm($_POST,"Password doesn't match","SignUp");
		exit;
	}*/
	$msg= $generalobj->checkDuplicateFront('vEmail', "register_user" , Array('vEmail'),$tconfig["tsite_url"]."sign-up_rider.php?error=1&var_msg=".$langage_lbl['LBL_EMAIL_ALREADY_EXIST'], $langage_lbl['LBL_EMAIL_ALREADY_EXIST'],"" ,"");


	$eReftype = "Rider";
	$Data['vRefCode'] = $generalobj->ganaraterefercode($eReftype);

	$Data['iRefUserId'] = $_POST['iRefUserId'];
	$Data['eRefType'] = $_POST['eRefType'];
	$Data['vName'] = $_POST['vName'];
	$Data['vLang'] = $_POST['vLang'];
	$Data['vLastName'] = $_POST['vLastName'];
	//$Data['vLoginId'] = "";
	$Data['vPassword'] = $generalobj->encrypt($_REQUEST['vPassword']);
	$Data['vEmail'] = $_POST['vEmail'];
	$Data['vPhone'] = $_POST['vPhone'];
	$Data['vCountry']=$_POST['vCountry'];
	$Data['vPhoneCode'] = $_POST['vPhoneCode'];
	$Data['vZip'] = $_POST['vZip'];
	//$Data['iDriverVehicleId	'] = "";
	$Data['vInviteCode'] = $_POST['vInviteCode'];
	$Data['vCurrencyPassenger'] = $_POST['vCurrencyPassenger'];
	$Data['dRefDate'] =  Date('Y-m-d H:i:s');

	if(SITE_TYPE=='Demo')
	{
		$Data['eStatus'] = 'Active';
	}
    
    
    if($_POST['vRefCode'] != "" && !empty($_POST['vRefCode'])){
        $vInviteCode 	  = isset($_REQUEST["vRefCode"]) ? $_REQUEST["vRefCode"] : '';
        $check_inviteCode ="";
        if($vInviteCode != ""){
            $check_inviteCode = $generalobj->validationrefercode($vInviteCode);
            if($check_inviteCode == "" || $check_inviteCode == "0" || $check_inviteCode == 0){
                
            }else{
                $inviteRes= explode("|",$check_inviteCode);
                $Data['iRefUserId'] = $inviteRes[0];
                $Data['eRefType'] = $inviteRes[1];
            }
        }
	}
    

	$id = $obj->MySQLQueryPerform("register_user",$Data,'insert');
	$eFor = "Referrer";
	$tDescription = "Referal amount credit ".$REFERRAL_AMOUNT." into your account";
	$dDate = Date('Y-m-d H:i:s');
	$ePaymentStatus = "Unsettelled";
	$REFERRAL_AMOUNT; 
    
	
	if($id != "")
	{
		$_SESSION['sess_iUserId'] = $id;
        $_SESSION["sess_vName"] = $Data['vName'].' '.$Data['vLastName'];
		$_SESSION["sess_company"] = " ";
        $_SESSION["sess_vEmail"] = $Data['vEmail'];
		$_SESSION["sess_user"] = "rider";
		$_SESSION["sess_vCurrency"] = $Data['vCurrencyPassenger'];
		$maildata['EMAIL'] = $_SESSION["sess_vEmail"];
        $maildata['NAME'] = $_SESSION["sess_vName"];
        $maildata['PASSWORD'] = $_REQUEST['vPassword'];
	    $generalobj->send_email_user("MEMBER_REGISTRATION_USER",$maildata);
		header("Location:profile_rider.php");
		exit;
	}
	}
?>
