<?
include_once('../common.php');

include_once('savar_check_permission.php');
if (checkPermission('REPORTS') == false)
    die('you dont`t have permission...');

$tbl_name = 'trips';
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$abc = 'admin,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

//$generalobj->setRole($abc,$url);
$script = 'Driver Payment Report';

#echo "<pre>";print_r($_REQUEST);exit;


# Code For Settle Payment of Driver
$iCountryCode = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

//Country
$sql = "select iCountryId,vCountry,vCountryCode from country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);

//Select dates
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));

$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = date('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = date('Y-m-d', strtotime('saturday this week'));

$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));


$startDate = $monday;
$endDate = $sunday;
$ssql = "";
$success = 0;

if ($action != "" && $action == 'newsearch') {

    $iCountryCode = $_REQUEST['iCountryCode'];
    $startDate = date("Y-m-d", strtotime($_REQUEST['startDate']));
    $endDate = date("Y-m-d", strtotime($_REQUEST['endDate']));
    $iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : "0";

    if ($startDate != '') {
        $ssql .= " AND Date(tEndDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tEndDate) <='" . $endDate . "'";
    }

    $companySql = '';

    if ($iCompanyId != '0') {
        $companySQL .= " AND register_driver.iCompanyId = $iCompanyId ";
    }

    $sql = "select register_driver.iDriverId,eDriverPaymentStatus,concat(vName,' ',vLastName) as dname,vCountry,vBankAccountHolderName,vAccountNumber,vBankLocation,vBankName,vBIC_SWIFT_Code from register_driver
	LEFT JOIN trips ON trips.iDriverId=register_driver.iDriverId
	WHERE eDriverPaymentStatus='Unsettelled' $ssql $companySQL GROUP BY register_driver.iDriverId";

    $db_payment = $obj->MySQLSelect($sql);

    global $generalobj;
    $balance_ssql = "dDate BETWEEN '" . $startDate . "' AND  '" . $endDate . "'";

    for ($i = 0; $i < count($db_payment); $i++) {
        $db_payment[$i]['cashPayment'] = $generalobjAdmin->getAllCashCountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['cardPayment'] = $generalobjAdmin->getAllCardCountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['discount'] = $generalobjAdmin->getAllDiscountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['userBalance'] = $generalobj->get_user_available_balance_date($db_payment[$i]['iDriverId'], 'Driver', $balance_ssql);
        $db_payment[$i]['sumToPay'] = (int)$db_payment[$i]['transferAmount'] + (int)$db_payment[$i]['userBalance'];
    }
    // echo "<pre>";print_r($db_payment);exit;
}


if ($action == "pay_driver" && $_REQUEST['ePayDriver'] == "Yes") {

    $iCountryCode = $_REQUEST['prev_country'];
    $startDate = date("Y-m-d", strtotime($_REQUEST['prev_start']));
    $endDate = date("Y-m-d", strtotime($_REQUEST['prev_end']));

    if ($startDate != '') {
        $ssql .= " AND Date(tEndDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tEndDate) <='" . $endDate . "'";
    }
    if (SITE_TYPE != 'Demo') {
        foreach ($_REQUEST['iDriverId'] as $ids) {

            $sql1 = " UPDATE trips set eDriverPaymentStatus = 'Settelled'
			WHERE iDriverId = '" . $ids . "' AND eDriverPaymentStatus='Unsettelled' $ssql";
            $obj->sql_query($sql1);

            global $generalobj;
            $balance_ssql = "dDate BETWEEN '" . $startDate . "' AND  '" . $endDate . "'";
            $userBalance = $generalobj->get_user_available_balance_date($ids, 'Driver', $balance_ssql);

            $iUserId = $ids;
            $bAmount = $userBalance;
            $userType = 'Driver';
            $iTripId = 0;
            $eFor = "Withdrawl";
            $tDescription = "برداشت از کیف پول برای تسویه حساب تاریخ:" . $startDate . "و" . $endDate;
            $ePaymentStatus = "Unsettelled";
            $dDate = Date('Y-m-d H:i:s');

            $insert_user_wallet = $generalobj->InsertIntoUserWallet($iUserId
                , $userType
                , $bAmount
                , 'Debit'
                , $iTripId
                , $eFor
                , $tDescription
                , $ePaymentStatus
                , $dDate
            );
        }
        //echo "<pre>";print_r($db_payment1);exit;
        $success = 1;
    } else {
        $success = 2;
    }

    $sql = "select 
       register_driver.iDriverId,
       eDriverPaymentStatus,concat(vName,' ',vLastName) as dname,
       vCountry,
       vBankAccountHolderName,
       vAccountNumber,
       vBankLocation,
       vBankName,
       vBIC_SWIFT_Code 
        from register_driver
	        LEFT JOIN trips ON trips.iDriverId=register_driver.iDriverId
	    WHERE 
	          vCountry = '" . $iCountryCode . "' AND 
	          eDriverPaymentStatus='Unsettelled' $ssql 
	    
	    GROUP BY register_driver.iDriverId";




    $db_payment = $obj->MySQLSelect($sql);

    global $generalobj;
    $balance_ssql = "dDate BETWEEN '" . $startDate . "' AND  '" . $endDate . "'";

    for ($i = 0; $i < count($db_payment); $i++) {
        $db_payment[$i]['cashPayment'] = $generalobjAdmin->getAllCashCountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['cardPayment'] = $generalobjAdmin->getAllCardCountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['discount'] = $generalobjAdmin->getAllDiscountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($db_payment[$i]['iDriverId'], $ssql);
        $db_payment[$i]['userBalance'] = $generalobj->get_user_available_balance_date($db_payment[$i]['iDriverId'], 'Driver', $balance_ssql);
        $db_payment[$i]['sumToPay'] = (int)$db_payment[$i]['transferAmount'] + (int)$db_payment[$i]['userBalance'];
    }
}


