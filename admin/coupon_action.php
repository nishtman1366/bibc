<?php
include_once('../common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$script = "Coupon";
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$sql = "select * from coupon";
$db_coupon = $obj->MySQLSelect($sql);

//For Currency
$sql = "select * from  currency where eStatus='Active'";
$db_currency = $obj->MySQLSelect($sql);

$iCouponId = isset($_REQUEST['iCouponId']) ? $_REQUEST['iCouponId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($iCouponId != '') ? 'Edit' : 'Add';

$tbl_name = 'coupon';
//$script = 'Driver';

$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);

#$sql = "select * from company where eStatus != 'Deleted'";
#$db_company = $obj->MySQLSelect($sql);

//echo '<prE>'; print_R($_REQUEST); exit;
// set all variables with either post (when submit) either blank (when insert)
$iCouponId = isset($_REQUEST['iCouponId']) ? $_REQUEST['iCouponId'] : '';
$vCouponCode = isset($_REQUEST['vCouponCode']) ? $_REQUEST['vCouponCode'] : '';
$fDiscount = isset($_REQUEST['fDiscount']) ? $_REQUEST['fDiscount'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$eValidityType = isset($_REQUEST['eValidityType']) ? $_REQUEST['eValidityType'] : '';
$dActiveDate = isset($_REQUEST['dActiveDate']) ? $_REQUEST['dActiveDate'] : '';
$dExpiryDate = isset($_REQUEST['dExpiryDate']) ? $_REQUEST['dExpiryDate'] : '';
$iUsageLimit = isset($_REQUEST['iUsageLimit']) ? $_REQUEST['iUsageLimit'] : '';
$iUsed = isset($_REQUEST['iUsed']) ? $_REQUEST['iUsed'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
$tDescription = isset($_REQUEST['tDescription']) ? $_REQUEST['tDescription'] : '';
$eOnePerUser = isset($_REQUEST['eOnePerUser']) ? $_REQUEST['eOnePerUser'] : 'No';
$eForFirstTrip = isset($_REQUEST['eForFirstTrip']) ? $_REQUEST['eForFirstTrip'] : 'No';


if (isset($_POST['submit'])) {
    #echo '<pre>'; print_r($_POST); exit;

    if (!empty($iCouponId)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:coupon_action.php?iCouponId=" . $iCouponId . '&success=2');
            exit;
        }

    }
//End :: Upload Image Script
    $q = "INSERT INTO ";
    $where = '';
    if ($action == 'Edit') {
        $str = " ";
    } else {
        $str = " , eStatus = 'Inactive' ";
    }

    if (SITE_TYPE == 'Demo') {
        $str = " , eStatus = 'active' ";
    }

    if ($eValidityType == 'Permanent') {

        $dActiveDate = '';
        $dExpiryDate = '';

    } else {

        $dActiveDate = $dActiveDate;
        $dExpiryDate = $dExpiryDate;

    }

    if ($iCouponId != '') {
        $q = "UPDATE ";
        $where = " WHERE `iCouponId` = '" . $iCouponId . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET
		`vCouponCode` = '" . $vCouponCode . "',
		`fDiscount` = '" . $fDiscount . "',
		`eType` = '" . $eType . "',
		`eValidityType` = '" . $eValidityType . "',
		`dActiveDate` = '" . $dActiveDate . "',
		`dExpiryDate` = '" . $dExpiryDate . "',
		`iUsageLimit` = '" . $iUsageLimit . "',
		`tDescription` = '" . $tDescription . "',
    `eStatus` = '" . $eStatus . "' ";

    if ($eForFirstTrip != '')
        $query .= ", `eForFirstTrip` = '" . $eForFirstTrip . "' ";

    if ($eOnePerUser != '')
        $query .= " , `eOnePerUser` = '" . $eOnePerUser . "' ";


    $query .= $where;

    #echo '<pre>'; print_r($query); exit;
    $obj->sql_query($query);


    $iCouponId = ($iCouponId != '') ? $iCouponId : mysql_insert_id();
    if ($action == 'Add') {
        header("Location:coupon.php?&success=1");
    } else {

        header("Location:coupon.php?&success=1");


    }
}
// for Edit

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCouponId = '" . $iCouponId . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_R($db_data);echo "</pre>";
    $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCouponCode = $value['vCouponCode'];
            $fDiscount = $value['fDiscount'];
            $eType = $value['eType'];
            $eValidityType = $value['eValidityType'];
            $dActiveDate = $value['dActiveDate'];
            $dExpiryDate = $value['dExpiryDate'];
            $iUsageLimit = $value['iUsageLimit'];
            $iUsed = $value['iUsed'];
            $eStatus = $value['eStatus'];
            $tDescription = $value['tDescription'];
            $eOnePerUser = $value['eOnePerUser'];
            $eForFirstTrip = $value['eForFirstTrip'];
            #$vCurrencyDriver=$value['vCurrencyDriver'];

        }
    }
}

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
    <title>Admin | PromoCode
        <?php echo $action; ?>
    </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>
                        <?php echo $action; ?>
                        کد تخفیف
                        <?php echo $vName; ?>
                    </h2>
                    <a href="coupon.php">
                        <input type="button" value="بازگشت" class="add-btn">
                    </a></div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group"><span style="color: red;font-size: small;" id="coupon_status"></span>
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            کد تخفیف با موفقیت ویرایش شد
                        </div>
                        <br/>
                    <? } ?>
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will
                            be enabled on the main script we will provide you.
                        </div>
                        <br/>
                    <? } ?>
                    <?php if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            "مقدار درصد بین 1 تا 100 باید باشد
                        </div>
                        <br/>
                    <? } ?>
                    <form method="post" action="" enctype="multipart/form-data" class="">
                        <input type="hidden" name="iCouponId" value="<?php if (isset($db_data[0]['iCouponId'])) {
                            echo $db_data[0]['iCouponId'];
                        } ?>">
                        <div class="row coupon-action-n1">
                            <div class="col-lg-12">
                                <label>کد کوپن * :<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vCouponCode"
                                       onBlur=" validate_coupon(this.value)" id="vCouponCode"
                                       value="<?php echo $vCouponCode; ?>" placeholder="Coupon Code" required>
                                <a style="margin: 0 !important;" class="btn btn-sm btn-info"
                                   onClick="randomStringToInput(this)">تولید کد کوپن</a></div>

                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>توضیحات :<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                <textarea name="tDescription" rows="5" cols="40" class="form-control" id="tDescription"
                          placeholder="Description" required><?php echo $tDescription; ?>
