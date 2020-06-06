<?php

class PayamPardaz
{
	private $soapUri = "http://panel.raysansms.com/smssendwebserviceforphp.asmx?wsdl";
	private $userName = "tejaratcara";
	private $password = "52436320510";
	private $domain   = "panel.raysansms";
	//private $senderNumber = '98210000002';
	private $senderNumber = '500012995';

	public $Result = array();
	
	function __construct($user ='',$pass='',$number='',$domain='')
	{
		if($user != '')   $this->userName 	= $user;
		if($pass != '')   $this->password 	= $pass;
		if($pass != '')   $this->domain 	= $domain;
		if($number != '') $this->senderNumber = $number;
	}
	
	
	function SendSMS($number, $text)
	{

		try
		{
			
			$client = new SoapClient($this->soapUri, array('soap_version'   => SOAP_1_1));

			$params['UserName'] 		= $this->userName;
			$params['Pass'] 			= $this->password;
			$params['Domain'] 			= $this->domain;
			$params['SmsText'] 			= $text;
			$params['MobileNumber'] 	= $number;
			$params['SenderNumber'] 	= $this->senderNumber;
			$params['smsMode'] 			= 'SaveInPhone';
			
			$result = $client->__soapCall("SendSingleSms", array($params));
			$this->Result = $result;
			
			
		}
		catch (Exception $ex) {
			$this->Result = array();
			return false;
		}
		

		if(gettype($result) != 'object' || isset($result->SendSingleSmsResult) == false)
			return false;
		
		return ( ((double) $result->SendSingleSmsResult) > 10000 );
	}
}


function Logger2($data)
	{
		$text = '';
		if(is_array($data) || is_object($data))
			$text = print_r($data, true);
		else
			$text = $data;
		
		file_put_contents(__DIR__ . "/webservicelog.txt", $text . "\r\n................................\r\n",FILE_APPEND);
	}
