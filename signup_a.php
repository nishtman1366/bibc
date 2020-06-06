<?php include_once("common.php");
require_once(TPATH_CLASS .'savar/jalali_date.php');

$mdate = jalali_to_gregorian($_POST['vYear'],$_POST['vMonth'],$_POST['vDay']);
//echo "<pre>";print_r($mdate); exit;
if($_POST)
{
	$user_type=$_POST['user_type'];
	if($user_type=='driver'){
		$table_name="register_driver";
	}else{
		$table_name="company";
	}
	$msg= $generalobj->checkDuplicateFront('vEmail', 'company' , Array('vEmail'),$tconfig["tsite_url"]."sign-up.php?error=1&var_msg=".$langage_lbl['LBL_EMAIL_ALREADY_EXIST'], $langage_lbl['LBL_EMAIL_ALREADY_EXIST'],"" ,"");

	$msg= $generalobj->checkDuplicateFront('vEmail', 'register_driver' , Array('vEmail'),$tconfig["tsite_url"]."sign-up.php?error=1&var_msg=".$langage_lbl['LBL_EMAIL_ALREADY_EXIST'], $langage_lbl['LBL_EMAIL_ALREADY_EXIST'],"" ,"");

	if($user_type=='driver'){
		
		$eReftype = "Driver";
		
		$Data['vRefCode'] = $generalobj->ganaraterefercode($eReftype);
		 $Data['iRefUserId'] = $_POST['iRefUserId'];
		 $Data['eRefType'] = $_POST['eRefType']; 
		 $Data['dRefDate']=Date('Y-m-d H:i:s');


	}

	// echo "<pre>";print_r($Data); exit;
	
	$Data['vName'] = $_POST['vFirstName'];
	$Data['vLastName'] = $_POST['vLastName'];
	$Data['vLang'] = $_SESSION['sess_lang'];
	$Data['vPassword'] = $generalobj->encrypt($_REQUEST['vPassword']);
	$Data['vEmail'] = $_POST['vEmail'];
	//$Data['dBirthDate'] = $_POST['vYear'].'-'.$_POST['vMonth'].'-'.$_POST['vDay'];
	$Data['dBirthDate'] = $mdate[0].'-'.$mdate[1].'-'.$mdate[2];
	$Data['vPhone'] = $_POST['vPhone'];
	$Data['vCaddress'] = $_POST['vCaddress'];
	$Data['vCadress2'] = $_POST['vCadress2'];
	$Data['vCity'] = $_POST['vCity'];
	$Data['vZip'] = $_POST['vZip'];
	$Data['vCountry'] = $_POST['vCountry'];
	$Data['vCode'] = $_POST['vCode'];
	$Data['vBackCheck'] = $_POST['vBackCheck'];
	$Data['vInviteCode'] = $_POST['vInviteCode'];
	$Data['vFathersName'] = $_POST['vFather'];
	$Data['vCompany'] = $_POST['vCompany'];
	$Data['tRegistrationDate']=Date('Y-m-d H:i:s');
	
	if(SITE_TYPE=='Demo')
	{
		$Data['eStatus'] = 'Active';
	}
	

	if($user_type=='driver')
	{
		$table='register_driver';
		$Data['vCurrencyDriver'] = $_POST['vCurrencyDriver'];
		$user_type='driver';
		$Data['iCompanyId'] = 1;
	}
	else
	{
		$table='company';
		$user_type='company';
	}
	//echo "<pre>";print_r($Data); exit;
	$id = $obj->MySQLQueryPerform($table,$Data,'insert');

	// user_wallet table insert data
	$eFor = "Referrer";
	$tDescription = "Referal amount credit ".$REFERRAL_AMOUNT." into your account";
	$dDate = Date('Y-m-d H:i:s');
	$ePaymentStatus = "Unsettelled";
	$REFERRAL_AMOUNT; 

	// added by seyyed amir
	//  این قسمت مربوط به هدیه معرفی راننده می باشد
	// چون هدایای رانندگان باز طراحی شده این قسمت غیر فعال می شود
	/*
	if($user_type=='driver'){

		if($_POST['vRefCode'] != "" && !empty($_POST['vRefCode'])){
			$generalobj->InsertIntoUserWallet($_POST['iRefUserId'],$_POST['eRefType'],$REFERRAL_AMOUNT,'Credit',0,$eFor,$tDescription,$ePaymentStatus,$dDate);
		}	
	}	
	*/


	if($APP_TYPE == 'UberX'){

		$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";
		$result = $obj->MySQLSelect($query);
		//print_r($result[0]['countId']] exit;
		$Drive_vehicle['iDriverId'] = $id;
		$Drive_vehicle['iCompanyId'] = "1";
		$Drive_vehicle['iMakeId'] = "3";
		$Drive_vehicle['iModelId'] = "1";
		$Drive_vehicle['iYear'] = Date('Y');
		$Drive_vehicle['vLicencePlate'] = "My Services";
		$Drive_vehicle['eStatus'] = "Active";
		$Drive_vehicle['eCarX'] = "Yes";
		$Drive_vehicle['eCarGo'] = "Yes";		
		$Drive_vehicle['vCarType'] = $result[0]['countId'];
		$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');
		$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$id."'";
		$obj->sql_query($sql);

	}else{
		if(SITE_TYPE=='Demo')
		{
			$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";
			$result = $obj->MySQLSelect($query);
			$Drive_vehicle['iDriverId'] = $id;
			$Drive_vehicle['iCompanyId'] = "1";
			$Drive_vehicle['iMakeId'] = "5";
			$Drive_vehicle['iModelId'] = "18";
			$Drive_vehicle['iYear'] = "2014";
			$Drive_vehicle['vLicencePlate'] = "CK201";
			$Drive_vehicle['eStatus'] = "Active";
			$Drive_vehicle['eCarX'] = "Yes";
			$Drive_vehicle['eCarGo'] = "Yes";		
			$Drive_vehicle['vCarType'] = $result[0]['countId'];
			$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');
			$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$id."'";
			$obj->sql_query($sql);
		}		
	}

	if($id != "")
	{
		$_SESSION['sess_iUserId'] = $id;
		if($user_type=='driver')
		{
			$_SESSION['sess_iCompanyId'] = 1;
			$_SESSION["sess_vName"] = $Data['vName'].' '.$Data['vLastName'];
			$_SESSION["sess_vCurrency"]= $Data['vCurrencyDriver'];
		}
		else
		{
			$_SESSION['sess_iCompanyId'] = $id;
			$_SESSION["sess_vName"] = $Data['vCompany'];
		}
        
		$_SESSION["sess_company"] = $Data['vCompany'];
        $_SESSION["sess_vEmail"] = $Data['vEmail'];
		$_SESSION["sess_user"] =$user_type;
		$_SESSION["sess_new"]=1;


		$maildata['EMAIL'] = $_SESSION["sess_vEmail"];
        $maildata['NAME'] = $_SESSION["sess_vName"];
        $maildata['PASSWORD'] = $_REQUEST['vPassword'];
	    //$generalobj->send_email_user("MEMBER_REGISTRATION_USER",$maildata);
	    if($user_type=='driver'){
	    	  $generalobj->send_email_user("DRIVER_REGISTRATION_ADMIN",$maildata);
	    	  $generalobj->send_email_user("DRIVER_REGISTRATION_USER",$maildata);
	    }else{
	    	$generalobj->send_email_user("COMPANY_REGISTRATION_ADMIN",$maildata);
	    	$generalobj->send_email_user("COMPANY_REGISTRATION_USER",$maildata);
	    }
		#header("Location:profile.php?first=yes");
		if($APP_TYPE == 'UberX'){
		header("Location:add_services.php");
		exit;
		
		}else{
			header("Location:profile.php?first=yes");
			exit;
		
		}
		
		
	}
}
?>
