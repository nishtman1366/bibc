<?php


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
