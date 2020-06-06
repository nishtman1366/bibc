<?php


//  address /assets/libraries/class.general.php   sendUserSMS
//  address /assets/libraries/class.general.php   sendCode
//  generalFunctions.php   sendEmeSms


$banix = new BanixSMS();

$banix->SendSMS('09171455977',"سلام، تست");

class BanixSMS
{
	private $soapUri = "http://panel.banixsms.ir/SOAPSmsSystem.asmx?WSDL";
	private $userName = "y09120218508";
	private $password = "9876543210";
	private $senderNumber = '500055659877';

	public $Result = array();
	
	function __construct($user ='',$pass='',$number='')
	{
		if($user != '')   $this->userName 	= $user;
		if($pass != '')   $this->password 	= $pass;
		if($number != '') $this->senderNumber = $number;
	}
	
	
	function SendSMS($number, $text)
	{

		$client = new SoapClient($this->soapUri, array('soap_version'   => SOAP_1_1));

		$params['UName'] 			= $this->userName;
		$params['Password'] 		= $this->password;
		$params['senderNumber'] 	= $this->senderNumber;
		$params['recipientNumber'] 	= $number;
		$params['Text'] 			= $text;
		$params['IsFlash'] 			= false;
		$result = $client->__soapCall("SendSingleSMS", array($params));
		
		$this->Result = $result->SendSingleSMSResult;
		
		return ($result->SendSingleSMSResult->State == 'OK');
	}
}



/*

	function sendEmeSms($toMobileNum,$message){
		global  $generalobj;
		
		require_once(TPATH_CLASS .'class.banixsms.php');
		
		$account_sid = $generalobj->getConfigurations("configurations","MOBILE_VERIFY_SID_TWILIO");
		$auth_token  = $generalobj->getConfigurations("configurations","MOBILE_VERIFY_TOKEN_TWILIO");
		$MobileNum   = $generalobj->getConfigurations("configurations","MOBILE_NO_TWILIO");
		
		$banix = new BanixSMS($account_sid, $auth_token, $MobileNum);
		
		// بعد از اینکه قرار شد تنظیمات از پنل ادمین گرفته شود
		// خط بعد پاک شود
		$banix = new BanixSMS();
		
		$ret = $banix->SendSMS($toMobileNum,$message);
		
		return $ret ? 1 : 0;
	}
	
	
	
	// Edit By Seyyed.AMir ,savar
		function sendUserSMS($mobileNo,$code,$fpass,$pass=''){
			
			require_once(TPATH_CLASS .'class.banixsms.php');
			
			$account_sid = $this->getConfigurations("configurations","MOBILE_VERIFY_SID_TWILIO");
			$auth_token  = $this->getConfigurations("configurations","MOBILE_VERIFY_TOKEN_TWILIO");
			$MobileNum   = $this->getConfigurations("configurations","MOBILE_NO_TWILIO");
			
			$banix = new BanixSMS($account_sid, $auth_token, $MobileNum);
			
			// بعد از اینکه قرار شد تنظیمات از پنل ادمین گرفته شود
			// خط بعد پاک شود
			$banix = new BanixSMS();

			$mobileNo = $this->clearPhone($mobileNo);
			
			$toMobileNum= "+".$code.$mobileNo;
			
			$ret = $banix->SendSMS($toMobileNum,$fpass);
			
			$success = $ret ? '1' : '0';

			return $success;
		}
		
		function sendCode($mobileNo,$code,$fpass='code',$pass=''){
			global $site_path,$langage_lbl;
			
			require_once(TPATH_CLASS .'class.banixsms.php');
			
			$account_sid = $this->getConfigurations("configurations","MOBILE_VERIFY_SID_TWILIO");
			$auth_token  = $this->getConfigurations("configurations","MOBILE_VERIFY_TOKEN_TWILIO");
			$MobileNum   = $this->getConfigurations("configurations","MOBILE_NO_TWILIO");
			
			$mobileNo=$this->clearPhone($mobileNo);
			
			$toMobileNum= "+".$code.$mobileNo;
			
			
			if($fpass=="forgot"){
				$text_prefix_reset_pass = $this->getConfigurations("configurations","PREFIX_PASS_RESET_SMS");
				// $verificationCode='Your Password is '.$this->decrypt($pass);
				$code=$this->decrypt($pass);
				$verificationCode=$text_prefix_reset_pass.' '.$code;
			}
			else{
				//$text_prefix_verification_code = $this->getConfigurations("configurations","PREFIX_VERIFICATION_CODE_SMS");
				$text_prefix_verification_code = $langage_lbl['LBL_VERIFICATION_CODE_TXT'];
				$code=mt_rand(1000, 9999);
				$verificationCode = $text_prefix_verification_code .' '.$code;
			}
			
			
			
			$banix = new BanixSMS($account_sid, $auth_token, $MobileNum);
			
			// بعد از اینکه قرار شد تنظیمات از پنل ادمین گرفته شود
			// خط بعد پاک شود
			$banix = new BanixSMS();

			$ret = $banix->SendSMS($toMobileNum,$verificationCode);

			$returnArr['action'] = $ret ? '1' : '0';
			$returnArr['verificationCode'] = $code;
			
			return $returnArr;
		}
	