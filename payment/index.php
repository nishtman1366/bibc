<?php
include_once('../common.php');
require_once(TPATH_CLASS .'savar/class.factor.php');
include_once("function.php");

$admin_email = 'seyyed.a@gmail.com';
$sha1Key = '22338240992352910814917221751200141041845518824222260';

$MerchantId="140308816";
$TerminalId="24019036";
$key="4f26jZ4NHYBaMohVhYBIBrlx0aAY+Ahl";

session_start();

if($_SESSION["sess_currency_smybol"] == '')
$_SESSION["sess_currency_smybol"] = 'تومان';

if(isset($_REQUEST['action']) && isset($_REQUEST['fAmount']) &&
isset($_SESSION['sess_iUserId']) )
{
  require_once(TPATH_CLASS .'savar/class.factor.php');
  $token = SavarFactor::Create($_SESSION['sess_iUserId'],$_REQUEST['fAmount']);

  if($token !== false)
  {
    $url = $tconfig["tsite_url"] . 'payment/?token=' . $token;
    header("Location: " . $url);
    echo '<a href="' . $url .'">Go To paymeny Page</a>';
    die();
  }
  else
  {
    header("Location: /app/sign-in");
    die("Error.");
  }
}
//print_r($_SESSION).die();

$body_action = "INDEX";
$success_massage = '';
$error_message = '';
$bank_form_action = '';
$bank_form_data = array();


//$generalobj->InsertIntoUserWallet();

//##################################################################
//#
//#
//#             دریافت اطلاعات کاربر
//#

$inputUserId = '';//isset($_SESSION['sess_iUserId']) ? $_SESSION['sess_iUserId'] : '';
$inputAmount  = '';//isset($_SESSION['sess_iUserId']) ? $_SESSION['sess_iUserId'] : '';
$userType = '';

$factor = false;

if(isset($_REQUEST['token']) && strlen($_REQUEST['token']) == 32)
{
  $token = $_REQUEST['token'];
  $_SESSION['savar_factor_token'] = $_REQUEST['token'];
  $factor = SavarFactor::GetByToken($token);
}

if(isset($_REQUEST['token'])=== false && isset($_REQUEST['payback']) === false)
{
  header("Location: /app/sign-in");
  die("Error.");
}

if($factor !== false && (isset($factor['iUserId']) || isset($factor['iDriverId'])) && $factor['iStatus'] == 'CREATE')
{
  if ($factor['iDriverId'] == '0' || $factor['iDriverId'] == 0) {

    $inputUserId = $factor['iUserId'];
  }
  else {

    $inputUserId = $factor['iDriverId'];
    $userType = "Driver";
  }
  $inputAmount = $factor['iAmount'];
}

if($inputAmount !== '' && $inputUserId !== '')
{
  $tbl = 'register_user';
  $fields = 'iUserId, vName, vLastName, vEmail, eStatus, vCurrencyPassenger';
  $sql = "SELECT $fields FROM $tbl WHERE iUserId = '".$inputUserId."'";

  if ($userType == "Driver") {

    $tbl = 'register_driver';
    $fields = 'iDriverId, vName, vLastName, vEmail, eStatus, vCurrencyDriver';
    $sql = "SELECT $fields FROM $tbl WHERE iDriverId = '".$inputUserId."'";
  }

  $db_login = $obj->MySQLSelect($sql);


  if(count($db_login) > 0)
  {
    if($db_login[0]['eStatus'] != "Deleted"){

      $_SESSION["savar_vName"]	= $db_login[0]['vName'];
      $_SESSION["savar_vLastName"]= $db_login[0]['vLastName'];
      $_SESSION["savar_vFullName"]= $db_login[0]['vName'] . ' ' . $db_login[0]['vLastName'];
      $_SESSION["savar_vEmail"]	= $db_login[0]['vEmail'];
      $_SESSION["savar_amount"]	= $inputAmount;
      $_SESSION["savar_factor_id"]	= $factor['iFactorId'];

      if ($userType == "Driver") {

        $_SESSION['savar_iUserId']	= $db_login[0]['iDriverId'];
        $_SESSION["savar_user"] 	= "driver";
        $_SESSION["savar_vCurrency"]= $db_login[0]['vCurrencyDriver'];
      }
      else {

        $_SESSION['savar_iUserId']	= $db_login[0]['iUserId'];
        $_SESSION["savar_user"] 	= "rider";
        $_SESSION["savar_vCurrency"]= $db_login[0]['vCurrencyPassenger'];
      }
    }
    else
    $error_message = 'کاربر حذف شده است';
  }
  else
  $error_message ='اطلاعات کاربر دریافت نشد';
}
else
{
  if(isset($_REQUEST['payback']) == false)
  {
    $_SESSION['savar_iUserId']	= '';
    $_SESSION["savar_vName"]	= '';
    $_SESSION["savar_vLastName"]= '';
    $_SESSION["savar_vFullName"]= '';
    $_SESSION["savar_vEmail"]	= '';
    $_SESSION["savar_user"] 	= '';
    $_SESSION["savar_vCurrency"]= '';
    $_SESSION["savar_amount"]	= '';
    $_SESSION["savar_factor_id"]= '';
  }

  if($factor['iStatus'] != 'CREATE' && isset($_REQUEST['payback']) == false)
  $error_message = 'اعتبار فاکتور تمام شده است';
}