</textarea>
                            </div>
                        </div>
                        <div class="row coupon-action-n2">
                            <div class="col-lg-12">
                                <label>تخفیف * :<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" class="form-control" name="fDiscount" min="0" id="fDiscount"
                                       value="<?php echo $fDiscount; ?>" placeholder="Discount" required>
                                <select id="eType" name="eType" onChange="coupon(this.value)" class="form-control">
                                    <option value="cash"
                                            <?php if ($db_data[0]['eType'] == "cash"){ ?>selected <?php } ?> >$
                                    </option>
                                    <option value="percentage" <?php if ($db_data[0]['eType'] == "percentage") { ?> selected <?php } ?> >
                                        %
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>اعتبار :<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="radio" name="eValidityType" onClick="showhidedate(this.value)"
                                       value="Permanent"
                                    <?php if ($db_data[0]['eValidityType'] == "Permanent") { ?> checked <?php } ?> >
                                دائمی
                                <input type="radio" name="eValidityType" onClick="showhidedate(this.value)"
                                       value="Defined" <?php if ($db_data[0]['eValidityType'] == "Defined") { ?> checked <?php } ?> >
                                سفارشی
                            </div>
                            <div id="emailCheck"></div>
                        </div>
                        <div class="row" id="date1" style="display:none;">
                            <div class="col-lg-12">
                                <label>تاریخ فعال سازی :</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" style="float: left;margin-right: 10px; width:45% "
                                       class="form-control" name="dActiveDate" id="dActiveDate"
                                       value="<?php echo $dActiveDate ?>" placeholder="Activation Date">
                            </div>
                        </div>
                        <div class="row" id="date2" style="display:none;">
                            <div class="col-lg-12">
                                <label>تاریخ انقضا:</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" style="float: left;margin-right: 10px; width:45% "
                                       class="form-control" name="dExpiryDate" value="<?php echo $dExpiryDate ?>"
                                       id="dExpiryDate" placeholder="Expiry Date">
                            </div>
                        </div>
                        <div class="row coupon-action-n3">
                            <div class="col-lg-12">
                                <label>محدودیت استفاده :<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" min="0" id="iUsageLimit" value="<?php echo $iUsageLimit ?>"
                                       name="iUsageLimit" title="Usage Limit" class="form-control"/>
                            </div>
                        </div>
                        <div class="row coupon-action-n3">
                            <div class="col-lg-12">
                                <label>وضعیت<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select id="eStatus" name="eStatus" class="form-control ">
                                    <option value="Active"
                                            <?php if ($db_data[0]['eStatus'] == "Active"){ ?>selected <?php } ?> >فعال
                                    </option>
                                    <option value="Inactive"
                                            <?php if ($db_data[0]['eStatus'] == "Inactive"){ ?>selected <?php } ?> >غیر
                                        فعال
                                    </option>
                                </select>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-lg-2">
                                یک نفر کاربر
                            </div>
                            <div class="col-lg-2">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" class="chk" name="eOnePerUser"
                                           <?php if ($eOnePerUser == 'Yes'){ ?>checked<?php } ?> value="Yes"/>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-2">
                                فقط برای اولین سفر
                            </div>
                            <div class="col-lg-2">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" class="chk" name="eForFirstTrip"
                                           <?php if ($eForFirstTrip == 'Yes'){ ?>checked<?php } ?> value="Yes"/>
                                </div>
                            </div>
                        </div>


                        <div class="row coupon-action-n4">
                            <div class="col-lg-12">
                                <input type="submit" class="save btn-info" name="submit" id="submit"
                                       value="<?php echo $action; ?> PromoCode">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    function coupon(dis) {
        var bla = $('#fDiscount').val();
        if (dis == 'percentage') {
            if (bla > 100) {
                alert("Please Enter 1 to 100 Discount");
            }
        }
    }

    function validate_coupon(username) {
        var request = $.ajax({
            type: "POST",
            url: 'ajax_validate_coupon.php',
            data: 'vCouponCode=' + username,
            success: function (data) {
                if (data == 0) {
                    $('#coupon_status').html('<i class="icon icon-remove alert-danger alert"> 	Coupon Code Already Exist</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');

                    return false;
                } else if (data == 1) {
                    $('#coupon_status').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                    $('vCouponCode[type="submit"]').removeAttr('disabled');
                } else if (data == 2) {
                    $('#coupon_status').html('<i class="icon icon-remove alert-danger alert"> Please Enter Coupon Code</i>');
                    $('vCouponCode[type="submit"]').removeAttr('disabled');
                }
            }

        });
    }

