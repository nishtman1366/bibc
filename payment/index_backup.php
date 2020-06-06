<?php
include_once('../common.php');
require_once(TPATH_CLASS .'savar/class.factor.php');

$MerchantId = 'B974';
$admin_email = 'seyyed.a@gmail.com';
$sha1Key = '22338240992352910814917221751200141041845518824222260';

session_start();

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

$factor = false;

if(isset($_REQUEST['token']) && strlen($_REQUEST['token']) == 32)
{
	$token = $_REQUEST['token'];
	$factor = SavarFactor::GetByToken($token);
}

if($factor !== false && isset($factor['iUserId']))
{
	print_r($factor );
	$inputAmount = $factor['iUserId'];
	$inputUserId = $factor['iAmount'];
}
else if(isset($_SESSION['sess_iUserId']) && $_SESSION['sess_iUserId'] > 0)
{
	
	$inputUserId = $_SESSION['sess_iUserId'];
	if(isset($_POST['fAmount']))
		$inputAmount = $_POST['fAmount'];
}
else
{
	
	$inputAmount = isset($_REQUEST['Amount']) ? $_REQUEST['Amount'] : '';
	$inputUserId = isset($_REQUEST['UserId']) ? $_REQUEST['UserId'] : '';
}


if($inputAmount !== '' && $inputUserId !== '')
{
	$tbl = 'register_user';
	$fields = 'iUserId, vName, vLastName, vEmail, eStatus, vCurrencyPassenger';

	$sql = "SELECT $fields FROM $tbl WHERE iUserId = '".$inputUserId."'";

	$db_login = $obj->MySQLSelect($sql);
	if(count($db_login) > 0)
	{
		if($db_login[0]['eStatus'] != "Deleted"){
			$_SESSION['savar_iUserId']	= $db_login[0]['iUserId'];
			$_SESSION["savar_vName"]	= $db_login[0]['vName'];
			$_SESSION["savar_vLastName"]= $db_login[0]['vLastName'];
			$_SESSION["savar_vFullName"]= $db_login[0]['vName'] . ' ' . $db_login[0]['vLastName'];
			$_SESSION["savar_vEmail"]	= $db_login[0]['vEmail'];
			$_SESSION["savar_user"] 	= "rider";
			$_SESSION["savar_vCurrency"]= $db_login[0]['vCurrencyPassenger'];
			$_SESSION["savar_amount"]	= $inputAmount;
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
	}
	else
		$error_message ='خطا در دریافت داده مبلغ و اطلاعات کاربر';
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

	if(intval($_POST['PayAmount']) >= 1000)
	{
		if(!empty($_POST['fullname']))
		{
			$_SESSION['merchantId'] = $MerchantId;
			$_SESSION['sha1Key'] = $sha1Key;
			$_SESSION['admin_email'] = $admin_email;
			$_SESSION['amount'] =$_POST['PayAmount'] ;
			$_SESSION['PayOrderId'] =$_POST['PayOrderId'];
			$_SESSION['fullname'] =$_POST['fullname'];
			$_SESSION['email'] =$_POST['email'];
			$revertURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?payback';
			
			$client = new SoapClient('https://ikc.shaparak.ir/XToken/Tokens.xml', array('soap_version'   => SOAP_1_1));

			$params['amount'] =  $_SESSION['amount'];
			$params['merchantId'] = $MerchantId;
			$params['invoiceNo'] = $_POST['PayOrderId'];
			$params['paymentId'] = $_POST['PayOrderId'];
			$params['specialPaymentId'] = $_POST['PayOrderId'];
			$params['revertURL'] = $revertURL;
			$params['description'] = "";
			$result = $client->__soapCall("MakeToken", array($params));
			$_SESSION['token'] = $result->MakeTokenResult->token;
			$data['token'] = $_SESSION['token'];
			$data['merchantId'] = $_SESSION['merchantId'];
            
            $bank_form_action = 'https://ikc.shaparak.ir/TPayment/Payment/index';
			$bank_form_data= $data;
            $body_action = "SEND_TO_BANK";
		}
		else
		{
			$error_message ='نام را وارد کنید<br/>';
		}
	}else
	{
		$error_message ='مبلغ صحیح نیست <br/>';
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
    
    if ($_POST['resultCode'] == '100') 
    {
        $referenceId = isset($_POST['referenceId']) ? intval($_POST['referenceId']) : 0;
        $client = new SoapClient('https://ikc.shaparak.ir/XVerify/Verify.xml', array('soap_version'   => SOAP_1_1));
        $params['token'] =  $_SESSION['token'];
        $params['merchantId'] = $_SESSION['merchantId'];
        $params['referenceNumber'] = $referenceId;
        $params['sha1Key'] = $_SESSION['sha1Key'];
        $result = $client->__soapCall("KicccPaymentsVerification", array($params));
        $result = ($result->KicccPaymentsVerificationResult);

        if (floatval($result) > 0 && floatval($result) == floatval($_SESSION['amount']) )
        {	
            
			$iUserId = $_SESSION['savar_iUserId'];
			$bAmount = $_SESSION['amount'];
			$iTripId = 0;
			$eFor = "Deposit";
			$tDescription = "شارژ نقدی کیف پول سوار #شماره تراکنش: " . $referenceId ;
			$ePaymentStatus = "Unsettelled";					
			$dDate = Date('Y-m-d H:i:s');
			
			$insert_user_wallet = $generalobj->InsertIntoUserWallet($iUserId
			,'Rider'
			,$bAmount
			,'Credit'
			,$iTripId
			,$eFor
			,$tDescription
			,$ePaymentStatus
			,$dDate
			);
			
			$vCurrency = $_SESSION["sess_currency_smybol"];
			
            $success_massage = '<p><b>پرداخت شما کامل شده است</b><br></p>';
            $success_massage .= '<p>کد پیگیری : ' . $referenceId . '<br></p>';
		$success_massage .= "<p>کیف پول سوار شما به مبلغ $bAmount $vCurrency شارژ شد.</p>";
        }
        else
        {
              $error_message  = '<p><b>پرداخت شما کامل نشد</b><br></p>';
              $error_message .= '<p>'. messeg2($result). '</p>';
        }

    }
    else
    {
        $error_message  = '<p><b>پرداخت شما کامل نشد</b><br></p>';
        $error_message .= '<p>' .messeg($_POST['resultCode']).'</p>';
    }
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
  
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, target-densitydpi=medium-dpi, user-scalable=0" />
	<title>شارژ کیف پول سوار</title>
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
            <h1 class="title">شارژ کیف پول سوار</h1>
            <div class="name">نام کاربر: <?php echo  $_SESSION["savar_vFullName"] ?></div>
            <div class="user">کد کاربر: <?php echo  $_SESSION["savar_vEmail"] ?></div>
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
				<span><?php echo  $_SESSION["sess_currency_smybol"]; ?></span>
			</div>

            <form action="" method="POST">        
                <input type="hidden" class="textbox" name="action" id="action" value="pay"/>
                <input type="hidden" name="fullname" value="<?php echo  $_SESSION["savar_vFullName"] ?>"   />
                <input type="hidden" name="PayOrderId" value="3453235" />
                <input type="hidden" name="PayAmount" value="<?php echo  $_SESSION["savar_amount"] ?>" />
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