if($error_message != '')
$body_action = "ERROR";

//##################################################################
//#
//#
//#             بخش بررسی فرم و ارسال به بانک
//#

if(isset($_POST['action']) && $_POST['action'] == 'pay')
{
  if(intval($_POST['PayAmount']) >= 100)
  {
    if(!empty($_POST['fullname']))
    {
      $_SESSION['merchantId']  = $MerchantId;
      $_SESSION['sha1Key']     = $sha1Key;
      $_SESSION['admin_email'] = $admin_email;
      $_SESSION['amount_tooman'] = $_POST['PayAmount'];
      $_SESSION['PayOrderId']  = $_POST['PayOrderId'];
      $_SESSION['fullname']    = $_POST['fullname'];
      $_SESSION['email']       = $_POST['email'];

      $OrderId = $_POST['PayOrderId'];
      $Amount = $_POST['PayAmount'];
      $Amount = $Amount * 10;

      $server_request_scheme = '';
      if ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') || (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ) {
          $server_request_scheme = 'https';
      } else {
          $server_request_scheme = 'http';
      }

      $ReturnUrl = $server_request_scheme.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?payback';

      $LocalDateTime=date("m/d/Y g:i:s a");

      $SignData=encrypt_pkcs7("$TerminalId;$OrderId;$Amount","$key");

      //echo '<pre>';print_r($SignData);die();

      $data = array('TerminalId'=>$TerminalId,
      'MerchantId'=>$MerchantId,
      'Amount'=>$Amount,
      'SignData'=> $SignData,
      'ReturnUrl'=>$ReturnUrl,
      'LocalDateTime'=>$LocalDateTime,
      'OrderId'=>$OrderId);
      $str_data = json_encode($data);


      $res=CallAPI('https://sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest',$str_data);
      $arrres=json_decode($res);

      if($arrres->ResCode==0)
      {
        $Token= $arrres->Token;
        $_SESSION['token'] = $Token;

        $where = " iFactorId = '".$_SESSION["savar_factor_id"]."'";
        $data_factor['iStatus']= "SEND_TO_BANK";
        $data_factor['LastChangeDate']= date('Y-m-d H:i:s');

        $id = $obj->MySQLQueryPerform("user_factor",$data_factor,'update',$where);

        $url="https://sadad.shaparak.ir/VPG/Purchase?Token=$Token";
        header("Location:$url");
      }
      else
      {
        die($arrres->Description);
      }
    }
    else
    {
      $error_message ='نام را وارد کنید<br/>';
    }
  }else
  {
    $error_message ='مبلغ باید بیشتر از 100 تومان باشد <br/>';
  }
}

//##################################################################
//#
//#
//#             بازگشت از بانک و بررسی نتایج
//#