</script>
<!--link rel="stylesheet" media="all" type="text/css" href="../assets/js/dtp/jquery-ui.css" />
          <link rel="stylesheet" media="all" type="text/css" href="../assets/js/dtp/jquery-ui-timepicker-addon.css" />

          <script type="text/javascript" src="../assets/js/dtp/jquery-ui.min.js"></script>
          <script type="text/javascript" src="../assets/js/dtp/jquery-ui-timepicker-addon.js"></script-->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"
        type="text/javascript"></script>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css"
      rel="Stylesheet" type="text/css"/>
<?php if ($action == 'Edit') { ?>
    <script>
        window.onload = function () {
            showhidedate('<?php echo $eValidityType; ?>');
        };
    </script>
<? }else{
?>
    <script>

        window.onload = function () {

            $('input:radio[name=eValidityType][value=Permanent]').attr('checked', true);
        };

    </script>
<?php } ?>
<script type="text/javascript">

    /*$(function() {
        $("#dActiveDate").datepicker({
          minDate: 0,
          dateFormat: "yy-mm-dd",
          showOn: "button",
          buttonImage: "http://192.168.1.131/uber-app/web-new/assets/img/cal-icon.gif",
          buttonImageOnly: true
        });
        $("#dExpiryDate").datepicker({
                minDate: 0,
          dateFormat: "yy-mm-dd",
          showOn: "button",
          buttonImage: "http://192.168.1.131/uber-app/web-new/assets/img/cal-icon.gif",
          buttonImageOnly: true
        });

        $("#dActiveDate").on("dp.change", function (e) {
    $('#dExpiryDate').data("DateTimePicker").minDate(e.date);
});
$("#dActiveDate").on("dp.change", function (e) {
    $('#dExpiryDate').data("DateTimePicker").maxDate(e.date);
});
      });*/

    $(function () {
        $("#dActiveDate").datepicker({
            numberOfMonths: 2,
            dateFormat: "yy-mm-dd",
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() + 1);
                $("#dExpiryDate").datepicker("option", "minDate", dt);
            }
        });
        $("#dExpiryDate").datepicker({
            numberOfMonths: 2,
            dateFormat: "yy-mm-dd",
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() - 1);
                $("#dActiveDate").datepicker("option", "maxDate", dt);
            }
        });
    });


    function showhidedate(val) {
        if (val == "Defined") {
            document.getElementById("date1").style.display = '';
            document.getElementById("date2").style.display = '';
            document.getElementById("dActiveDate").lang = '*';
            document.getElementById("dExpiryDate").lang = '*';
        } else {
            document.getElementById("date1").style.display = 'none';
            document.getElementById("date2").style.display = 'none';
            document.getElementById("dActiveDate").lang = '';
            document.getElementById("dExpiryDate").lang = '';

        }
    }

    function randomStringToInput(clicked_element) {
        var self = $(clicked_element);
        var random_string = generateRandomString(6);
        $('input[name=vCouponCode]').val(random_string);

    }

    function generateRandomString(string_length) {
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var string = '';
        for (var i = 0; i <= string_length; i++) {
            var rand = Math.round(Math.random() * (characters.length - 1));
            var character = characters.substr(rand, 1);
            string = string + character;
        }
        return string;
    }
</script>
</body>
</html>
