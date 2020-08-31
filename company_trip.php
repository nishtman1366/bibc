<?
include_once('common.php');
require_once(TPATH_CLASS . 'savar/jalali_date.php');
$script = "Trips";
$tbl_name = 'register_driver';
$generalobj->check_member_login();
$abc = 'admin,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = '';
if ($action != '') {
    $startDate = $_REQUEST['startDate'];
    $endDate = $_REQUEST['endDate'];
    $gstartDate = savar_request_date_to_gregorian($_REQUEST['startDate']);
    $gendDate = savar_request_date_to_gregorian($_REQUEST['endDate']);
    if ($gstartDate != '') {
        $ssql .= " AND Date(t.tEndDate) >='" . $gstartDate . "'";
    }
    if ($gendDate != '') {
        $ssql .= " AND Date(t.tEndDate) <='" . $gendDate . "'";
    }
}
// ADDED BY SEYYED AMIR
$iCompanyId = $_SESSION['sess_iUserId'];
$sql = "SELECT `iCompanyId` FROM `company` where `iParentId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
$comp_childs = $obj->MySQLSelect($sql);
$comp_list = $iCompanyId;

foreach ($comp_childs as $comp) {
    $comp_list .= ',' . $comp['iCompanyId'];
}


////////////////////

$counter = isset($_REQUEST['counter']) ? 'yes' : '';
$iActive = isset($_REQUEST['iActive']) ? $_REQUEST['iActive'] : '';

#print_r($counter);die();

$groupby = "";
$counterStart = "";
if ($counter == 'yes') {
    $groupby = " GROUP BY t.iDriverId ";
    $counterStart = " , COUNT(*) as tripCount";
}


$whereActive = '';

if ($iActive == 'Finished') {
    $whereActive = " AND t.iActive = 'Finished'  ";
} else if ($iActive == 'Canceled') {
    $whereActive = " AND t.iActive = 'Canceled'  ";
} else if ($iActive == 'DriverCanceled') {
    $whereActive = " AND t.iActive = 'Canceled' AND vCancelReason != '' ";
} else if ($iActive == 'RiderCanceled') {
    $whereActive = " AND t.iActive = 'Canceled' AND vCancelReason = '' ";
}

$sql = "SELECT u.vName, u.vLastName,t.tEndDate, d.vAvgRating,d.iCompanyId,t.vRideNo, t.iFare, d.iDriverId,t.iActive, t.tSaddress, t.tDaddress,t.eType, d.vName AS name, d.vLastName AS lname,t.eCarType,t.iTripId,t.fTripGenerateFare,vt.vVehicleType_" . $_SESSION['sess_lang'] . " as vVehicleType , t.vCancelReason , t.vCancelComment
{$counterStart}
FROM register_driver d
RIGHT JOIN trips t ON d.iDriverId = t.iDriverId
LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId
LEFT JOIN  register_user u ON t.iUserId = u.iUserId
WHERE d.iCompanyId IN (" . $comp_list . ")" . $ssql . $whereActive . $groupby . " ORDER BY t.iTripId DESC";

if ($ssql == '')
    $sql .= " LIMIT 200";

//die($sql);


$db_trip = $obj->MySQLSelect($sql);