if (isset($_REQUEST['payback']))
{
  $body_action = "PAY_BACK";

  $OrderId=$_POST["OrderId"];
  $Token=$_POST["token"];
  $ResCode=$_POST["ResCode"];

  $where = " iFactorId = '".$_SESSION["savar_factor_id"]."'";
  $data_factor['iResultCode']= $ResCode;
  $data_factor['iStatus']= "PAY_BACK";
  $data_factor['LastChangeDate']= date('Y-m-d H:i:s');

  $data_back = $_POST;
  $data_back["message"] = messeg($ResCode);
  $data_back["message2"] = messeg2($ResCode);
  $data_factor['tExtra']= serialize($data_back);
  #echo '<pre>';print_r($data_factor);die();
  $id = $obj->MySQLQueryPerform("user_factor",$data_factor,'update',$where);

  if ($ResCode==0)
  {
    $verifyData = array('Token'=>$Token,'SignData'=>encrypt_pkcs7($Token,$key));
    $str_data = json_encode($verifyData);
    $res=CallAPI('https://sadad.shaparak.ir/vpg/api/v0/Advice/Verify',$str_data);
    $arrres=json_decode($res);
  }

  if ($arrres->ResCode!=-1 && $ResCode==0)
  {
    $referenceId = $arrres->SystemTraceNo;//isset($_POST['referenceId']) ? intval($_POST['referenceId']) : 0;
    $tbl = 'user_wallet';
    $db_sql = "SELECT count(*) as count FROM $tbl WHERE referenceId = '".$_SESSION["savar_factor_id"]."'";
    $arr_ = $obj->MySQLSelect($db_sql);
    //echo print_r($arr_[0]['count']);exit;

    if ($arr_[0]['count'] == 0) {

          $iUserId = $_SESSION['savar_iUserId'];
          $bAmount = floatval($_SESSION['amount_tooman']);
          $userType = $_SESSION["savar_user"];

          $iTripId = 0;
          $eFor = "Deposit";
          $tDescription = "شارژ نقدی کیف پول پارسی تاکسی\n#شماره تراکنش: " . $referenceId . "\nشماره فاکتور: " . $_SESSION["savar_factor_id"];
          $ePaymentStatus = "Unsettelled";
          $dDate = Date('Y-m-d H:i:s');

          $insert_user_wallet = $generalobj->InsertIntoUserWallet($iUserId
          ,$userType
          ,$bAmount
          ,'Credit'
          ,$iTripId
          ,$eFor
          ,$tDescription
          ,$ePaymentStatus
          ,$dDate
          ,$_SESSION["savar_factor_id"]
        );

        $vCurrency = $_SESSION["sess_currency_smybol"];

        $success_massage = '<p><b>پرداخت شما کامل شده است</b><br></p>';
        $success_massage .= '<p>کد پیگیری : ' . $referenceId . '<br></p>';
        $success_massage .= "<p>کیف پول سوار شما به مبلغ $bAmount $vCurrency شارژ شد.</p>";

        $where = " iFactorId = '".$_SESSION["savar_factor_id"]."'";
        $data_factor['iStatus']= "SUCCESS";
        $data_factor['LastChangeDate']= date('Y-m-d H:i:s');
        $data_factor['iVerifyCode'] = $ResCode;
        #echo '<pre>';print_r($data_factor);die();
        $id = $obj->MySQLQueryPerform("user_factor",$data_factor,'update',$where);
    }
    else {
      $error_message  = '<p><b>پرداخت شما کامل نشد، تراکنش تکراری است.</b><br></p>';
    }
}
else
{
  // $error_message  = '<p><b>پرداخت شما کامل نشد</b><br></p>';
  // $error_message .= '<p>' .$ResCode.'</p>';
  $error_message  = '<p><b>پرداخت شما کامل نشد</b><br></p>';
  // $error_message .= '<p>'. messeg2($result). '</p>';
  $error_message .= '<p> کد خطا : '. $ResCode. '</p>';

  $where = " iFactorId = '".$_SESSION["savar_factor_id"]."'";
  $data_factor['iStatus'] = "VERIFY_ERROR";
  $data_factor['iVerifyCode'] = $result;
  $data_factor['LastChangeDate']= date('Y-m-d H:i:s');
  #echo '<pre>';print_r($data_factor);die();
  $id = $obj->MySQLQueryPerform("user_factor",$data_factor,'update',$where);
}
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, target-densitydpi=medium-dpi, user-scalable=0" />
  <title>شارژ کیف پول پارسی تاکسی</title>
  <link rel="stylesheet" href="assets/style.css">
  <script type="text/javascript">
  function LoadPage(){
    redirectToBank();
  }
  function redirectToBank() {
    <?php if($body_action == "SEND_TO_BANK") : ?>
    document.forms["redirect_to_bank"].submit();
    <?php endif; ?>
  }
  </script>
</head>
<body onload="LoadPage();">
  <div id="main">
    <div class="header">
      <h1 class="title">شارژ کیف پول:</h1>
      <div class="name">نام کاربر: <?php echo  $_SESSION["savar_vFullName"] ?></div>
      <?php /*<div class="user">کد کاربر: <?php echo  $_SESSION["savar_vEmail"] ?></div> */ ?>
    </div>

    <div class="content">

      <?php if($success_massage != '') : ?>
        <div class="alert success"><?php echo  $success_massage ?></div>
      <?php endif; ?>
      <?php if($error_message != '') : ?>
        <div class="alert error"><?php echo  $error_message ?></div>
      <?php endif; ?>



      <?php if($body_action == "INDEX") : ?>
        <div class="text">مبلغ شارژ</div>
        <div class="amount"><?php echo  $_SESSION["savar_amount"] ?>
          <span style="color:red"><?php echo  $_SESSION["sess_currency_smybol"]; ?></span>
        </div>

        <form action="" method="POST">
            <input type="hidden" class="textbox" name="action" id="action" value="pay"/>
            <input type="hidden" name="fullname" value="<?php echo  $_SESSION["savar_vFullName"] ?>"   />
            <input type="hidden" name="PayOrderId" value="<?php echo  round(microtime(true) * 1000) ?>" />
            <input type="hidden" name="PayAmount" value="<?php echo  $_SESSION["savar_amount"] ?>"/>
            <input type="hidden" name="email" value="<?php echo  $_SESSION["savar_vEmail"] ?>" />
            <input type="submit" class="btnSubmit" value="تایید و پرداخت آنلاین" />
        </form>
      <?php elseif($body_action == "PAY_BACK") : ?>


      <?php elseif($body_action == "SEND_TO_BANK") : ?>

        <form name="redirect_to_bank" method="post" action="<?php echo  $bank_form_action ?>">
          <?php
          if ( is_array($data) ) {
            foreach ($data as $key => $val) {
              echo '<input type="hidden" name="' . $key . '" value="' . $val . '"> ';
            }
          }
          ?>
        </form>
        <div class="alert info">
          <p><b>در حال اتصال به درگاه پرداخت</b></p>
          <p><img src="assets/images/loading.gif" width="70px" /></p>
          <p>شکیبا باشید</p>
        </div>
      <?php endif; ?>

    </div>

    <div class="footer">
      <img src="assets/images/banks.png" />
    </div>

  </div>

  <!--

  [[[[[[[[[[[[:]]]]]]]]]]]]
  [:::::::::::::::::::::::]
  [::::               ::::]
  [::::  Designed By  ::::]
  [::::  Seyyed Amir  ::::]
  [::::   Eftekhari   ::::]
  [::::               ::::]
  [::::   1396.1.14   ::::]
  [:::: majazestan.com::::]
  [:::::::::::::::::::::::]
  [[[[[[[[[[[[:]]]]]]]]]]]]

-->
</body>
</html>

<?php
/******************************************************************************
*
*       F U N C T I O N S
*/

// تابع ارسال کاربر به صفحه پرداخت ایران کیش
function redirect_post($url, array $data)
{

  echo '
  <script type="text/javascript">
  function closethisasap() {
    document.forms["redirectpost"].submit();
  }
  </script>';
  echo '<form name="redirectpost" method="post" action="'.$url.'">';

  if ( !is_null($data) ) {
    foreach ($data as $k => $v) {
      echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
    }
  }

  echo'</form><div id="main">
  <p>درحال اتصال به درگاه بانک</p></div>';

  exit;
}


// توابع پیغام زمان برگشت از بانک
function messeg2($result)
{
  switch ($result)
  {
    case '-20':
    return "در درخواست کارکتر های غیر مجاز وجو دارد";
    break;
    case '-30':
    return " تراکنش قبلا برگشت خورده است";
    break;
    case '-50':
    return " طول رشته درخواست غیر مجاز است";
    break;
    case '-51':
    return " در در خواست خطا وجود دارد";
    break;
    case '-80':
    return " تراکنش مورد نظر یافت نشد";
    break;
    case '-81':
    return " خطای داخلی بانک";
    break;
    case '-90':
    return " تراکنش قبلا تایید شده است";
    break;
  }
}
function messeg($resultCode)
{
  switch ($resultCode)
  {
    case 110:
    return " انصراف دارنده کارت";
    break;
    case 120:
    return"   موجودی کافی نیست";
    break;
    case 130:
    case 131:
    case 160:
    return"   اطلاعات کارت اشتباه است";
    break;
    case 132:
    case 133:
    return"   کارت مسدود یا منقضی می باشد";
    break;
    case 140:
    return" زمان مورد نظر به پایان رسیده است";
    break;
    case 200:
    case 201:
    case 202:
    return" مبلغ بیش از سقف مجاز";
    break;
    case 166:
    return" بانک صادر کننده مجوز انجام  تراکنش را صادر نکرده";
    break;
    case 150:
    default:
    return " خطا بانک  $resultCode";
    break;
  }
}


// توابع پیغام زمان برگشت از بانک
function sep_message2($result)
{
  switch ($result)
  {
    case '-1':
    return "خطای در پردازش اط عات ارسالی (مشکل در یکی از ورودی ها و ناموفق بودن فراخوانی متد برگشت￼￼￼￼￼ تراکنش).";
    break;
    case '-3':
    return "ورودیها حاوی کارکترهای غیرمجاز میباشند.";
    break;
    case '-4':
    return "(کلمه عبور یا کد فروشنده اشتباه است.)";
    break;
    case '-6':
    return "سند قب  برگشت کامل یافته است. یا خارج از زمان 30 دقیقه ارسال شده است.";
    break;
    case '-7':
    return "رسید دیجیتالی تهی است.";
    break;
    case '-8':
    return "طول ورودیها بیشتر از حد مجاز است.";
    break;
    case '-9':
    return "وجود کارکترهای غیرمجاز در مبلغ برگشتی.";
    break;
    case '-10':
    return "رسید دیجیتالی به صورت Base64 نیست (حاوی کاراکترهای غیرمجاز است).";
    break;
    case '-11':
    return "طول ورودیها ک تر از حد مجاز است.";
    break;
    case '-12':
    return "مبلغ برگشتی منفی است.";
    break;
    case '-13':
    return "مبلغ برگشتی برای برگشت جزئی بیش از مبلغ برگشت نخوردهی رسید دیجیتالی است.";
    break;
    case '-14':
    return "چنین تراکنشی تعریف نشده است.";
    break;
    case '-15':
    return "مبلغ برگشتی به صورت اعشاری داده شده است.";
    break;
    case '-16':
    return "خطای داخلی سیستم";
    break;
    case '-17':
    return "برگشت زدن جزیی تراکنش مجاز ن ی باشد.";
    break;
    case '-18':
    return "IP Addressفروشنده نا معتبر است و یا رمز تابع بازگشتی (reverseTransaction) اشتباه است";
    break;
  }
}
function sep_message($sep_state_code)
{
  switch ($sep_state_code)
  {
    case -1:
    return " انصراف دارنده کارت";
    break;
    case 79:
    return "مبلغ سند برگشتی، از مبلغ تراکنش اصلی بیشتر است.";
    break;
    case 12:
    return "درخواست برگشت یک تراکنش رسیده است، در حالی که￼￼￼￼￼￼￼￼￼￼￼ تراکنش اصلی پیدا ن ی شود.";
    break;
    case 14:
    return "شماره کارت نامعتبر است.";
    break;
    case 15:
    return "چنین صادر کننده کارتی وجود ندارد.";
    break;
    case 33:
    return "تاریخ انقضای کارت گذشته است و کارت دیگر معتبر نیست.";
    break;
    case 38:
    return "رمز کارت (PIN) 3 مرتبه اشتباه وارد شده است در نتیجه￼￼￼￼￼￼￼￼￼￼￼ کارت غیر فعال خواهد شد.";
    break;
    case 55:
    return "خریدار رمز کارت (PIN) را اشتباه وارد کرده است.";
    break;
    case 61:
    return "مبلغ بیش از سقف برداشت می باشد.";
    break;
    case 93:
    return "تراکنش Authorize شده است (ش اره PIN و PAN￼￼￼￼￼￼￼￼ درست هستند) ولی امکان سند خوردن وجود ندارد.";
    break;
    case 68:
    return "تراکنش در شبکه بانکی Timeout خورده است.";
    break;
    case 34:
    return "خریدار یا فیلد CVV2 و یا فیلد ExpDate را اشتباه وارد￼￼￼￼￼￼￼￼ کرده است (یا اص  وارد نکرده است).";
    break;
    case 51:
    return "موجودی حساب خریدار، کافی نیست.";
    break;
    case 84:
    return "سیستم بانک صادر کننده کارت خریدار، در وضعیت ع لیاتی￼￼￼￼￼￼￼￼ نیست.";
    break;
    case 96:
    default:
    return " خطا بانک  $resultCode";
    break;
  }
}
