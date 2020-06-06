<?php

class PayamPardaz
{
  private $url = "http://37.130.202.188/services.jspd";//'http://37.130.202.188/class/sms/webservice/send_url.php?uname={user}&pass={password}&msg={message}&to={number}&from={sendernumber}';
  private $userName = "bib";
  private $password = "bib";
  private $domain   = "panel.raysansms";
  #private $senderNumber = '98210000002';  // شماره اول
  //private $senderNumber = '500012995';	 // شماره دوم
  private $senderNumber = 'bib';   // شماره برای بلک لیست مخابرات

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


    if(isset($_GET['amir'])) echo "X1";
    $orgNumber = $number;
    $number = $this->FixNumber($number);

    if(isset($_GET['amir'])) echo "X2";
    try
    {
      /*$search = array('{user}','{password}', '{message}' , '{number}' ,'{sendernumber}');
      $replace = array($this->userName,$this->password,urlencode($text),urlencode($number),$this->senderNumber);

      $smsUrl = str_replace($search,$replace,$this->url);

      $this->Result = file_get_contents($smsUrl);

      if(isset($_GET['amir'])) echo "X4 " . ((double) $this->Result);

      return ((double) $this->Result) > 1000 ;*/

      $rcpt_nm = array($number);
      $param = array
      (
        'uname'=>$this->userName,
        'pass'=>$this->password,
        'from'=>$this->senderNumber,
        'message'=>$text,
        'to'=>json_encode($rcpt_nm),
        'op'=>'send'
      );

      $handler = curl_init("http://37.130.202.188/services.jspd");
      curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
      curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
      $response2 = curl_exec($handler);

      $response2 = json_decode($response2);
      $res_code = $response2[0];
      $res_data = $response2[1];

      return ((double) $res_data) > 1000;

    }
    catch (Exception $ex) {
      $this->Result = array();
      return false;
    }


    if(gettype($result) != 'object' || isset($result->SendSingleSmsResult) == false)
    return false;

    return ( ((double) $result->SendSingleSmsResult) > 1000 );
  }

  function FixNumber($number)
  {
    $number = str_ireplace(' ','',$number);
    $number = '0' . substr($number, -10);
    return $number;
  }
}
