<?

include_once('../common.php');
require_once(TPATH_CLASS . 'savar/jalali_date.php');

$tbl_name = 'register_driver';
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$abc = 'admin,company';


include_once('savar_check_permission.php');
if (checkPermission('TRIPS') == false)
    die('you dont`t have permission...');


$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$actionType = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
//$generalobj->setRole($abc,$url);
#echo $actionType;exit;
#echo"<pre>";print_r($_REQUEST);exit;
$script = 'Trips';

$sql = "select * from company WHERE eStatus != 'Deleted'";
$db_company = $obj->MySQLSelect($sql);

$sql = "select * from register_driver WHERE eStatus != 'Deleted'";
$db_company = $obj->MySQLSelect($sql);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = '';
if ($action != '') {
    $startDate = $_REQUEST['startDate'];
    $endDate = $_REQUEST['endDate'];
    if ($startDate != '') {
        $ssql .= " AND Date(t.tStartDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(t.tEndDate) <='" . $endDate . "'";
    }
}
$ssl = "";


$cmp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $cmp_ssql = " And t.tEndDate > '" . WEEK_DATE . "'";
}
if ($actionType == "onRide") {
    $ssl = " AND (t.iActive = 'On Going Trip' OR t.iActive = 'Active') AND t.eCancelled='No'";
} else if ($actionType == "cancel") {
    $ssl = " AND t.iActive = 'Canceled' OR t.eCancelled='yes'";
} else if ($actionType == "complete") {
    $ssl = " AND t.iActive = 'Finished' AND t.eCancelled='No'";
}
/* $sql = "SELECT u.vName, u.vLastName, d.vAvgRating, t.tStartDate ,t.tEndDate, t.tTripRequestDate,t.vCancelReason,t.vCancelComment, t.iFare,t.eType,d.iDriverId, t.tSaddress,t.vRideNo, t.tDaddress, d.vName AS name,c.vName AS comp,c.vCompany, d.vLastName AS lname,t.eCarType,t.iTripId,vt.vVehicleType,t.iActive
FROM register_driver d
RIGHT JOIN trips t ON d.iDriverId = t.iDriverId
LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId
LEFT JOIN  register_user u ON t.iUserId = u.iUserId JOIN company c ON c.iCompanyId=d.iCompanyId
WHERE 1=1".$ssql.$ssl.$cmp_ssql."
ORDER BY t.iTripId DESC ";*/


if ($_GET['pagerecord'] == '') {
    $sql = "SELECT u.vName, u.vLastName, d.vAvgRating, t.tStartDate ,t.tEndDate, t.tTripRequestDate,t.vCancelReason,t.vCancelComment, t.iFare,t.eType,d.iDriverId, t.tSaddress,t.vRideNo, t.tDaddress, d.vName AS name,c.vName AS comp,c.vCompany, d.vLastName AS lname,t.eCarType,t.iTripId,vt.vVehicleType,t.iActive
 FROM register_driver d
 RIGHT JOIN trips t ON d.iDriverId = t.iDriverId
 LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId
 LEFT JOIN  register_user u ON t.iUserId = u.iUserId JOIN company c ON c.iCompanyId=d.iCompanyId
 WHERE 1=1" . $ssql . $ssl . $cmp_ssql . "
 ORDER BY t.iTripId DESC  limit 0,500";
} else {
    $sql = "SELECT u.vName, u.vLastName, d.vAvgRating, t.tStartDate ,t.tEndDate, t.tTripRequestDate,t.vCancelReason,t.vCancelComment, t.iFare,t.eType,d.iDriverId, t.tSaddress,t.vRideNo, t.tDaddress, d.vName AS name,c.vName AS comp,c.vCompany, d.vLastName AS lname,t.eCarType,t.iTripId,vt.vVehicleType,t.iActive
 FROM register_driver d
 RIGHT JOIN trips t ON d.iDriverId = t.iDriverId
 LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId
 LEFT JOIN  register_user u ON t.iUserId = u.iUserId JOIN company c ON c.iCompanyId=d.iCompanyId
 WHERE 1=1" . $ssql . $ssl . $cmp_ssql . "
 ORDER BY t.iTripId DESC  limit " . $_GET['pagerecord'] * 500 . ',500';
}
$data_drv = $obj->MySQLSelect($sql);


$sql4 = "SELECT iTripId FROM trips WHERE 1=1" . $ssql . $ssl . $cmp_ssql . ' AND 1=1 order by iTripId DESC ';

$data_drv_recordcount = $obj->MySQLSelect($sql4);


if ($ssql == '') {
    $sql .= " ";
} else {
    $sql .= " ";
}

