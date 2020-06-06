<?php
session_start();
include_once('../common.php');
/*
 * بررسی سطح دسترسی کاربر
 */
include_once('savar_check_permission.php');
$tbl_name = "administrators";
$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
$sql = "SELECT * FROM " . $tbl_name . " WHERE iAdminId = '" . $sess_iAdminUserId . "'";
$db_data1 = $obj->MySQLSelect($sql);
$vAccessOptionsMenu = "";
if (count($db_data1) > 0) {
    $vAccessOptionsMenu = $db_data1[0]['vAccessOptions'];
}
//پایان بررسی سطح دسترسی کاربر
$script = 'Dashboard';

if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$generalobjAdmin->check_member_login();

$TimeDate = date("Y-m-d H:i:s");
$dateElements = explode('-', $TimeDate);
$month = date('m');
$day = date('d');
$date_start = "$dateElements[0]" . "-" . "$month" . "-" . "$day" . " " . "00:00:00";


//2
$ssql = "AND `tEndDate` <= '$TimeDate' AND `tTripRequestDate` >= '$date_start'";
$db_trips_fTripGenerateFare_LBL = 0;
$sql = "SELECT  count(*) as count, sum(iFare) as revenue FROM `trips` WHERE `iActive` = 'Finished' " . $ssql;
$db_trips_fTripGenerateFare = $obj->MySQLSelect($sql);
$revenue = $db_trips_fTripGenerateFare[0]['revenue'];
$count = $db_trips_fTripGenerateFare[0]['count'];

//3
$sql = "SELECT * FROM `vehicle_type` WHERE 1";
$db_vehicle_type = $obj->MySQLSelect($sql);

//4 Scheduled rides
$ssql = "`dBooking_date` >= '$date_start'";
$sql = "SELECT  count(*) as count FROM `cab_booking` WHERE " . $ssql;
$db_trips_scheduled = $obj->MySQLSelect($sql);
$scheduled_count = $db_trips_scheduled[0]['count'];

//5
$ssql = "AND `tTripRequestDate` >= '$date_start'";
$sql = "SELECT  count(*) as count FROM `trips` WHERE iActive = 'Canceled' " . $ssql;
$db_trips_Canceled = $obj->MySQLSelect($sql);
$trips_Canceled_count = $db_trips_Canceled[0]['count'];

//6
$ssql = "AND `tTripRequestDate` >= '$date_start'";
$sql = "SELECT  count(*) as count FROM `trips` WHERE iActive = 'Active' OR iActive = 'On Going Trip' " . $ssql;
$db_trips_ACTIVE_TRIPS = $obj->MySQLSelect($sql);
$trips_active_count = $db_trips_ACTIVE_TRIPS[0]['count'];

//7
$sql = "select * from register_driver WHERE eStatus != 'Deleted'";
$db_driver = $obj->MySQLSelect($sql);

//8
$sql = "SELECT * FROM `company` WHERE eStatus != 'Deleted'";
$db_company = $obj->MySQLSelect($sql);

//9
$db_Credit_Driver_LBL = 0;
$db_Debit_Driver_LBL = 0;
$db_Credit_Rider_LBL = 0;
$db_Debit_Rider_LBL = 0;
$db_trips_Commission_LBL = 0;
$db_trips_fDiscount_LBL = 0;
$sql = "SELECT iBalance FROM `user_wallet` WHERE `eUserType` = 'Driver' And `eType` = 'Credit'";
$db_Credit_Driver = $obj->MySQLSelect($sql);
for ($i = 0; $i < count($db_Credit_Driver); $i++) {
    $db_Credit_Driver_LBL += $db_Credit_Driver[$i]['iBalance'];
}

$sql = "SELECT iBalance FROM `user_wallet` WHERE `eUserType` = 'Driver' And `eType` = 'Debit'";
$db_Debit_Driver = $obj->MySQLSelect($sql);
for ($i = 0; $i < count($db_Credit_Driver); $i++) {
    $db_Debit_Driver_LBL += $db_Debit_Driver[$i]['iBalance'];
}

$sql = "SELECT iBalance FROM `user_wallet` WHERE `eUserType` = 'Rider' And `eType` = 'Credit'";
$db_Credit_Rider = $obj->MySQLSelect($sql);
for ($i = 0; $i < count($db_Credit_Driver); $i++) {
    $db_Credit_Rider_LBL += $db_Credit_Rider[$i]['iBalance'];
}

$sql = "SELECT iBalance FROM `user_wallet` WHERE `eUserType` = 'Rider' And `eType` = 'Debit'";
$db_Debit_Rider = $obj->MySQLSelect($sql);
for ($i = 0; $i < count($db_Credit_Driver); $i++) {
    $db_Debit_Rider_LBL += $db_Debit_Rider[$i]['iBalance'];
}
$ssql = "AND `tEndDate` <= '$TimeDate' AND `tTripRequestDate` >= '$date_start'";
$sql = "SELECT * FROM `trips` WHERE `iActive` = 'Finished' " . $ssql;
$db_trips = $obj->MySQLSelect($sql);
for ($i = 0; $i < count($db_trips); $i++) {
    $db_trips_Commission_LBL += $db_trips[$i]['fCommision'];
}
for ($i = 0; $i < count($db_trips); $i++) {
    $db_trips_fDiscount_LBL += $db_trips[$i]['fDiscount'];
}

