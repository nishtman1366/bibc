<?php
include_once('../common.php');
print_r($_POST);
$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$npass = $generalobj->encrypt($pass);
$remember = isset($_POST['remember-me']) ? $_POST['remember-me'] : '';

$tbl = 'administrators';
$fields = 'iAdminId, vFirstName,vLastName, vEmail, eStatus, iGroupId,area';

$sql = "SELECT $fields FROM $tbl WHERE vEmail = '" . $email . "' AND vPassword = '" . $npass . "'";
$db_login = $obj->MySQLSelect($sql);
$sql = "SELECT * from $tbl WHERE vEmail = '" . $email . "'";
$db_mail = $obj->MySQLSelect($sql);

if (count($db_login) == 0) {
    if (count($db_mail) > 0) {
        echo "3";
        exit;
    } else {
        echo "4";
        exit;
    }
}
if (count($db_login) > 0) {
    if ($db_login[0]['eStatus'] != 'Active') {
        echo 1;
        exit;
    } else {
        $_SESSION['sess_area'] = $db_login[0]['area'];
        $_SESSION['sess_iAdminUserId'] = $db_login[0]['iAdminId'];
        $_SESSION['sess_iGroupId'] = $db_login[0]['iGroupId'];
        $_SESSION["sess_vAdminFirstName"] = $db_login[0]['vFirstName'];
        $_SESSION["sess_vAdminLastName"] = $db_login[0]['vLastName'];
        $_SESSION["sess_vAdminEmail"] = $db_login[0]['vEmail'];

        if ($remember == "Yes") {
            setcookie("member_login_cookie", $vEmail, time() + 2592000);
            setcookie("member_password_cookie", $vPassword, time() + 2592000);
        } else {
//            setcookie("member_login_cookie", "", time());
//            setcookie("member_password_cookie", "", time());
        }
        echo 2;
        exit;
    }
}
?>