$db_trip = $obj->MySQLSelect($sql);
#echo "<pre>";print_r($sql);exit;
/* $sql = "select iDriverId from register_driver where iCompanyId = '".$_SESSION['sess_iCompanyId']."'";
$db_sql = $obj->MySQLSelect($sql);
for($i=0;$i<count($db_sql);$i++)
{
   $db[$i] = $db_sql[$i]['iDriverId'];
}
$ids = implode(",",$db);
$sql = "SELECT t.tSaddress,t.tDaddress,t.iFare,t.iActive,t.iDriverId,d.vName from trips t left join register_driver d on t.iDriverId = d.iDriverId where d.iDriverId IN (".$ids.")";
$db_trip = $obj->MySQLSelect($sql); */
#echo '<pre>'; print_R($db_trip); echo '</pre>';
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
    <title>ادمین | سفرها </title>
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
    <?php include_once('header2.php'); ?>
    <?php include_once('left_menu.php'); ?>

    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <h2>سفر </h2>
            </div>
            <hr/>
            <div class="">
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">
                                <div class="panel-heading driver-neww1 driver-neww2">
                                    <b>سفر </b>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <div class="alert alert-error" id="alert" style="display: none;">
                                            <strong>Oh snap!</strong>
                                            <p></p>
                                        </div>
                                        <form name="search" action="" method="post" onSubmit="return checkvalid()">
                                            <div class="Posted-date mytrip-page">
                                                <input type="hidden" name="action" value="search"/>
                                                <h3>جست و جو راننده ها بر اساس تاریخ</h3>
                                                <span>
													<a style="cursor:pointer" onClick="return todayDate('dp4','dp5');">امروز</a>
													<a style="cursor:pointer"
                                                       onClick="return yesterdayDate('dFDate','dTDate');">روز گذشته</a>
													<a style="cursor:pointer"
                                                       onClick="return currentweekDate('dFDate','dTDate');">هفته جاری</a>
													<a style="cursor:pointer"
                                                       onClick="return previousweekDate('dFDate','dTDate');">هفته گذشته</a>
													<a style="cursor:pointer"
                                                       onClick="return currentmonthDate('dFDate','dTDate');">ماه جاری</a>
													<a style="cursor:pointer"
                                                       onClick="return previousmonthDate('dFDate','dTDate');">ماه گذشته</a>
													<a style="cursor:pointer"
                                                       onClick="return currentyearDate('dFDate','dTDate');">سال جاری</a>
													<a style="cursor:pointer"
                                                       onClick="return previousyearDate('dFDate','dTDate');">سال گذشته</a>
													</span>
                                                <span>
													<input type="text" id="dp4" name="startDate" placeholder="از تاریخ"
                                                           class="form-control" value="" readonly=""
                                                           style="cursor:default; background-color: #fff"/>
													<input type="text" id="dp5" name="endDate" placeholder="تا تاریخ"
                                                           class="form-control" value="" readonly=""
                                                           style="cursor:default; background-color: #fff"/>
													<b><button class="driver-trip-btn">جست و جو</button>
														<button onClick="reset();"
                                                                class="driver-trip-btn">پاک کردن لیست</button></b>
													</span>
                                            </div>
                                        </form>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <label>فیلتر بر اساس وضعیت سفر :</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <select class="form-control" name='vStatus'
                                                        onChange="setRideStatus(this.value);">
                                                    <option value="">همه</option>
                                                    <option value="onRide" <?php if ($actionType == "onRide") {
                                                        echo "selected";
                                                    } ?>>در حال سفر
                                                    </option>
                                                    <option value="complete" <?php if ($actionType == "complete") {
                                                        echo "selected";
                                                    } ?>>تکمیل شده
                                                    </option>
                                                    <option value="cancel" <?php if ($actionType == "cancel") {
                                                        echo "selected";
                                                    } ?>>کنسل شده
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        </br>
                                        <div style="margin: 13px;margin-top: -6px;">
                                            تعداد رکورد در هر لیست<br>
                                            <select class="form-control" name='iCompanyId' id='iCompanyId'
                                                    onchange="location = this.value;">
                                                <!--<option value="map.php">--select--</option>-->
                                                <?php $listrecord1 = 1;
                                                $listrecord2 = count($data_drv_recordcount) / 500;
                                                for ($i = 0; $i < count($data_drv_recordcount) / 500; $i++) {

                                                    if ($_GET['pagerecord'] == $i) {
                                                        echo '<option selected value ="trip.php?pagerecord=' . $i . '">' . $listrecord1 . ' to ' . $listrecord1 += 500 . " </option>";
                                                    } else {
                                                        echo '<option value ="trip.php?pagerecord=' . $i . '">' . $listrecord1 . ' to ' . $listrecord1 += 500 . " </option>";
                                                    }
                                                    //$listrecord1 += 1000;
                                                } ?>
                                            </select>
                                        </div>


                                        <table class="table table-striped table-bordered table-hover"
                                               id="dataTables-example">
                                            <thead>
                                            <tr>
                                                <?php if ($APP_TYPE != 'UberX') { ?>
                                                    <th>نوع خودرو</th>
                                                <?php } ?>
                                                <th><?php echo $langage_lbl_admin['LBL_TRIP_NO']; ?></th>
                                                <th>آدرس</th>
                                                <th>تاریخ سفر</th>
                                                <th>شرکت</th>
                                                <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> </th>
                                                <th><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> </th>
                                                <th>کرایه</th>
                                                <th>خودرو</th>
                                                <th>مشاهده صورت حساب</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?
                                            //Mamad H . A . M Change count($db_trip) to 500
                                            for ($i = 0; $i < count($db_trip); $i++) {

                                                //print_r($db_trip);  exit;
                                                $eType = $db_trip[$i]['eType'];
                                                $trip_type = ($eType == 'Ride') ? 'Ride' : 'Delivery';
                                                ?>
                                                <tr class="gradeA">
                                                    <?php if ($APP_TYPE != 'UberX') { ?>

                                                        <td>
                                                            <?php echo $trip_type; ?>
                                                        </td>
                                                    <?php } ?>
                                                    <td>
                                                        <?php echo $db_trip[$i]['vRideNo']; ?>
                                                    </td>
                                                    <td width="30%"
                                                        data-order="<?php echo $db_trip[$i]['iTripId'] ?>"><?php echo $db_trip[$i]['tSaddress'] . ' -> ' . $db_trip[$i]['tDaddress']; ?></td>
                                                    <!--<td><?php echo date('d F, Y', strtotime($db_trip[$i]['tStartDate'])); ?></td>-->
                                                    <td><?php if ($db_trip[$i]['tEndDate'] == "0000-00-00 00:00:00") {
                                                            echo jdate('d-F-Y', strtotime($db_trip[$i]['tTripRequestDate']));
                                                        } else {
                                                            echo jdate('d-F-Y', strtotime($db_trip[$i]['tEndDate']));
                                                        } ?></td>
                                                    <td>
                                                        <?php echo $db_trip[$i]['vCompany']; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $db_trip[$i]['name'] . " " . $db_trip[$i]['lname']; ?>
                                                    </td>

                                                    <td>
                                                        <?php echo $db_trip[$i]['vName'] . " " . $db_trip[$i]['vLastName']; ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php echo $generalobj->trip_currency($db_trip[$i]['iFare']); ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php echo $db_trip[$i]['vVehicleType']; ?>

                                                    </td>
                                                    <td align="center" width="10%">
                                                        <?php if ($db_trip[$i]['iFare'] != 0 or $db_trip[$i]['iActive'] == 'Finished') { ?>
                                                            <a href="javascript:void(0);"
                                                               onclick='javascript:window.open("invoice.php?iTripId=<?php echo $db_trip[$i]['iTripId'] ?>","_blank")'
                                                               ;">
                                                            <button class="btn btn-primary">
                                                                <i class="icon-th-list  icon-white"><b>View Invoice</b></i>
                                                            </button>
                                                            </a>
                                                        <? } else {
                                                            if ($db_trip[$i]['iActive'] == "Active" or $db_trip[$i]['iActive'] == "On Going Trip") {
                                                                echo "On Ride";
                                                            } else if ($db_trip[$i]['iActive'] == "Canceled" && $db_trip[$i]['vCancelReason'] != '') { ?>
                                                                <a href="javascript:void(0);" class="btn btn-info"
                                                                   data-toggle="modal"
                                                                   data-target="#uiModal1_<?php echo $db_trip[$i]['iTripId']; ?>">Cancel
                                                                    Reason</a>

                                                            <?php } else if ($db_trip[$i]['iActive'] == "Canceled") {

                                                                echo "Cancelled";
                                                            } else {
                                                                echo $db_trip[$i]['iActive'];
                                                            }

                                                        } ?>
                                                    </td>
                                                </tr>
                                                <div class="clear"></div>
                                                <div class="modal fade"
                                                     id="uiModal1_<?php echo $db_trip[$i]['iTripId']; ?>" tabindex="-1"
                                                     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                    <div class="modal-content image-upload-1" style="width:400px;">
                                                        <div class="upload-content" style="width:350px;">
                                                            <h3>Trip Cancel Reason</h3>
                                                            <h4>Cancel
                                                                Reason: <?php echo $db_trip[$i]['vCancelReason'] . " " . $db_trip[$i]['vCancelComment']; ?></h4>
                                                            <form class="form-horizontal" id="frm6" method="post"
                                                                  enctype="multipart/form-data" action="" name="frm6">


                                                                <input type="button" class="save" data-dismiss="modal"
                                                                       name="cancel" value="Close">
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php } ?>


                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

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
            alert("From date should be lesser than To date.")
            return false;
        }
    }
</script>
</body>
<!-- END BODY-->
</html>
