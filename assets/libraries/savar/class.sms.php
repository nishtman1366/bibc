<?php
require_once "class.payamakpardaz.php";

class SMS
{
    private $smsObj;
    private $Result;
    
	function __construct($user='', $pass='', $numbers='')
	{
        if($user == '')
            $user = '';
        if($pass == '')
            $pass = '';
        if($numbers == '')
            $numbers = '';
        
		$this->smsObj = new PayamPardaz($user, $pass, $number); 
	}
	
	
	function SendSMS($number, $text)
	{
        if(isset($_GET['amir'])) echo 3.1;
		$orgNumber = $number;
		$number = $this->FixNumber($number);

		try
		{
            //if(isset($_GET['amir'])) var_dump($this->smsObj);
            
			return $this->smsObj->SendSMS($number,$text);
		}
		catch (Exception $ex) {
			$this->Result = array();
			return false;
		}
	}
	
	function FixNumber($number)
	{
		$number = str_ireplace(' ','',$number);
		$number = '0' . substr($number, -10);
		return $number;
	}
}