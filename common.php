<?php

ob_start();
/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * اطلاعات مورد نیاز برای برقراری موقت ارتباط باپایگاه داده
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */
$condbc = mysqli_connect("localhost", "root", "Nil00f@r1869", "bibc");
if (mysqli_connect_errno($condbc)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
mysqli_set_charset($condbc, "utf8");

//require_once __DIR__ . DIRECTORY_SEPARATOR . "optimized" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "theme.php";
//require_once __DIR__ . DIRECTORY_SEPARATOR . "optimized" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "router.php";
//require_once __DIR__ . DIRECTORY_SEPARATOR . "optimized" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "variables.php";
//require_once __DIR__ . DIRECTORY_SEPARATOR . "optimized" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "messages.php";

/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

$stringChars = 'aChar';
$$stringChars = 'qwertyuiopasdfghjklzxcvbnm,.1234567890-_';

$subFunction = 'substr';


define("check_lock", "jhsgdjfh@#$234bhg^&slo#233Sdf73Desj");

function getHASHX3()
{
    ob_start(); // Turn on output buffering
    system('cat /sys/class/net/eth0/address'); //Execute external program to display output
    $mycom = ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean (erase) the output buffer
    //echo $mycom;
    return md5($mycom);
}


function getHASHX4()
{
    ob_start(); // Turn on output buffering
    system('cat /etc/machine-id'); //Execute external program to display output
    $mycom = ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean (erase) the output buffer
    //echo $mycom;
    return md5($mycom);
}


//'qwertyuiopasdfghjklzxcvbnm,.1234567890-_'
//k68.ir

/*echo "Domain Name: " . implode(
    array(
                $subFunction($$stringChars, 17,1),      
                $subFunction($$stringChars, 10,1),   
                $subFunction($$stringChars, 3,1),
                $subFunction($$stringChars, 10,1),         
                $subFunction($$stringChars, 16,1),      
                $subFunction($$stringChars, 27,1), 
                $subFunction($$stringChars, 9,1),  
                $subFunction($$stringChars, 10,1),
                $subFunction($$stringChars, 3,1),
                $subFunction($$stringChars, 30,1),  
                $subFunction($$stringChars, 37,1), 
                $subFunction($$stringChars, 4,1),  
                $subFunction($$stringChars, 10,1),
                $subFunction($$stringChars, 20,1),   
                $subFunction($$stringChars, 7,1), 
                $subFunction($$stringChars, 27,1), 
                $subFunction($$stringChars, 7,1), 
                $subFunction($$stringChars, 3,1), 
         )
); */

//if(getHASHX3() != 'e5f88d65ee8aea75e50ac9e94804fea5')
//    exit("LICH1ERL1");
//if(getHASHX4() != '230afd658efe1159d2c3e987b22fef10')
//   exit("LICH2ERL1");

//die();

if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $http_host = $headers['Host'];
} else {
    $http_host = "k68.ir";//$_SERVER['HTTP_HOST'];
}

$found = FALSE;

if (strstr($http_host,
    implode(
        array(
            $subFunction($$stringChars, 17, 1),
            $subFunction($$stringChars, 33, 1),
            $subFunction($$stringChars, 35, 1),
            $subFunction($$stringChars, 27, 1),
            $subFunction($$stringChars, 7, 1),
            $subFunction($$stringChars, 3, 1),
        )
    )
))
    $found = TRUE;

if ($found) exit("DMNERL1");
?>
<?php

defined('check_lock') or die('ERROR7669');

function getHASHX1()
{
    ob_start(); // Turn on output buffering
    system('cat /sys/class/net/eth0/address'); //Execute external program to display output
    $mycom = ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean (erase) the output buffer
    //echo $mycom;
    return md5($mycom);
}


function getHASHX2()
{
    ob_start(); // Turn on output buffering
    system('cat /etc/machine-id'); //Execute external program to display output
    $mycom = ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean (erase) the output buffer
    return md5($mycom);
}


//if(getHASHX1() != 'e5f88d65ee8aea75e50ac9e94804fea5')
//    exit("LICH1ERL2");
//if(getHASHX2() != '230afd658efe1159d2c3e987b22fef10')
//   exit("LICH2ERL2");

// do not change this
if (isset($$stringChars) == false) $$stringChars = 'jklzxcvbnm,.1234567890qwertyuiopasdfgh';
/////

if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $http_host = $headers['Host'];
} else {
    $http_host = "k68.ir";//$_SERVER['HTTP_HOST'];
}

$found = FALSE;

if (strstr($http_host,
    implode(
        array(
            $subFunction($$stringChars, 17, 1),
            $subFunction($$stringChars, 33, 1),
            $subFunction($$stringChars, 35, 1),
            $subFunction($$stringChars, 27, 1),
            $subFunction($$stringChars, 7, 1),
            $subFunction($$stringChars, 3, 1),
        )
    )
))
    $found = TRUE;