$sql = "SELECT * from company WHERE eStatus='Active'";
$db_company = $obj->MySQLSelect($sql);


?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->

<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>گزارش پرداخت راننده | ادمین</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <?php include_once('global_files.php'); ?>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>

    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <h2>گزارش پرداخت راننده</h2>
            </div>
            <hr/>
            <?php if ($success == 1) { ?>
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    Record(s) mark as settlled successful.
                </div><br/>
            <?php } elseif ($success == 2) { ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    "Mark as Settlled Feature" has been disabled on the Demo Admin Panel. This feature will be enabled
                    on the main script we will provide you.
                </div><br/>
            <?php } ?>

            <div class="">
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    گزارش پرداخت راننده
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <div class="alert alert-error" id="alert" style="display: none;">
                                            <strong>Oh snap!</strong>

                                            <p></p>
                                        </div>

                                        <form name="search" action="" method="post" onSubmit="return checkvalid()">
                                            <div class="Posted-date mytrip-page">
                                                <input type="hidden" name="action" value="newsearch"/>
                                                <h3>انتخاب مدت زمان</h3>
                                                <span>
													<a onClick="return todayDate('dp4','dp5');">امروز</a>
													<a onClick="return yesterdayDate('dFDate','dTDate');">روز گذشته</a>
													<a onClick="return currentweekDate('dFDate','dTDate');">هفته جاری</a>
													<a onClick="return previousweekDate('dFDate','dTDate');">هفته گذشته</a>
													<a onClick="return currentmonthDate('dFDate','dTDate');">ماه جاری</a>
													<a onClick="return previousmonthDate('dFDate','dTDate');">ماه گذشته</a>
													<a onClick="return currentyearDate('dFDate','dTDate');">سال جاری</a>
													<a onClick="return previousyearDate('dFDate','dTDate');">سال گذشته</a>
													</span>
                                                <span>
                                                        <div class="col-lg-6">
                                                                <select name="iCompanyId" id="iCompanyId"
                                                                        class="form-control" required>
                                                                    <option value="0">انتخاب شرکت</option>
                                                                <?php for ($j = 0; $j < count($db_company); $j++) { ?>
                                                                    <option value="<?php echo $db_company[$j]['iCompanyId'] ?>" <?php if ($iCompanyId == $db_company[$j]['iCompanyId']) { ?> selected <?php } ?>><?php echo $db_company[$j]['vCompany'] ?></option>
                                                                <?php } ?>
                                                                </select>
                                                        </div>
                                                    </span>
                                                <span>
													<input type="text" id="dp4" name="startDate" placeholder="از تاریخ"
                                                           class="form-control" value="" required/>
													<input type="text" id="dp5" name="endDate" placeholder="تا تاریخ"
                                                           class="form-control" value="" required/>
													<b><button class="driver-trip-btn">جست و جو</button>
														<button type="button"
                                                                onClick="redirectpaymentpage('driver_pay_report.php');"
                                                                class="driver-trip-btn">پاک سازی لیست</button></b>
													</span>

                                                <?php if (count($db_payment) > 0) { ?>
                                                    <span><b><button type="button" class="driver-trip-btn"
                                                                     onclick="exportlist();">خروجی</button></b>
													</span>
                                                <?php } ?>


                                            </div>
                                        </form>

                                        <form name="frmpayment" id="frmpayment" method="post"
                                              action="javascript:void(0);">
                                            <input type="hidden" id="actionpay" name="action" value="pay_driver">
                                            <input type="hidden" name="ePayDriver" id="ePayDriver" value="">
                                            <input type="hidden" name="prev_country" id="prev_country"
                                                   value="<?php echo $iCountryCode; ?>">
                                            <input type="hidden" name="prev_start" id="prev_start"
                                                   value="<?php echo $startDate; ?>">
                                            <input type="hidden" name="prev_end" id="prev_end"
                                                   value="<?php echo $endDate; ?>">
                                            <table class="table table-striped table-bordered table-hover"
                                                   id="dataTables-example123"
                                                   <?php if ($action == ""){ ?>style="display:none;"<? } else { ?> style="display:;" <? } ?>>
                                                <thead>
                                                <tr>
                                                    <th>کد راننده</th>
                                                    <th>نام راننده</th>
                                                    <th>نام حساب راننده</th>
                                                    <th>نام بانک</th>
                                                    <th>شماره حساب</th>
                                                    <th>کل پول نقد پرداختی</th>
                                                    <th>تخفیف</th>
                                                    <th>کل پرداخت کارت</th>
                                                    <th>تعادل کیف پول کاربر</th>
                                                    <th>مقدار انتقال</th>
                                                    <th>مبلغ پرداختی</th>
                                                    <th>وضعیت پرداخت راننده</th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?
                                                if (count($db_payment) > 0) {

                                                    for ($i = 0; $i < count($db_payment); $i++) {
                                                        $driverId = $db_payment[$i]['iDriverId'];
                                                        $sql = "SELECT * FROM `savar_shaba` WHERE userId = '$driverId' AND userType = 'Driver'";
                                                        $resB = $obj->MySQLSelect($sql);
                                                        if (count($resB) > 0) {
                                                            $bankNumber = $resB[0]['shabaNumber'];
                                                            $bankUserName = $resB[0]['shabaName'];
                                                            $bankBank = $resB[0]['shabaBank'];
                                                        } else {
                                                            $bankNumber = '';
                                                            $bankUserName = '';
                                                            $bankBank = '';
                                                        }

                                                        $db_payment[$i]['vBIC_SWIFT_Code'] = '';
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td><?php echo $db_payment[$i]['iDriverId']; ?></td>
                                                            <td><?php echo $db_payment[$i]['dname']; ?></td>
                                                            <td><?php echo ($bankUserName != "") ? $bankUserName : '---'; ?></td>
                                                            <td><?php echo ($bankBank != "") ? $bankBank : '---'; ?></td>
                                                            <td><?php echo ($bankNumber != "") ? $bankNumber : '---'; ?></td>

                                                            <td><?php echo $db_payment[$i]['cashPayment']; ?></td>
                                                            <td><?php echo $db_payment[$i]['discount']; ?></td>
                                                            <td><?php echo $db_payment[$i]['cardPayment']; ?></td>
                                                            <td><?php echo $db_payment[$i]['userBalance']; ?></td>
                                                            <td><?php echo $db_payment[$i]['transferAmount']; ?></td>
                                                            <td><?php echo $db_payment[$i]['sumToPay']; ?></td>

                                                            <td><?php echo $db_payment[$i]['eDriverPaymentStatus']; ?></td>
                                                            <td>
                                                                <?
                                                                if ($db_payment[$i]['eDriverPaymentStatus'] == 'Unsettelled') {
                                                                    ?>
                                                                    <input class="validate[required]" type="checkbox"
                                                                           value="<?php echo $db_payment[$i]['iDriverId'] ?>"
                                                                           id="iTripId_<?php echo $db_payment[$i]['iDriverId'] ?>"
                                                                           name="iDriverId[]">
                                                                    <?
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                    <tr class="gradeA">
                                                        <td colspan="12" align="right">
                                                            <div class="row">
        													<span style="margin:26px 13px 0 0;">
        														<a onclick="javascript:Paytodriver(); return false;"
                                                                   href="javascript:void(0);"><button
                                                                            class="btn btn-primary ">Mark As Settelled</button></a>
        													</span>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                <? } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="12" style="text-align:center;"> No Payment Details
                                                            Found.
                                                        </td>
                                                    </tr>
                                                <? } ?>


                                                </tbody>
                                            </table>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<form name="_submit_this" id="_submit_this" action="">

</form>


<?php include_once('footer.php'); ?>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/uniform/jquery.uniform.min.js"></script>
<script src="../assets/plugins/inputlimiter/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="../assets/plugins/chosen/chosen.jquery.min.js"></script>
<script src="../assets/plugins/colorpicker/js/bootstrap-colorpicker.js"></script>
<script src="../assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
<script src="../assets/plugins/validVal/js/jquery.validVal.min.js"></script>
<script src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script src="../assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="../assets/plugins/autosize/jquery.autosize.min.js"></script>
<script src="../assets/plugins/jasny/js/bootstrap-inputmask.js"></script>
<script src="../assets/js/formsInit.js"></script>
<script>
    $(document).ready(function () {
        if ('<?php echo $startDate?>' != '') {
            $("#dp4").val('<?php echo $startDate?>');
            $("#dp4").datepicker('update', '<?php echo $startDate?>');
        }
        if ('<?php echo $endDate?>' != '') {
            $("#dp5").datepicker('update', '<?php echo $endDate;?>');
            $("#dp5").val('<?php echo $endDate;?>');
        }
        $('#dataTables-example').dataTable({
            "order": [[0, "desc"]]
        });
        formInit();
    });

    function setRideStatus(actionStatus) {
        window.location.href = "trip.php?type=" + actionStatus;
    }

    function todayDate() {
        $("#dp4").val('<?php echo $Today;?>');
        $("#dp5").val('<?php echo $Today;?>');
    }

    function reset() {
        location.reload();

    }

    function yesterdayDate() {
        $("#dp4").val('<?php echo $Yesterday;?>');
        $("#dp4").datepicker('update', '<?php echo $Yesterday;?>');
        $("#dp5").datepicker('update', '<?php echo $Yesterday;?>');
        $("#dp4").change();
        $("#dp5").change();
        $("#dp5").val('<?php echo $Yesterday;?>');
    }

    function currentweekDate(dt, df) {
        $("#dp4").val('<?php echo $monday;?>');
        $("#dp4").datepicker('update', '<?php echo $monday;?>');
        $("#dp5").datepicker('update', '<?php echo $sunday;?>');
        $("#dp5").val('<?php echo $sunday;?>');
    }

    function previousweekDate(dt, df) {
        $("#dp4").val('<?php echo $Pmonday;?>');
        $("#dp4").datepicker('update', '<?php echo $Pmonday;?>');
        $("#dp5").datepicker('update', '<?php echo $Psunday;?>');
        $("#dp5").val('<?php echo $Psunday;?>');
    }

    function currentmonthDate(dt, df) {
        $("#dp4").val('<?php echo $currmonthFDate;?>');
        $("#dp4").datepicker('update', '<?php echo $currmonthFDate;?>');
        $("#dp5").datepicker('update', '<?php echo $currmonthTDate;?>');
        $("#dp5").val('<?php echo $currmonthTDate;?>');
    }

    function previousmonthDate(dt, df) {
        $("#dp4").val('<?php echo $prevmonthFDate;?>');
        $("#dp4").datepicker('update', '<?php echo $prevmonthFDate;?>');
        $("#dp5").datepicker('update', '<?php echo $prevmonthTDate;?>');
        $("#dp5").val('<?php echo $prevmonthTDate;?>');
    }

    function currentyearDate(dt, df) {
        $("#dp4").val('<?php echo $curryearFDate;?>');
        $("#dp4").datepicker('update', '<?php echo $curryearFDate;?>');
        $("#dp5").datepicker('update', '<?php echo $curryearTDate;?>');
        $("#dp5").val('<?php echo $curryearTDate;?>');
    }

    function previousyearDate(dt, df) {
        $("#dp4").val('<?php echo $prevyearFDate;?>');
        $("#dp4").datepicker('update', '<?php echo $prevyearFDate;?>');
        $("#dp5").datepicker('update', '<?php echo $prevyearTDate;?>');
        $("#dp5").val('<?php echo $prevyearTDate;?>');
    }

    function checkvalid() {
        if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.");
            return false;
        }
    }

    function redirectpaymentpage(url) {
        //$("#frmsearch").reset();
        // document.getElementById("action").value = '';
        // document.getElementById("frmsearch").reset();
        window.location = url;
    }

    function getCheckCount(frmpayment) {
        var x = 0;
        var threasold_value = 0;
        for (i = 0; i < frmpayment.elements.length; i++) {
            if (frmpayment.elements[i].checked == true) {
                x++;
            }
        }
        return x;
    }


    function Paytodriver() {
        y = getCheckCount(document.frmpayment);

        if (y > 0) {
            ans = confirm("Are you sure you want to Pay To Driver?");
            if (ans == false) {
                return false;
            }
            $("#ePayDriver").val('Yes');
            $("#frmpayment").attr('action', '');
            document.frmpayment.submit();
        } else {
            alert("Select record for Pay To Driver");
            return false;
        }
    }

    function exportlist() {
        $("#actionpay").val('export');
        $("#frmpayment").attr('action', "export_driver_pay_details.php");
        document.frmpayment.submit();
    }

    function search_filters() {
        document.search.action = "";
        document.search.submit();
    }


    /*$('#dataTables-example').DataTable( {
       paging: false
     } );*/

</script>
</body>
<!-- END BODY-->
</html>