#print_r($db_trip);die();

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
$Today = jdate('Y-m-d');
$tdate = jdate("d") - 1;
$mdate = jdate("d");
$Yesterday = jdate("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

$curryearFDate = jdate("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = jdate("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = jdate("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = jdate("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));

$currmonthFDate = jdate("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = jdate("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = jdate("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = jdate("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = jdate('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = jdate('Y-m-d', strtotime('saturday this week'));

$Pmonday = jdate('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = jdate('Y-m-d', strtotime('saturday this week -1 week'));

?>
<!DOCTYPE html>
<html lang="en"
      dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $SITE_NAME ?> | Trips</title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>

    <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
    <!-- End: Default Top Script and css-->

</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <div class="page-contant">
        <div class="page-contant-inner">
            <h2 class="header-page"><?php echo $langage_lbl['LBL_COMPANY_TRIP_HEADER_TRIPS_TXT']; ?></h2>
            <!-- trips page -->
            <div class="trips-page">
                <form name="search" action="" method="post" onSubmit="return checkvalid()">
                    <input type="hidden" name="action" value="search"/>
                    <div class="Posted-date">
                        <h3><?php echo $langage_lbl['LBL_COMPANY_TRIP_SEARCH_RIDES_POSTED_BY_DATE']; ?></h3>
                        <span>
				      			<input type="text" id="dp4" name="startDate"
                                       placeholder="<?php echo $langage_lbl['LBL_FROM_DATE'] ?>" class="form-control"
                                       value=""/>
				      			<input type="text" id="dp5" name="endDate"
                                       placeholder="<?php echo $langage_lbl['LBL_TO_DATE'] ?>" class="form-control"
                                       value=""/>

                                <span class="select-wrapper" style="width: 24%">
                                    <select name="iActive" id="iActive">
                                        <option value="0">همه سفر ها</option>
                                        <option value="Finished" <?php if ($iActive == 'Finished') echo 'selected' ?>>سفرها پایان یافته</option>
                                        <option value="Canceled" <?php if ($iActive == 'Canceled') echo 'selected' ?>>سفرهای لغو شده</option>
                                        <option value="DriverCanceled" <?php if ($iActive == 'DriverCanceled') echo 'selected' ?>>سفرهای لغو(راننده)</option>
                                        <option value="RiderCanceled" <?php if ($iActive == 'RiderCanceled') echo 'selected' ?>>سفرهای لغو (مسافر)</option>
                                    </select>
		        			   </span>
					      	</span>

                    </div>

                    <div class="Posted-date" style="margin-top:20px">

                        <input type="password" id="" name="exp_password" placeholder="رمز گرفتن خروجی"
                               class="fform-control" style="min-width:100px" value=""/>
                        <a style="text-align: center;min-width:100px" href="javascript:void(0);"
                           class="btn btn-danger driver-trip-btn" onClick="exportlist();">خروجی گرفتن</a>

                    </div>


                    <div class="time-period">
                        <h3><?php echo $langage_lbl['LBL_COMPANY_TRIP_SEARCH_RIDES_POSTED_BY_TIME_PERIOD']; ?></h3>
                        <span>
								<a onClick="return todayDate('dp4','dp5');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></a>
								<a onClick="return yesterdayDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></a>
								<a onClick="return currentweekDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></a>
								<a onClick="return previousweekDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></a>
								<a onClick="return currentmonthDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></a>
								<a onClick="return previousmonthDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></a>
								<a onClick="return currentyearDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></a>
								<a onClick="return previousyearDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></a>
				      		</span>
                        <span style="margin-top:10px">
                                <label>شمارش تعداد سفر هر راننده<input type="checkbox" id="counter" name="counter"
                                                                       class="form-control"
                                                                       value="yes" <?php if ($counter == 'yes') echo 'checked' ?> style="float: right;width: 20px;margin: auto;height: 20px;"/></label>

                            </span>
                        <?php /*
                            <span style="margin-top:10px">
                                <label>فقط سفرهای موفق<input type="checkbox" id="counter" name="isFinished" class="form-control" value="yes" <?php if($isFinished == 'yes') echo 'checked' ?> style="float: right;width: 20px;margin: auto;height: 20px;" /></label>

                            </span>
                            */ ?>
                        <b>
                            <button class="driver-trip-btn"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
                        </b>

                    </div>
                </form>
                <div class="trips-table">
                    <div class="trips-table-inner">
                        <div class="driver-trip-table">
                            <table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
                                <thead>
                                <tr>
                                    <?php if ($APP_TYPE != 'UberX') { ?>
                                        <th>#</th>
                                        <th><?php echo $langage_lbl['LBL_TRIP_TYPE_TXT_ADMIN']; ?></th>
                                    <?php } ?>
                                    <th><?php echo $langage_lbl['LBL_Pick_Up']; ?></th>
                                    <th><?php echo $langage_lbl['LBL_COMPANY_TRIP_DRIVER']; ?></th>
                                    <?php if ($counter == 'yes') : ?>
                                        <th><?php echo 'تعداد سفر'; ?></th>
                                    <?php endif; ?>
                                    <th><?php echo $langage_lbl['LBL_COMPANY_TRIP_RIDER']; ?></th>
                                    <th><?php echo $langage_lbl['LBL_COMPANY_TRIP_Trip_Date']; ?></th>
                                    <th><?php echo $langage_lbl['LBL_COMPANY_TRIP_FARE_TXT']; ?></th>
                                    <th><?php echo $langage_lbl['LBL_COMPANY_TRIP_Car_Type']; ?></th>
                                    <th><?php echo $langage_lbl['LBL_COMPANY_TRIP_View_Invoice']; ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?
                                for ($i = 0; $i < count($db_trip); $i++) {

                                    $eType = $db_trip[$i]['eType'];
                                    $trip_type = ($eType == 'Ride') ? $langage_lbl['LBL_RIDE_TXT'] : $langage_lbl['LBL_DELIVER_TEXT'];

                                    if ($counter == 'yes') {
                                        $db_trip[$i]['tSaddress'] = $db_trip[$i]['tDaddress'] = '---';
                                        $db_trip[$i]['vName'] = $db_trip[$i]['vLastName'] = '---';
                                        $db_trip[$i]['fTripGenerateFare'] = 0;
                                    }
                                    ?>
                                    <tr class="gradeA" trip_id="<?php echo $db_trip[$i]['iTripId']; ?>">
                                        <?php if ($APP_TYPE != 'UberX') { ?>
                                            <td><?php echo $i + 1 ?></td>
                                            <td><?php echo $trip_type; ?> <br> <?php echo $db_trip[$i]['vRideNo']; ?>
                                            </td>
                                        <?php } ?>
                                        <td width="30%"><?php echo $db_trip[$i]['tSaddress'] . ' -> ' . $db_trip[$i]['tDaddress']; ?></td>
                                        <td>
                                            <?php echo $db_trip[$i]['name'] . " " . $db_trip[$i]['lname']; ?>
                                        </td>
                                        <?php if ($counter == 'yes') : ?>
                                            <td><?php echo $db_trip[$i]['tripCount']; ?></td>
                                        <?php endif; ?>

                                        <td>
                                            <?php echo $db_trip[$i]['vName'] . " " . $db_trip[$i]['vLastName']; ?>
                                        </td>
                                        <td>
                                            <?
                                            if ($db_trip[$i]['tEndDate'] != '0000-00-00 00:00:00') {
                                                echo jdate('d-M-Y H:i:s', strtotime($db_trip[$i]['tEndDate']));
                                            } else {
                                                echo "----";
                                            }
                                            ?>

                                        </td>
                                        <td align="center">
                                            <?php echo $generalobj->trip_currency($db_trip[$i]['fTripGenerateFare']); ?>
                                        </td>
                                        <td align="center">
                                            <?php echo $db_trip[$i]['vVehicleType']; ?>
                                        </td>
                                        <?php if ($db_trip[$i]['iActive'] == 'Canceled') {
                                            ?>
                                            <td class="center">
                                                <?php if ($db_trip[$i]['vCancelReason'] != '') :
                                                    ?><a style="color:red"
                                                         href="javascript:alert('<?php echo $db_trip[$i]['vCancelReason'] . '\n' . $db_trip[$i]['vCancelComment']; ?>')"><?php echo $langage_lbl['LBL_CANCELED']; ?></a>
                                                <?php else : ?>
                                                    <?php echo $langage_lbl['LBL_CANCELED']; ?>
                                                <?php endif; ?>
                                                <br>
                                                <a href="invoice.php?iTripId=<?php echo base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>"
                                                   target="_blank">
                                                    <img alt="" src="assets/img/invoice.png">
                                                </a>
                                            </td>
                                        <?php } else { ?>
                                            <td align="center" width="10%">
                                                <a href="invoice.php?iTripId=<?php echo base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>"
                                                   target="_blank">
                                                    <img alt="" src="assets/img/invoice.png">
                                                </a>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- -->
                <?php //if(SITE_TYPE=="Demo"){?>
                <!-- <div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
			      This feature will be enabled in the main product we will provide you.</span> </div>
			      <?php //}?> -->
                <!-- -->
            </div>
            <!-- -->
            <div style="clear:both;"></div>
        </div>
    </div>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- End:contact page-->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="assets/js/jquery.ui.datepicker-cc-fa.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#dp4").datepicker({
            dateFormat: "yy-mm-dd",
            changeYear: true,
            changeMonth: true,
            yearRange: "-100:+10"
        });
        $("#dp5").datepicker({
            dateFormat: "yy-mm-dd",
            changeYear: true,
            changeMonth: true,
            yearRange: "-100:+10"
        });
        if ('<?php echo $startDate?>' != '') {
            $("#dp4").val('<?php echo $startDate?>');
            //$("#dp4").datepicker('refresh');
        }
        if ('<?php echo $endDate?>' != '') {
            $("#dp5").val('<?php echo $endDate;?>');
            //$("#dp5").datepicker('refresh');
        }
        $('#dataTables-example').dataTable({
            "language": {
                <?php echo Datatablelang?>
            },
        });
        // formInit();
    });

    function todayDate() {
        $("#dp4").val('<?php echo $Today;?>');
        $("#dp5").val('<?php echo $Today;?>');
    }

    function yesterdayDate() {
        $("#dp4").val('<?php echo $Yesterday;?>');
        $("#dp5").val('<?php echo $Yesterday;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }

    function currentweekDate(dt, df) {
        $("#dp4").val('<?php echo $monday;?>');
        $("#dp5").val('<?php echo $sunday;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }

    function previousweekDate(dt, df) {
        $("#dp4").val('<?php echo $Pmonday;?>');
        $("#dp5").val('<?php echo $Psunday;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }

    function currentmonthDate(dt, df) {
        $("#dp4").val('<?php echo $currmonthFDate;?>');
        $("#dp5").val('<?php echo $currmonthTDate;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }

    function previousmonthDate(dt, df) {
        $("#dp4").val('<?php echo $prevmonthFDate;?>');
        $("#dp5").val('<?php echo $prevmonthTDate;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }

    function currentyearDate(dt, df) {
        $("#dp4").val('<?php echo $curryearFDate;?>');
        $("#dp5").val('<?php echo $curryearTDate;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }

    function previousyearDate(dt, df) {
        $("#dp4").val('<?php echo $prevyearFDate;?>');
        $("#dp5").val('<?php echo $prevyearTDate;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }

    function checkvalid() {
        if ($("#dp5").val() < $("#dp4").val()) {
            //bootbox.alert("<h4>From date should be lesser than To date.</h4>");
            bootbox.dialog({
                message: "<h4>From date should be lesser than To date.</h4>",
                buttons: {
                    danger: {
                        label: "OK",
                        className: "btn-danger"
                    }
                }
            });
            return false;
        }
    }
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $("[name='dataTables-example_length']").each(function () {
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $("[name='dataTables-example_length']").change(function () {
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    })

    function exportlist() {
        document.search.action = "export.php?trips";
        document.search.submit();
    }
</script>
<!-- End: Footer Script -->
</body>
</html>