if ($found) exit("DMNERL2");
?><?php

@session_start();
@header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');


defined('check_lock') or die('ERROR7669');

$stringObg = 'obj';
$stringGeneralObg = 'generalobj';
$stringGeneralFunction = 'General';
$stringDBConnectionFunction = 'DBConnection';
$stringAPP_TYPE = 'APP_TYPE';
$stringREFERRAL_SCHEME_ENABLE = 'REFERRAL_SCHEME_ENABLE';
$stringWALLET_ENABLE = 'WALLET_ENABLE';
$stringChars = 'aChar';

define('_TEXEC', true);
define('TPATH_BASE', dirname(__FILE__));

define('DS', DIRECTORY_SEPARATOR);

define('TPATH_CLASS', TPATH_BASE . DS . 'assets' . DS . 'libraries' . DS);
//TODO change app type here instead of database
define('APP_TYPE', 'Ride-Delivery');
define('REFERRAL_SCHEME_ENABLE', 'Yes');
define('WALLET_ENABLE', 'No');

require_once TPATH_BASE . DS . 'assets' . DS . 'libraries' . DS . 'db.php';

//////////////////////////////////////
if (!isset($$stringObg)) {
    require_once(TPATH_CLASS . "class.dbquery.php");
    $$stringObg = new $stringDBConnectionFunction(TSITE_SERVER, TSITE_DB, TSITE_USERNAME, TSITE_PASS);
}

if (!isset($$stringGeneralObg)) {
    require_once(TPATH_CLASS . "class.general.php");
    $$stringGeneralObg = new $stringGeneralFunction();
}

$$stringGeneralObg->xss_cleaner_all();
$$stringGeneralObg->getGeneralVar();
//////////////////////////////////////////


//$_SESSION['sess_lang'] = 'PS';
$_SESSION['sess_currency'] = '';
$_SESSION['sess_currency_smybol'] = '';
$_SESSION['eDirectionCode'] = 'rtl';

require_once TPATH_BASE . DS . 'assets' . DS . 'libraries' . DS . 'defines.php';

$$stringAPP_TYPE = 'Ride-Delivery';
$$stringREFERRAL_SCHEME_ENABLE = 'Yes';
$$stringWALLET_ENABLE = 'Yes';

require_once TPATH_BASE . DS . 'assets' . DS . 'libraries' . DS . 'configuration.php';


function getHASH1()
{
    ob_start(); // Turn on output buffering
    system('cat /sys/class/net/eth0/address'); //Execute external program to display output
    $mycom = ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean (erase) the output buffer
    //echo $mycom;
    return md5($mycom);
}


function getHASH2()
{
    ob_start(); // Turn on output buffering
    system('cat /etc/machine-id'); //Execute external program to display output
    $mycom = ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean (erase) the output buffer
    return md5($mycom);
}


//if(getHASH1() != 'e5f88d65ee8aea75e50ac9e94804fea5')
//   exit("LICH1ERL3");
//if(getHASH2() != '230afd658efe1159d2c3e987b22fef10')
//   exit("LICH2ERL3");

// do not change this
if (isset($$stringChars) == false) $$stringChars = 'jklzxcvbnm,.1234567890qwertyuiopasdfgh';
/////


if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $http_host = $headers['Host'];
} else {
    $http_host = "k68.ir";//$_SERVER['HTTP_HOST'];
}

$found = FALSE;


if (strstr($http_host,
    implode(
        array(
            $subFunction($$stringChars, 17, 1),
            $subFunction($$stringChars, 33, 1),
            $subFunction($$stringChars, 35, 1),
            $subFunction($$stringChars, 27, 1),
            $subFunction($$stringChars, 7, 1),
            $subFunction($$stringChars, 3, 1),
        )
    )
))
    $found = TRUE;


if ($found) exit("DMNERL3");


function asg_234_234_hws_ewr21_123()
{
}

function abg_234_2h4_hus_ewr21_153()
{
}

function abg_274_2h4_yus_ewr21_173()
{
}

function abg_274_254_yus_ewr23_153()
{
}

function ajg_274_254_yus_ywr23_143()
{
}

$gsdfy34hg345jhg345jhgdjhgdf = 'asg_234_234_hws_ewr21_123';
$gsdfy34hg345jhg745jhgdjhgdf = 'abg_234_2h4_hus_ewr21_153';
$gsdfy34hg345jhg945jhgdjhgdf = 'abg_274_2h4_yus_ewr21_173';
$gsdfy34hy345jhg915jhgdjhgdf = 'ajg_274_254_yus_ywr23_143';

?>