//10
$ssql = "`tTripRequestDate` >= '$date_start'" . " ORDER BY `iTripId` ASC";
$sql = "SELECT * FROM `trips` WHERE " . $ssql;
$db_recent_trips = $obj->MySQLSelect($sql);

?>
<!DOCTYPE html>
<html lang="en">

<!-- BEGIN HEAD-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>ادمین | داشبورد</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="<?php assets('css/admin/adminLTE/AdminLTE.min.css'); ?>"/>
    <script type="text/javascript" src="<?php assets('vendor/morris/raphael-min.js'); ?>"></script>
    <script type="text/javascript" src="<?php assets('vendor/morris/morris.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php assets('js/actions.js'); ?>"></script>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php assets('vendor/bootstrap/4.3.1/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/Themify/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/animate/animate.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/jScrollPane/jquery.jscrollpane.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/waves/waves.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/switchery/switchery.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/DataTables/css/dataTables.bootstrap4.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/DataTables/css/responsive.bootstrap4.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/DataTables/css/buttons.dataTables.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/DataTables/css/buttons.bootstrap4.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/bootstrap/css/bootstrap-datepicker.min.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php assets('vendor/bootstrap/css/bootstrap-glyphicons.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/bootstrap/css/bootstrap-editable.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/dropify/dropify.min.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/tranxit/core.css'); ?>">
    <link rel="stylesheet" href="<?php assets('vendor/tranxit/style_pagination.css'); ?>">

    <!--	<script type="text/javascript" async="" src="-->
    <?php //assets('vendor/livechatinc/tracking.js'); ?><!--"></script>-->
    <script>
        window.Laravel = {"csrfToken": "aE99KLvAc1YqR5xG7rr9k4HaM64jqeBViyj8fkCk"}    </script>
    <style type="text/css">
        .rating-outer span,
        .rating-symbol-background {
            color: #ffe000 !important;
        }

        .rating-outer span,
        .rating-symbol-foreground {
            color: #ffe000 !important;
        }

        .row::after {
            content: none;
            display: none;
            clear: none;
        }

        body {
            background-color: #fff;

        }
    </style>
    <link rel="stylesheet" href="<?php assets('vendor/jvectormap/jquery-jvectormap-2.0.3.css'); ?>">
    <style>
        #sidebar {
            width: 200px;
            overflow: auto;
        }

        #sidebar .sidebar-header .user-pic {
            float: right;
            width: 60px;
            padding: 2px;
            border-radius: 12px;
            margin-left: 15px;
            overflow: hidden;
        }

        #sidebar .sidebar-header .user-pic img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }

        #sidebar .sidebar-header .user-info {
            color: #0ab9d3;
            text-align: right;
        }

        #sidebar .sidebar-content {
            clear: both;
            border-top: 1px solid #3a3f48;
        }

        #sidebar .sidebar-content ul {
            padding-right: 0;
        }

        #sidebar .sidebar-content ul li {
            display: block;
            height: 45px;
            line-height: 45px;
            border-bottom: 1px solid #3a3f48;
            text-align: right;
            padding: 5px 15px 5px 5px;
        }

        #sidebar .sidebar-content ul li:hover {
            background-color: #3e454c;
        }

        #sidebar .sidebar-content ul li a {
            display: block;
            color: white;
        }

        #sidebar .sidebar-content ul li a:hover {
            color: #00aced;
        }

        #sidebar .sidebar-content ul li a i {
            margin: 5px;
            padding: 3px;
            border-radius: 3px;
            background-color: #3e454c;
        }

        #sidebar .sidebar-content ul li a:hover i {
            background-color: #3e454c;
        }

        #content {
            padding: 75px 16px 1px;
            text-align: justify;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="m-0">
<div class="container-fluid m-0 p-0" style="direction: rtl">
    <?php include_once('left_menu.php'); ?>
    <div style="margin-right: 200px;">
        <?php include_once('header2.php'); ?>
        <div class="container-fluid" id="content">
            <?php
            $messages = getMessages();
            if (count($messages) > 0) {
                foreach ($messages as $message) {
                    ?>
                    <div class="alert alert-<?php echo $message['type']; ?> alert-dismissable text-right">
                        <?php echo $message['message']; ?>
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    </div>
                    <?php
                }
            }
            if (key_exists('module', $_GET) && !is_null($_GET['module'])) {
                if (key_exists('op', $_GET) && !is_null($_GET['op'])) {
                    $file = sprintf('Modules/%s/%s.php', $_GET['module'], $_GET['op']);
                    if (file_exists($file)) {
                        include_once($file);
                    } else {
                        die('<h3 class="text-center text-danger">صفحه مورد نظر یافت نشد.</h3>');
                    }
                } else {
                    $file = sprintf('Modules/%s/index.php', $_GET['module']);
                    if (file_exists($file)) {
                        include_once($file);
                    } else {
                        die('<h3 class="text-center text-danger">صفحه مورد نظر یافت نشد.</h3>');
                    }
                }
            } else {
                include_once('dashboard.content.php');
            }
            ?>
        </div>
    </div>
</div>
<?php
include_once('footer.php');
?>
</body>
<!-- END BODY-->
</html>
