<?php
include_once("common.php");
require_once(TPATH_CLASS . 'savar/jalali_date.php');
//$generalobj->check_member_login();
$script = 'Booking';

$tbl_name = 'cab_booking';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$iCabBookingId = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : '';
$iCompanyId = $_SESSION['sess_iUserId'];
$action = ($iCabBookingId != '') ? 'Edit' : 'Add';
//For Country
$sql = "SELECT * from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
//For Currency
$sql = "select * from  currency where eStatus='Active'";
$db_currency = $obj->MySQLSelect($sql);
//For Area
$sql = "SELECT area.aid,area.sAreaNamePersian,area.mapCenter,area.mapZoom FROM `savar_area` as area,`company` as com where com.iCompanyId = {$iCompanyId} AND com.iAreaId = area.aid";
$db_area = $obj->MySQLSelect($sql);

$mapCenter = '';
$mapZoom = '14';
$iAreaId = '';
if (count($db_area) > 0) {
    $db_area = $db_area[0];
    $iAreaId = $db_area['aid'];
    $mapCenter = $db_area['mapCenter'];
    //$mapZoom = $db_area['mapZoom'];
}

$sql1 = "SELECT * FROM `vehicle_type` WHERE 1";
if ($iAreaId != '')
    $sql1 .= " AND vSavarArea = {$iAreaId}";
$db_carType = $obj->MySQLSelect($sql1);

$cmp_ssql = " AND tbl1.iCompanyId = '" . $iCompanyId . "'";
//$sql2 = "select * FROM register_driver as tbl1 LEFT JOIN driver_vehicle as tbl2 on tbl1.iDriverId=tbl2.iDriverId   WHERE tbl1.eStatus='active' ".$cmp_ssql." ORDER BY tbl1.vName ASC";
$sql2 = "select * FROM register_driver as tbl1 LEFT JOIN driver_vehicle as tbl2 on tbl1.iDriverId=tbl2.iDriverId   WHERE tbl1.eStatus='active' ORDER BY tbl1.vName ASC";
$db_records_online = $obj->MySQLSelect($sql2);

//echo "<pre>";print_r($db_records_online); exit;

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " LEFT JOIN register_user on register_user.iUserId=" . $tbl_name . ".iUserId WHERE " . $tbl_name . ".iCabBookingId = '" . $iCabBookingId . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_R($db_data);echo "</pre>"; die;
    $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iUserId = $value['iUserId'];
            $vDistance = $value['vDistance'];
            $vDuration = $value['vDuration'];
            $dBooking_date = savar_mysql_date_to_shamsi($value['dBooking_date']);

            $split = explode(" ", $dBooking_date, 2);
            $dBooking_date = $split[0];
            $dBooking_time = $split[1];

            $vSourceAddresss = $value['vSourceAddresss'];
            $tDestAddress = $value['tDestAddress'];
            $tTripComment = $value['tTripComment'];
            $iVehicleTypeId = $value['iVehicleTypeId'];
            $vPhone = $value['vPhone'];
            $vName = $value['vName'];
            $vLastName = $value['vLastName'];
            $vEmail = $value['vEmail'];
            $vPhoneCode = $value['vPhoneCode'];
            $vCountry = $value['vCountry'];
            $vAddress = $value['vAddress'];
            $vDescription = $value['vDescription'];
            $from_lat_long = '(' . $value['vSourceLatitude'] . ', ' . $value['vSourceLongitude'] . ')';
            $from_lat = $value['vSourceLatitude'];
            $from_long = $value['vSourceLongitude'];
            $to_lat_long = '(' . $value['vDestLatitude'] . ', ' . $value['vDestLongitude'] . ')';
            $to_lat = $value['vDestLatitude'];
            $to_long = $value['vDestLongitude'];
            #$vCurrencyDriver=$value['vCurrencyDriver'];
        }
    }
} else {
    $dBooking_date = jdate("Y-m-d");
    $dBooking_time = jdate("H:i:s");

    if (isset($_GET['callerid'])) {
        $callerid = substr($_GET['callerid'], -10);
        if (preg_match('/[0-9]{10}/', $callerid)) {
            $callerid = '0' . $callerid;

            $sql = "select * from register_user where vPhone = '" . $callerid . "' LIMIT 1";
            $db_user = $obj->MySQLSelect($sql);

            if (count($db_user) > 0) {
                $userType = 'registered';
                $iUserId = $db_user[0]['iUserId'];
                $vPhone = $db_user[0]['vPhone'];
                $vName = $db_user[0]['vName'];
                $vLastName = $db_user[0]['vLastName'];
                $vEmail = $db_user[0]['vEmail'];
                $vAddress = $db_user[0]['vAddress'];
                $vDescription = $db_user[0]['vDescription'];

                $mustBeLoadTrip = true;
            } else {
                $vPhone = $callerid;
            }
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
    <link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet'/>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!-- <title><?php echo $COMPANY_NAME ?>| Signup</title>-->
    <title><?php echo $langage_lbl['LBL_MANUAL_TAXI_DISPATCH']; ?></title>
    <!-- Default Top Script and css -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css"/>
    <?php include_once("top/top_script.php"); ?>
    <link href="assets/css/checkbox.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/radio.css" rel="stylesheet" type="text/css"/>
    <!--<script src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB ?>"></script>-->
    <?php include_once("top/validation.php"); ?>
    <!-- End: Default Top Script and css-->
    <style>
        #ui-datepicker-div {
            left: 10%;
        }
    </style>
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
            <h2 class="header-page trip-detail"><?php echo $langage_lbl['LBL_MANUAL_TAXI_DISPATCH']; ?>
                <a href="booking.php">
                    <img src="assets/img/arrow-white.png" alt=""><?php echo $langage_lbl['LBL_BACK_LIST_TEXT']; ?>
                </a>
            </h2>
            <!-- trips detail page -->
            <div class="manual-dispatch">
                <a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModal"><i
                            class="fa fa-question-circle"
                            style="font-size: 18px;"></i><?php echo $langage_lbl['LBL_HOW_IT_WORKS']; ?></a>

                <div class="modal fade manual-dispatch-popup" id="myModal" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-large">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-question-circle"
                                                                             style="font-size: 18px;"></i><?php echo $langage_lbl['LBL_HOW_IT_WORKS']; ?>
                                </h4>
                            </div>
                            <div class="modal-body">
                                <p><b>راهنمای استفاده </b> از اعزام تاکسی</p>
                                <p><span><img src="#DISABLE#admin/images/mobile_app_booking.png"></img></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <?php if ($success == "1") { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                            <?php
                            if ($vassign != "1") {
                                ?>
                                Booking Has Been Added Successfully.
                            <?php } else {
                                ?>
                                Driver Has Been Assigned Successfully.
                            <?php } ?>

                        </div><br/>
                    <?php } ?>

                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                            "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will
                            be enabled on the main script we will provide you.
                        </div><br/>
                    <?php } ?>
                    <?php if ($success == 0 && $var_msg != "") { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                            <?php echo $var_msg; ?>
                        </div><br/>
                    <?php } ?>
                    <div class="col-lg-5">
                        <form name="add_booking_form" id="add_booking_form" method="post" action="action_booking.php"
                              enctype="multipart/form-data">
                            <input type="hidden" name="iCompanyId" id="iCompanyId"
                                   value="<?php echo $_SESSION['sess_iUserId']; ?>">
                            <input type="hidden" name="distance" id="distance" value="<?php echo $vDistance; ?>">
                            <input type="hidden" name="duration" id="duration" value="<?php echo $vDuration; ?>">
                            <input type="hidden" name="from_lat_long" id="from_lat_long"
                                   value="<?php echo $from_lat_long; ?>">
                            <input type="hidden" name="from_lat" id="from_lat" value="<?php echo $from_lat; ?>">
                            <input type="hidden" name="from_long" id="from_long" value="<?php echo $from_long; ?>">
                            <input type="hidden" name="to_lat_long" id="to_lat_long"
                                   value="<?php echo $to_lat_long; ?>">
                            <input type="hidden" name="to_lat" id="to_lat" value="<?php echo $to_lat; ?>">
                            <input type="hidden" name="to_long" id="to_long" value="<?php echo $to_long; ?>">
                            <input type="hidden" value="1" id="location_found" name="location_found">
                            <input type="hidden" value="<?php echo $userType ?>" id="user_type" name="user_type">
                            <input type="hidden" value="<?php echo $iUserId; ?>" id="iUserId" name="iUserId">
                            <input type="hidden" value="<?php echo $iCabBookingId; ?>" id="iCabBookingId"
                                   name="iCabBookingId">
                            <input type="hidden" value="<?php echo $iCabBookingId; ?>" id="iCabBookingId"
                                   name="iCabBookingId">
                            <input type="hidden" value="" id="fGDdistance" name="fGDdistance">
                            <input type="hidden" value="" id="fGDtime" name="fGDtime">

                            <div class="add-booking-form">
                  <span>
                    <select name="vCountry" class="form-control form-control-select" onChange="changeCode(this.value); "
                            required>
                      <option value=""><?php echo $langage_lbl['LBL_PLEASE_SELECT_COUNTRY']; ?></option>
                      <?php for ($i = 0; $i < count($db_code); $i++) { ?>
                          <option value="<?php echo $db_code[$i]['vCountryCode'] ?>" <?php
                          if ($db_code[$i]['vCountryCode'] == "IR" && $vCountry == "")
                              echo "selected";
                          if ($db_code[$i]['vCountryCode'] == $vCountry)
                              echo "selected";
                          ?> >
                        <?php echo $db_code[$i]['vCountry'] ?>
                      </option>
                      <?php } ?>
                  </select>
                </span>
                                <span>
                  <input type="hidden" name="vPhoneCode" readonly class="form-control form-control14" placeholder="Code"
                         id="code" value="<?php echo $vPhoneCode; ?>"/>
                  <input type="text" maxlength="12" title="Please enter 10 digit mobile number."
                         class="form-control add-book-input" name="vPhone" id="vPhone" value="<?php echo $vPhone; ?>"
                         onKeyPress="return isNumberKey(event)" placeholder="شماره موبایل" required style="">
                  <a class="btn btn-sm btn-info" id="get_details"><?php echo $langage_lbl['LBL_DETAILS']; ?></a>
                </span>

                                <span> <input type="text" title="Only Alpha character allow"
                                              class="form-control first-name1" name="vName" id="vName"
                                              value="<?php echo $vName; ?>" placeholder="نام">  <input type="text"
                                                                                                       title="Only Alpha character allow"
                                                                                                       class="form-control last-name1"
                                                                                                       name="vLastName"
                                                                                                       id="vLastName"
                                                                                                       value="<?php echo $vLastName; ?>"
                                                                                                       placeholder="نام خانوادگی"
                                                                                                       required></span>
                                <!-- Mehrshad Added -->
                                <span><input type="text" class="form-control" name="vAddress" id="vAddress"
                                             value="<?php echo $vAddress; ?>"
                                             placeholder="آدرس کاربر(جهت استفاده در مبدا قرار دهید)">
                </span>
                                <span><input type="text" class="form-control" name="vDescription" id="vDescription"
                                             value="<?php echo $vDescription; ?>"
                                             placeholder="توضیحات(جهت راهنمایی ادمین)">
                </span>
                                <!-- Mehrshad Added -->
                                <span><input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                                             class="form-control" name="vEmail" onChange="validate_email(this.value)"
                                             id="vEmail" value="<?php echo $vEmail; ?>" placeholder="Email">
                  <div id="emailCheck"></div></span>
                                <span>
                    <input type="text" class="ride-location1 highalert txt_active form-control" name="vSourceAddresss"
                           id="from" value="<?php echo $vSourceAddresss; ?>" placeholder="آدرس مبدا" required>
                  </span>
                                <span>
                    <input type="text" class="ride-location1 highalert txt_active form-control" name="tDestAddress"
                           id="to" value="<?php echo $tDestAddress; ?>" placeholder="آدرس مقصد" required>
                  </span>
                                <span>
                    <a class="btn btn-sm btn-info" id="select_manual">انتخاب دستی مبدا و مقصد</a>
                  </span>

                                <span>
                    <textarea type="text" class="ride-location1 highalert txt_active form-control" name="tTripComment"
                              id="to" value="<?php echo $tTripComment; ?>" placeholder="توضیحات"></textarea>
                  </span>

                                <span>
                    <!--                                            <input type="text" class=" form-control" name="dBooking_date"  id="datetimepicker4" value="<?php echo $dBooking_date; ?>" placeholder="Select Date / Time" required readonly>-->
                    <input type="text" class="form-control" name="dBooking_date" id="datepicker4"
                           value="<?php echo $dBooking_date; ?>" placeholder="انتخاب تاریخ" required>
                  </span>
                                <span style="position:relative">
                    <input type="text" class="form-control" name="dBooking_time" id="timepicker5"
                           value="<?php echo $dBooking_time; ?>" placeholder="انتخاب ساعت" required>
                  </span>
                                <span style="position:relative">
                    <select class="selectpicker_disable form-control form-control-select" name='numberOfCar'
                            id="numberOfCar" required onChange="">
                      <option value="1">(1)تعداد ماشین</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      <option value="5">5</option>

                    </select>
                  </span>

                                <?php if (!empty($db_records_online)) { ?>
                                    <span class="col-lg-6 vehicle-type2">
                      <style>
                      .vehicle-type2 .caret {
                          display: none
                      }
                      </style>
                      <select class="selectpicker_disable form-control form-control-select" name='iDriverId'
                              id="iDriverId" onChange="shoeDriverDetail002(this.value);">
                        <option value="">انتخاب <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>

                      </select>
                    </span>
                                <?php } else { ?>
                                    <div class="row show_drivers_lists">
                                        <div class="col-lg-6">
                                            <h5>راننده ای پیدا نشد.</h5>
                                        </div>
                                    </div>
                                <?php } ?>
                                <span class="vehicle-type1">
                    <select class="form-control form-control-select" name='iVehicleTypeId' id="iVehicleTypeId"
                            onChange="getFarevalues(this.value);" required>
                      <option value="">انتخاب <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></option>
                      <?php foreach ($db_carType as $db_car) { ?>
                          <option value="<?php echo $db_car['iVehicleTypeId']; ?>" <?php if ($iVehicleTypeId == $db_car['iVehicleTypeId']) {
                              echo "selected";
                          } ?> ><?php echo $db_car['vVehicleType']; ?></option>
                      <?php } ?>
                    </select>
                  </span>
                                <span class="col-lg-6" id="showDriver003"></span>
                                <span>
                    <input type="submit" class="save btn-info button-submit" name="submit" id="submit" value="رزرو">
                    <input type="button" class="save btn-info button-submit" name="estimatefare" id="estimatefare"
                           value="محاسبه کرایه" onclick="estimateFare()">
                    <input type="reset" class="btn btn-danger" name="reset" id="reset12" value="جدید">
                  </span>
                            </div>
                        </form>
                        <div class="total-price">
                            <ul>
                                <li><b>حداقل کرایه</b> : <em id="minimum_fare_price">0</em> تومان</li>
                                <li><b>کرایه پایه</b> : <em id="base_fare_price">0</em> تومان</li>
                                <li><b>فاصله (<em id="dist_fare">0</em> KMs)</b> : <em id="dist_fare_price">0</em> تومان
                                </li>
                                <li><b>زمان (<em id="time_fare">0</em> Minutes)</b> : <em id="time_fare_price">0</em>
                                    تومان
                                </li>
                            </ul>
                            <span>جمع کرایه<b> <em id="total_fare_price">0</em></b> تومان</span>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="gmap-div gmap-div1" style="height:665px;">
                            <div id="map" class="gmap3" style="height:665px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- -->
            <div style="clear:both;"></div>

            <?php
            if ($userType == 'registered') {
                $sql = "SELECT cb.*,CONCAT(ru.vName,' ',ru.vLastName) as rider,CONCAT(rd.vName,' ',rd.vLastName) as driver,vt.vVehicleType FROM cab_booking as cb
          LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId
          LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId
          LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE cb.iUserId = '{$iUserId}' ORDER BY `cb`.`iCabBookingId` DESC LIMIT 5";
                $data_drv = $obj->MySQLSelect($sql);
                ?>


                <div class="trips-table trips-table-driver trips-table-driver-res">
                    <div class="trips-table-inner">
                        <div class="driver-trip-table">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" id="dataTables-example">
                                <thead>
                                <tr>
                                    <th width="20%">#</th>
                                    <th width="20%"><?php echo $langage_lbl['LBL_DATE_TXT']; ?></th>
                                    <th width="25%"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></th>
                                    <th><?php echo $langage_lbl['LBL_PICKUP_TXT']; ?></th>
                                    <th width="10%"><?php echo $langage_lbl['LBL_DESTINATION_TEXT']; ?></th>
                                    <th width="15%"
                                        style="width: 67px;"><?php echo $langage_lbl['LBL_TRIP_COMMENT']; ?></th>
                                    <th width="15%"
                                        style="width: 67px;"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></th>
                                    <th width="14%"><?php echo $langage_lbl['LBL_TRIP_DETAILS_TXT']; ?></th>
                                    <th width="8%"><?php echo $langage_lbl['LBL_STATUS_TXT']; ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php for ($i = 0; $i < count($data_drv); $i++) { ?>
                                    <tr class="gradeA">
                                        <td data-order='[[ 1, "asc" ]]'><?php echo $data_drv[$i]['iCabBookingId']; ?></td>
                                        <td <?php if (time() - strtotime($data_drv[$i]['dBooking_date']) < 600)
                                            echo "style=\"background-color:#fee\"" ?> ><?php echo jdate('dS M Y,', strtotime($data_drv[$i]['dBooking_date'])); ?><?php echo jdate('H:i', strtotime($data_drv[$i]['dBooking_date'])); ?></td>
                                        <td><?php echo $data_drv[$i]['rider']; ?></td>
                                        <td><?php echo $data_drv[$i]['vSourceAddresss']; ?></td>
                                        <td><?php echo $data_drv[$i]['tDestAddress']; ?></td>
                                        <td><?php echo $data_drv[$i]['tTripComment']; ?></td>
                                        <?php if ($data_drv[$i]['eStatus'] == "Pending") { ?>
                                            <td><a class="btn btn-info"
                                                   href="manual_dispatch.php?booking_id=<?php echo $data_drv[$i]['iCabBookingId']; ?>"><i
                                                            class="icon-shield icon-flip-horizontal icon-white"></i>
                                                    اختصاص راننده</a><br>( نوع ماشین
                                                : <?php echo $data_drv[$i]['vVehicleType']; ?>)
                                            </td>
                                        <?php } else if ($data_drv[$i]['eCancelBy'] == "Driver" && $data_drv[$i]['eStatus'] == "Cancel") { ?>
                                            <td><a class="btn btn-info"
                                                   href="manual_dispatch.php?booking_id=<?php echo $data_drv[$i]['iCabBookingId']; ?>"><i
                                                            class="icon-shield icon-flip-horizontal icon-white"></i>
                                                    اختصاص راننده</a><br>( نوع ماشین
                                                : <?php echo $data_drv[$i]['vVehicleType']; ?>)
                                            </td>
                                        <?php } else if ($data_drv[$i]['driver'] != "" && $data_drv[$i]['driver'] != "0") { ?>
                                            <td><b><?php echo $data_drv[$i]['driver']; ?></b><br>( نوع ماشین
                                                : <?php echo $data_drv[$i]['vVehicleType']; ?>)
                                            </td>
                                        <?php } else { ?>
                                            <td>---<br>( نوع ماشین : <?php echo $data_drv[$i]['vVehicleType']; ?>)</td>
                                        <?php } ?>
                                        <td><?php if ($data_drv[$i]['iTripId'] != "" && $data_drv[$i]['eStatus'] == "Completed") { ?>
                                            <a class="btn btn-primary"
                                               href="invoice.php?iTripId=<?php echo base64_encode(base64_encode($data_drv[$i]['iTripId'])); ?>">
                                                    View</a><?php } else {
                                                echo "---";
                                            } ?></td>
                                        <td><?php if ($data_drv[$i]['eStatus'] == "Assign") {
                                                echo "---";
                                            } else {
                                                $sql = "select iActive from trips where iTripId=" . $data_drv[$i]['iTripId'];
                                                $data_stat = $obj->MySQLSelect($sql);
                                                //echo "<pre>";print_r($data_stat); die;
                                                if ($data_stat) {
                                                    for ($d = 0; $d < count($data_stat); $d++) {
                                                        echo $data_stat[$d]['iActive'];
                                                    }
                                                } else {
                                                    echo $data_drv[$i]['eStatus'];
                                                }
                                            } ?>
                                            <?php if ($data_drv[$i]['eStatus'] == "Cancel") { ?>
                                                <br/><a href="javascript:void(0);" class="btn btn-info"
                                                        data-toggle="modal"
                                                        data-target="#uiModal_<?php echo $data_drv[$i]['iCabBookingId']; ?>">دلیل
                                                    کنسلی</a>
                                            <?php } ?>

                                            <br/>
                                            <a class="btn btn-info"
                                               href="booking_details.php?booking_id=<?php echo $data_drv[$i]['iCabBookingId']; ?>"
                                               target="_blank"><i
                                                        class="icon-shield icon-flip-horizontal icon-white"></i>جزئیات
                                                بیشتر</a>
                                        </td>
                                    </tr>
                                    <div class="col-lg-12">
                                        <div class="modal fade"
                                             id="uiModal_<?php echo $data_drv[$i]['iCabBookingId']; ?>" tabindex="-1"
                                             role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-content image-upload-1" style="width:400px;">
                                                <div class="upload-content" style="width:350px;">
                                                    <h3>دلیل کنسل کردن رزرو</h3>
                                                    <h4>کنسل شده توسط: <?php echo $data_drv[$i]['eCancelBy']; ?></h4>
                                                    <h4>دلیل: <?php echo $data_drv[$i]['vCancelReason']; ?></h4>
                                                    <form class="form-horizontal" id="frm6" method="post"
                                                          enctype="multipart/form-data" action="" name="frm6">
                                                        <div class="form-group">
                                                            <div class="col-lg-12">

                                                            </div>
                                                        </div>
                                                        <div class="col-lg-13">

                                                        </div>


                                                        <input type="button" class="save" data-dismiss="modal"
                                                               name="cancel" value="Close">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
            }
            ?>
        </div>
    </div>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- -->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<link rel="stylesheet" type="text/css" media="screen"
      href="admin/css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="admin/js/moment.min.js"></script>
<script type="text/javascript" src="admin/js/bootstrap-datetimepicker.min.js"></script>
<script src="assets/js/jquery.ui.datepicker-cc-fa.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>


<script type="text/javascript">
    L.cedarmaps.accessToken = '4a0a95307ce57f099d59085bf0b36c46668124b2'; // See the note below on how to get an access token

    // Getting maps info from a tileJSON source
    var tileJSONUrl = 'https://api.cedarmaps.com/v1/tiles/cedarmaps.streets.json?access_token=' + L.cedarmaps.accessToken;
    var map = L.cedarmaps.map('map', tileJSONUrl, {
        scrollWheelZoom: true,
        <?php if ($mapCenter != '') {
            echo 'center: ' . $mapCenter . ',';
        } else {
            echo 'center: {lat: 20.1849963, lng: 64.4125062},';
            //echo 'center: {lat: 51.505, lng: -0.09},';
        }
        if ($mapZoom != '') {
            echo 'zoom:' . $mapZoom . ',';

        } else {
            echo 'zoom: 4,';

        } ?>
        fullscreenControl: true
    });
    var LeafIcon = L.Icon.extend({
        options: {}
    });


    var geocoder = L.cedarmaps.geocoder('cedarmaps.streets');


</script>

<script>

    function getFarevalues(vehicleId) {

        var select = document.getElementById("iDriverId");
        var length = select.options.length;
        for (i = 1; i < length; i++) {
            select.options[i] = null;
        }
//window.alert("sometext");
        var jArray = <?php echo json_encode($db_records_online); ?>;
        for (var i = 0; i < jArray.length; i++) {

            if (jArray[i]['vCarType'].includes(vehicleId)) {
                var opt = document.createElement('option');
                opt.value = jArray[i]['iDriverId'];
                opt.innerHTML = jArray[i]['vName'] + ' ' + jArray[i]['vLastName'];
                select.appendChild(opt);
            }
        }

        //return;
        $.ajax({
            type: "POST",
            url: 'admin/ajax_find_rider_by_number.php',
            data: 'vehicleId=' + vehicleId,
            success: function (dataHtml) {
                console.log(dataHtml);
                if (dataHtml != "") {
                    var result = dataHtml.split(':');
                    $('#minimum_fare_price').text(parseFloat(result[3]).toFixed(2));
                    $('#base_fare_price').text(parseFloat(result[0]).toFixed(2));
                    $('#dist_fare_price').text(parseFloat(result[1] * $('#dist_fare').text()).toFixed(2));
                    $('#time_fare_price').text(parseFloat(result[2] * $('#time_fare').text()).toFixed(2));
                    var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                    if (parseInt(totalPrice) >= parseInt($('#minimum_fare_price').text())) {
                        $('#total_fare_price').text(totalPrice);
                    } else {
                        $('#total_fare_price').text($('#minimum_fare_price').text());
                    }
                } else {
                    $('#minimum_fare_price').text('0');
                    $('#base_fare_price').text('0');
                    $('#dist_fare_price').text('0');
                    $('#time_fare_price').text('0');
                    $('#total_fare_price').text('0');
                }
            }
        });
    }

    function estimateFare() {
        alert("sorry my fault, didn't mean to but now I am in byte nirvana");

        if ($("#from_lat").val() == '' ||
            $("#from_long").val() == '' ||
            $("#to_lat").val() == '' ||
            $("#to_long").val() == '') {
            alert("مبدا و مقصد انتخاب نشده است");
        } else if ($("#iVehicleTypeId").val() == '') {
            alert("نوع وسیله نقلیه انتخاب نشده است");
        } else {

            $('#total_fare_price').text('0');
            $('#minimum_fare_price').text('0');
            $('#base_fare_price').text('0');
            $('#dist_fare_price').text('0');
            $('#time_fare_price').text('0');
            $('#dist_fare').text('0');
            $('#time_fare').text('0');
            $('#fGDdistance').val('');
            $('#fGDtime').val('');

            $.ajax({
                type: "POST",
                url: 'ajax_calc_estimatefare.php',
                data:
                    'from_lat=' + $("#from_lat").val() + "&" +
                    'from_long=' + $("#from_long").val() + "&" +
                    'to_lat=' + $("#to_lat").val() + "&" +
                    'to_long=' + $("#to_long").val() + "&" +
                    'vehicle_type_id=' + $("#iVehicleTypeId").val() + "&"
                ,
                success: function (datajson) {
                    console.log(data);
                    if (datajson != '') {
                        var data = jQuery.parseJSON(datajson);

                        $('#total_fare_price').text(data.total_fare);
                        $('#minimum_fare_price').text(data.iMinFare);
                        $('#base_fare_price').text(data.iBaseFare);
                        $('#dist_fare_price').text(data.fPricePerKM);
                        $('#time_fare_price').text(data.fPricePerMin);
                        $('#dist_fare').text(data.tripDistance);
                        $('#time_fare').text(data.tripDuration);
                        $('#fGDdistance').val(data.tripDistance);
                        $('#fGDtime').val(data.tripDuration);

                    }

                }
            });
        }
    }


    $('.gallery').each(function () { // the containers for all your galleries
        $(this).magnificPopup({
            delegate: 'a', // the selector for gallery item
            type: 'image',
            gallery: {
                enabled: true
            }
        });
    });

    $(function () {

        $("#add_booking_form").submit(function () {
            if ($("#from_lat_long").val() == '') {
                $("#from").css('border-color', 'red');
                return false;
            }
            return true;
        });

        newDate = new Date('Y-M-D');
        //                $('#datetimepicker4').datetimepicker({
        //					format: 'YYYY-MM-DD HH:mm:ss',
        //					minDate: moment().format('l'),
        //					ignoreReadonly: true,
        //					sideBySide: true,
        //				});
        $('#datepicker4').datepicker({
            dateFormat: "yy-mm-dd",
            changeYear: true,
            changeMonth: true,
            yearRange: "0:+10"
        });
        $('#timepicker5').datetimepicker({
            format: "HH:mm:ss"
        });
    });


    $('#select_manual').on('click', function () {
        var allcentermap = map.getCenter().toString();
        var allcentermap2 = allcentermap.replace('LatLng(', '');
        allcentermap2 = allcentermap2.replace(')', '');
        var allcentermap3 = allcentermap2.split(',');
        //alert(allcentermap3[0]);
        var centerLat = allcentermap3[0];
        var centerLon = allcentermap3[1];
//window.alert("sometext");
        if ($("#from_lat_long").val() == '') {
            $("#from_lat_long").val('(' + centerLat + ',' + centerLon + ')');
            $("#from_lat").val(centerLat);
            $("#from_long").val(centerLon);
            go_for_action('from');
        }

        if ($("#to_lat_long").val() == '') {
            //centerLat += 0.005;

            $("#to_lat_long").val('(' + centerLat + ',' + centerLon + ')');
            $("#to_lat").val(centerLat);
            $("#to_long").val(centerLon);
            go_for_action('to');
        }
    });

    $('#get_details').on('click', function () {
        var phone = $('#vPhone').val();
        console.log(phone);
        $.ajax({
            type: "POST",
            url: 'ajax_find_rider.php',
            data: 'phone=' + phone,
            success: function (dataStr) {
                if (dataStr != "") {

                    data = JSON.parse(dataStr);
                    if (data['status'] == 'ok') {
                        $("#user_type").val('registered');
                        //var result = dataHtml.split(':');
                        $('#vName').val(data['message']['vName']);
                        $('#vLastName').val(data['message']['vLastName']);
                        $('#vEmail').val(data['message']['vEmail']);
                        $('#iUserId').val(data['message']['iUserId']);
                        $('#vAddress').val(data['message']['vAddress']);
                        $('#vDescription').val(data['message']['vDescription']);
                        InitTripsPoint(data['message']['trips']);
                    } else {
                        alert(data['message']);
                    }
                } else {
                    $("#user_type").val('');
                    $('#vName').val('');
                    $('#vLastName').val('');
                    $('#vEmail').val('');
                    $('#vAddress').val('');
                    $('#vDescription').val('');
                    $('#iUserId').val('');
                }
            }
        });
    });


    var map;
    var geocoder;
    var markers = [];
    var tripMarkers = [];
    var autocomplete_from;
    var autocomplete_to;

    function initialize() {
        geocoder = new google.maps.Geocoder();

        <?php if($mapCenter != '') : ?>
        var mapOptions = {
            zoom: <?php echo $mapZoom ?>,
            center: <?php echo $mapCenter ?>
        };
        <?php else : ?>
        var mapOptions = {
            zoom: 4,
            center: new google.maps.LatLng('20.1849963', '64.4125062')
        };
        <?php endif; ?>

        map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);
        <?php if($action == "Edit") { ?>
        callEditFundtion();
        <?php } ?>

        <?php
        if(isset($mustBeLoadTrip) && $mustBeLoadTrip == true) :
        ?>
        //$(document).ready(function () {
        $('#get_details').click();
        //});
        <?php
        endif;
        ?>
    }


    function InitTripsPoint(trips) {
        if (typeof trips == "object" && trips.length > 0) {
            for (x = 0; x < tripMarkers.length; x++) {
                tripMarkers[x].setMap(null);
            }

            tripMarkers = [];


            for (i = 0; i < trips.length; i++) {
                var points = [];

                var image = {
                    url: 'assets/img/savar/StartPoint.png',
                    //anchor: new google.maps.Point(8,8)
                };

                points['startPoint'] = trips[i]['tStartLat'], trips[i]['tStartLong'];
//window.alert(trips[i]['tStartLong']);


                var redIcon = new LeafIcon({iconUrl: 'assets/img/savar/EndPoint.png'}),
                    red2Icon = new LeafIcon({iconUrl: 'assets/img/savar/DriverPoint.png'}),
                    greenIcon = new LeafIcon({iconUrl: 'assets/img/savar/StartPoint.png'});
                L.marker([trips[i]['tStartLat'], trips[i]['tStartLong']], {icon: greenIcon}).bindPopup("Start").addTo(map);

//window.alert([trips[i]['tStartLat'],trips[i]['tStartLong']] + " start");


                /*          var marker = new google.maps.Marker({
                            position: points['startPoint'],
                            map: map,
                            animation: google.maps.Animation.DROP,
                            icon: image,
                          });

                          tripMarkers.push(marker);*/

                //points['endPoint']  = new google.maps.LatLng( trips[i]['tEndLat'],trips[i]['tEndLong']);
                L.marker([trips[i]['tEndLat'], trips[i]['tEndLong']], {icon: redIcon}).bindPopup("End").addTo(map);
//window.alert(trips[i]['tEndLat'],trips[i]['tEndLong']);
                /*image.url = 'assets/img/savar/EndPoint.png'
                var marker = new google.maps.Marker({
                  position: points['endPoint'],
                  map: map,
                  animation: google.maps.Animation.DROP,
                  icon: image,
                });
                tripMarkers.push(marker);*/

                var driverLoc = trips[i]['vDriverStartLocation'];
                //window.alert(trips[i]['vDriverStartLocation'] + " driver");
                if (driverLoc != '') {
                    var dls = driverLoc.split(',');
                    //points['driverStartPoint'] = new google.maps.LatLng( dls[0],dls[1]);
                    L.marker([dls[0], dls[1]], {icon: red2Icon}).bindPopup("DriverStartLocation").addTo(map);
//map.remove();
                    /*




                    map = L.cedarmaps.map('map', tileJSONUrl, {
                               scrollWheelZoom: true,
<?php if ($mapCenter != '') {
                    echo 'center: ' . $mapCenter . ',';
                } else {
                    echo 'center: {lat: 35.6899828, lng: 51.389644},';
                    //echo 'center: {lat: 51.505, lng: -0.09},';
                }
                    if ($mapZoom != '') {
                        echo 'zoom:' . $mapZoom . ',';

                    } else {
                        echo 'zoom: 15,';

                    } ?>
                       fullscreenControl: true
                   });














            image.url = 'assets/img/savar/DriverPoint.png'
            var marker = new google.maps.Marker({
              position: points['driverStartPoint'],
              map: map,
              animation: google.maps.Animation.DROP,
              icon: image,
            });
            tripMarkers.push(marker);*/
                }
            }
        }
    }

    $(document).ready(function () {
        //google.maps.event.addDomListener(window, 'load', initialize);
    });


    $(function () {

        var from = document.getElementById('from');
        autocomplete_from = new google.maps.places.Autocomplete(from);
        google.maps.event.addListener(autocomplete_from, 'place_changed', function () {
            var place = autocomplete_from.getPlace();
            //console.log(autocomplete_from.);
            $("#from_lat_long").val(place.geometry.location);
            $("#from_lat").val(place.geometry.location.lat());
            $("#from_long").val(place.geometry.location.lng());
            go_for_action('from');
        });

        var to = document.getElementById('to');
        autocomplete_to = new google.maps.places.Autocomplete(to);
        google.maps.event.addListener(autocomplete_to, 'place_changed', function () {
            var place = autocomplete_to.getPlace();
            $("#to_lat_long").val(place.geometry.location);
            $("#to_lat").val(place.geometry.location.lat());
            $("#to_long").val(place.geometry.location.lng());
            go_for_action('to');
        });
    });

    function go_for_action(where) {
        if (where == 'from') {
            show_locationNew('from');
        } else if (where == 'to') {
            show_locationNew('to');
        }
        //				else if ($("#from").val() != '' && $("#to").val() != '') {
        //                  from_to($("#from").val(), $("#to").val());
        //				}
    }


    //            var image = {
    //            url: 'assets/img/savar/StartLoc.png',
    //            anchor: new google.maps.Point(25,25)
    //
    //          };
    //
    //
    //        var markerSource = new google.maps.Marker({
    //          position: new google.maps.LatLng( 30.6755734,51.5921389),
    //          map: map,
    //          draggable:true,
    //          animation: google.maps.Animation.DROP,
    //          icon: image,
    //          opacity: 0.7
    //        });
</script>
<script type="text/javascript" src="admin/js/gmap3.js"></script>
<script type="text/javascript">
    var chk_route;

    function show_location(address) {
        alert("show_location");
        clearThat();
        $('#map-canvas').gmap3({
            map: {
                options: {
                    zoom: <?php echo $mapZoom ?>
                }
            }
        }).marker({position: [48.8620722, 2.352047]});
    }

    function show_locationNew(where) {
        var startIcon = new LeafIcon({iconUrl: 'assets/img/savar/StartLoc.png'});
        var endIcon = new LeafIcon({iconUrl: 'assets/img/savar/EndLoc.png'});
        if (where == 'to') {
            address = $("#to").val();
            lat = $("#to_lat").val();
            long = $("#to_long").val();
            mLabel = where;
            image = {
                url: 'assets/img/savar/EndLoc.png',
                //anchor: new google.maps.Point(25,25)
            };
//var kolan = [lat,long];

            marker1 = L.marker([lat, long], {icon: endIcon, draggable: true}).bindPopup(mLabel).addTo(map);
            $('#to').removeAttr('required');
        } else if (where == 'from') {
            address = $("#from").val();
            lat = $("#from_lat").val();
            long = $("#from_long").val();
            mLabel = where;
            image = {
                url: 'assets/img/savar/StartLoc.png',
                //anchor: new google.maps.Point(25,25)
            };
            marker = L.marker([lat, long], {icon: startIcon, draggable: true}).bindPopup(mLabel).addTo(map);
            $('#from').removeAttr('required');
        } else
            return;
        //var point = new google.maps.LatLng( lat,long);

        map.panTo(new L.LatLng(lat, long));
        //map.setCenter(point);
        //map.setZoom(14);

//map.addLayer(marker);
//var marker2 = L.marker([51.441767, 5.470247],{draggable: true,icon: startIcon}).addTo(map);


        //var endIcon = new LeafIcon({iconUrl: 'assets/img/savar/EndLoc.png'});


        /*

        <form name="add_booking_form" id="add_booking_form" method="post" action="action_booking.php" enctype="multipart/form-data">
                        <input type="text" name="iCompanyId" id="iCompanyId" value="2">
                        <input type="text" name="distance" id="distance" value="">
                        <input type="text" name="duration" id="duration" value="">
                        <input type="text" name="from_lat_long" id="from_lat_long" value="">
                        <input type="text" name="from_lat" id="from_lat" value="">
                        <input type="text" name="from_long" id="from_long" value="">
                        <input type="text" name="to_lat_long" id="to_lat_long" value="">
                        <input type="text" name="to_lat" id="to_lat" value="">
                        <input type="text" name="to_long" id="to_long" value="">
                        <input type="text" value="1" id="location_found" name="location_found">
                        <input type="text" value="" id="user_type" name="user_type">
                        <input type="text" value="" id="iUserId" name="iUserId">
                        <input type="text" value="" id="iCabBookingId" name="iCabBookingId">
                        <input type="text" value="" id="iCabBookingId" name="iCabBookingId">
                        <input type="text" value="" id="fGDdistance" name="fGDdistance">
                        <input type="text" value="" id="fGDtime" name="fGDtime">

                        <div class="add-booking-form">
                          <span>
                            <select name="vCountry" class="form-control form-control-select" onchange="changeCode(this.value); " required="">
                              <option value="">لطفا کشور را انتخاب کنید</option>
                                                      <option value="IR" selected="">
                                Iran (Islamic Republic of)                      </option>
                                              </select>
                        </span>
                        <span>
                          <input type="hidden" name="vPhoneCode" readonly="" class="form-control form-control14" placeholder="Code" id="code" value="">
                          <input type="text" maxlength="12" title="Please enter 10 digit mobile number." class="form-control add-book-input" name="vPhone" id="vPhone" value="" onkeypress="return isNumberKey(event)" placeholder="شماره موبایل" required="" style="">
                          <a class="btn btn-sm btn-info" id="get_details">جزئیات</a>
                        </span>

                        <span> <input type="text" title="Only Alpha character allow" class="form-control first-name1" name="vName" id="vName" value="" placeholder="نام">  <input type="text" title="Only Alpha character allow" class="form-control last-name1" name="vLastName" id="vLastName" value="" placeholder="نام خانوادگی" required=""></span>
                        <!-- Mehrshad Added -->
                        <span><input type="text" class="form-control" name="vAddress" id="vAddress" value="" placeholder="آدرس کاربر(جهت استفاده در مبدا قرار دهید)">
                        </span>
                        <span><input type="text" class="form-control" name="vDescription" id="vDescription" value="" placeholder="توضیحات(جهت راهنمایی ادمین)">
                        </span>
                        <!-- Mehrshad Added -->
                        <span><input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" class="form-control" name="vEmail" onchange="validate_email(this.value)" id="vEmail" value="" placeholder="Email">
                          <div id="emailCheck"></div></span>
                          <span>
                            <input type="text" class="ride-location1 highalert txt_active form-control" name="vSourceAddresss" id="from" value="" placeholder="آدرس مبدا">
                          </span>
                          <span>
                            <input type="text" class="ride-location1 highalert txt_active form-control" name="tDestAddress" id="to" value="" placeholder="آدرس مقصد">
                          </span>
                          <span>
                            <a class="btn btn-sm btn-info" id="select_manual">انتخاب دستی مبدا و مقصد</a>
                          </span>

                          <span>
                            <textarea type="text" class="ride-location1 highalert txt_active form-control" name="tTripComment" id="to" value="" placeholder="توضیحات"></textarea>
                          </span>

                          <span>
                            <!--                                            <input type="text" class=" form-control" name="dBooking_date"  id="datetimepicker4" value="1397-06-08" placeholder="Select Date / Time" required readonly>-->
                            <input type="text" class="form-control hasDatepicker" name="dBooking_date" id="datepicker4" value="1397-06-08" placeholder="انتخاب تاریخ" required="">
                          </span>
                          <span style="position:relative">
                            <input type="text" class="form-control" name="dBooking_time" id="timepicker5" value="16:36:56" placeholder="انتخاب ساعت" required="">
                          </span>
                          <span style="position:relative">
                            <select class="selectpicker_disable form-control form-control-select" name="numberOfCar" id="numberOfCar" required="" onchange="">
                              <option value="1">(1)تعداد ماشین</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value="4">4</option>
                              <option value="5">5</option>

                            </select>
                          </span>

                                              <span class="col-lg-6 vehicle-type2">
                              <style>
                              .vehicle-type2 .caret{display:none}
                              </style>
                              <select class="selectpicker_disable form-control form-control-select" name="iDriverId" id="iDriverId" onchange="shoeDriverDetail002(this.value);">
                                <option value="">انتخاب راننده / حامل</option>

                              </select>
                            </span>
                                            <span class="vehicle-type1">
                            <select class="form-control form-control-select" name="iVehicleTypeId" id="iVehicleTypeId" onchange="getFarevalues(this.value);" required="">
                              <option value="">انتخاب Vehicle Type</option>
                                                      <option value="12">basic-esf</option>
                                                      <option value="13">motor-esf</option>
                                                  </select>
                          </span>
                          <span class="col-lg-6" id="showDriver003"></span>
                          <span>
                            <input type="submit" class="save btn-info button-submit" name="submit" id="submit" value="رزرو">
                            <input type="button" class="save btn-info button-submit" name="estimatefare" id="estimatefare" value="محاسبه کرایه" onclick="estimateFare()">
                            <input type="reset" class="btn btn-danger" name="reset" id="reset12" value="جدید">
                          </span>
                        </div>
                      </form>















        var marker = new google.maps.Marker({
          position: point,
          map: map,
          draggable:true,
          animation: google.maps.Animation.DROP,
          icon: image,
          title: mLabel,
          opacity: 1
        });*/
        var marker1;
        if (typeof markers[mLabel] == "object")
            markers[mLabel].setMap(null);

        markers[mLabel] = marker;
        marker.on('drag', function (e) {
            console.log('marker drag event');
        });
        marker1.on('drag', function (e) {
            console.log('marker drag event');
        });

        var ssssss;
        marker.on('dragend', function (e) {

            //  alert(allcentermarker3[0]);

            lat = marker.getLatLng().lat;
            lon = marker.getLatLng().lng;
            ssssss = marker.getPopup();
            if (ssssss.getContent() == 'from') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#from").val(res.result.province + " , " + res.result.city + " , " + res.result.locality);
                });
                $("#from_lat").val(lat);
                $("#from_long").val(lon);
                $("#from_lat_long").val('(' + lat + ',' + lon + ')');
            } else if (ssssss.getContent() == 'to') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#to").val(res.result.province + " , " + res.result.city + " , " + res.result.locality);
                });
                $("#to_lat").val(lat);
                $("#to_long").val(lon);
                $("#to_lat_long").val('(' + lat + ',' + lon + ')');
            }
        });
        marker1.on('dragend', function (e) {

            //  alert(allcentermarker3[0]);

            lat = marker1.getLatLng().lat;
            lon = marker1.getLatLng().lng;
            ssssss = marker1.getPopup();
            if (ssssss.getContent() == 'from') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#from").val(res.result.province + " , " + res.result.city + " , " + res.result.locality);
                });
                $("#from_lat").val(lat);
                $("#from_long").val(lon);
                $("#from_lat_long").val('(' + lat + ',' + lon + ')');
            } else if (ssssss.getContent() == 'to') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#to").val(res.result.province + " , " + res.result.city + " , " + res.result.locality);
                });
                $("#to_lat").val(lat);
                $("#to_long").val(lon);
                $("#to_lat_long").val('(' + lat + ',' + lon + ')');
            }
        });


    }

    //Mamad
    function geocodePosition(marker) {
        //console.log(marker);
        var allcentermarker = marker.getCenter().toString();
        var allcentermarker2 = allcentermarker.replace('LatLng(', '');
        allcentermarker2 = allcentermarker2.replace(')', '');
        var allcentermarker3 = allcentermarker2.split(',');
        //  alert(allcentermarker3[0]);

        lat = allcentermarker3[0];
        lon = allcentermarker3[1];

        if (marker.getTitle() == 'from') {
            $("#from_lat").val(lat);
            $("#from_long").val(lon);
            $("#from_lat_long").val('(' + lat + ',' + lon + ')');
        } else if (marker.getTitle() == 'to') {
            $("#to_lat").val(lat);
            $("#to_long").val(lon);
            $("#to_lat_long").val('(' + lat + ',' + lon + ')');
        }
    }

    //Mamad
    function clearThat() {
        var opts = {};
        opts.name = ["marker", "directionsrenderer"];
        opts.first = true;
        $('#map-canvas').gmap3({clear: opts});
    }

    function from_to(from, to) {

        clearThat();
        if (from == '')
            from = $('#from').val();

        if (to == '')
            to = $('#to').val();
        //alert("from_to" + from +"   to "+to);
        $("#from_lat_long").val('');
        $("#to_lat_long").val('');

        var chks = document.getElementsByName('loc');
        if (from != '') {
            geocoder.geocode({'address': from}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                        $("#from_lat_long").val((results[0].geometry.location));
                    } else {
                        alert("No results found");
                    }
                } else {
                    var place19 = autocomplete_from.getPlace();
                    $("#from_lat_long").val(place19.geometry.location);
                }
            });
        }
        if (to != '') {
            geocoder.geocode({'address': to}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                        $("#to_lat_long").val((results[0].geometry.location));
                    } else {
                        alert("No results found");
                    }
                } else {
                    var place20 = autocomplete_to.getPlace();
                    $("#to_lat_long").val(place20.geometry.location);
                }
            });
        }

        var fromLatlongs = $("#from_lat").val() + ", " + $("#from_long").val();
        var toLatlongs = $("#to_lat").val() + ", " + $("#to_long").val();
//Mamad
        $("#map-canvas").gmap3({
            getroute: {
                options: {
                    origin: fromLatlongs,
                    destination: toLatlongs,
                    travelMode: google.maps.DirectionsTravelMode.DRIVING
                },
                callback: function (results, status) {
                    chk_route = status;
                    if (!results)
                        return;
                    $(this).gmap3({
                        map: {
                            options: {
                                zoom: 8,
                                //center: [51.511214, -0.119824]
                                center: [58.0000, 20.0000]
                            }
                        },
                        directionsrenderer: {
                            options: {
                                directions: results
                            }
                        }
                    });
                }
            }
        });
//Mamad
        $("#map-canvas").gmap3({
            getdistance: {
                options: {
                    origins: fromLatlongs,
                    destinations: toLatlongs,
                    travelMode: google.maps.TravelMode.DRIVING
                },
                callback: function (results, status) {
                    var html = "";
                    if (results) {
                        for (var i = 0; i < results.rows.length; i++) {
                            var elements = results.rows[i].elements;
                            for (var j = 0; j < elements.length; j++) {
                                switch (elements[j].status) {
                                    case "OK":
                                        html += elements[j].distance.text + " (" + elements[j].duration.text + ")<br />";
                                        document.getElementById("distance").value = elements[j].distance.value;
                                        document.getElementById("duration").value = elements[j].duration.value;
                                        var dist_fare = parseInt(elements[j].distance.value, 10) / parseInt(1000, 10);
                                        $('#dist_fare').text(Math.round(dist_fare));
                                        var time_fare = parseInt(elements[j].duration.value, 10) / parseInt(60, 10);
                                        $('#time_fare').text(Math.round(time_fare));
                                        var vehicleId = $('#iVehicleTypeId').val();
                                        $.ajax({
                                            type: "POST",
                                            url: 'admin/ajax_find_rider_by_number.php',
                                            data: 'vehicleId=' + vehicleId,
                                            success: function (dataHtml) {
                                                if (dataHtml != "") {
                                                    var result = dataHtml.split(':');
                                                    $('#minimum_fare_price').text(parseFloat(result[3]).toFixed(2));
                                                    $('#base_fare_price').text(parseFloat(result[0]).toFixed(2));
                                                    $('#dist_fare_price').text(parseFloat(result[1] * $('#dist_fare').text()).toFixed(2));
                                                    $('#time_fare_price').text(parseFloat(result[2] * $('#time_fare').text()).toFixed(2));
                                                    var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                    if (parseInt(totalPrice) >= parseInt($('#minimum_fare_price').text())) {
                                                        $('#total_fare_price').text(totalPrice);
                                                    } else {
                                                        $('#total_fare_price').text($('#minimum_fare_price').text());
                                                    }
                                                } else {
                                                    $('#minimum_fare_price').text('0');
                                                    $('#base_fare_price').text('0');
                                                    $('#dist_fare_price').text('0');
                                                    $('#time_fare_price').text('0');
                                                    $('#total_fare_price').text('0');
                                                }
                                            }
                                        });
                                        document.getElementById("location_found").value = 1;
                                        break;
                                    case "NOT_FOUND":
                                        document.getElementById("location_found").value = 0;
                                        break;
                                    case "ZERO_RESULTS":
                                        document.getElementById("location_found").value = 0;
                                        break;
                                }
                            }
                        }
                    } else {
                        html = "error";
                    }
                    $("#results").html(html);
                }
            }
        });
    }

    function callEditFundtion() {
        var from = $('#from').val();
        var to = $('#to').val();

        if (from != '') {
            geocoder.geocode({'address': from}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                        $("#from_lat_long").val((results[0].geometry.location));
                    } else {
                        alert("No results found");
                    }
                } else {
                    var place19 = autocomplete_from.getPlace();
                    $("#from_lat_long").val(place19.geometry.location);
                }
            });
        }
        if (to != '') {
            geocoder.geocode({'address': to}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                        $("#to_lat_long").val((results[0].geometry.location));
                    } else {
                        alert("No results found");
                    }
                } else {
                    var place20 = autocomplete_to.getPlace();
                    $("#to_lat_long").val(place20.geometry.location);
                }
            });
        }

        var fromLatlongs = $("#from_lat").val() + ", " + $("#from_long").val();
        var toLatlongs = $("#to_lat").val() + ", " + $("#to_long").val();
        //alert(fromLatlongs+toLatlongs);
        $("#map-canvas").gmap3({
            getroute: {
                options: {
                    origin: fromLatlongs,
                    destination: toLatlongs,
                    travelMode: google.maps.DirectionsTravelMode.DRIVING
                },
                callback: function (results, status) {
                    chk_route = status;
                    if (!results)
                        return;
                    $(this).gmap3({
                        map: {
                            options: {
                                zoom: 8,
                                //       center: [51.511214, -0.119824]
                                center: [36.849, 54.405]
                            }
                        },
                        directionsrenderer: {
                            options: {
                                directions: results
                            }
                        }
                    });
                }
            }
        });
//Mamad
        $("#map-canvas").gmap3({
            getdistance: {
                options: {
                    origins: fromLatlongs,
                    destinations: toLatlongs,
                    travelMode: google.maps.TravelMode.DRIVING
                },
                callback: function (results, status) {
                    var html = "";
                    if (results) {
                        for (var i = 0; i < results.rows.length; i++) {
                            var elements = results.rows[i].elements;
                            for (var j = 0; j < elements.length; j++) {
                                switch (elements[j].status) {
                                    case "OK":
                                        html += elements[j].distance.text + " (" + elements[j].duration.text + ")<br />";
                                        document.getElementById("distance").value = elements[j].distance.value;
                                        document.getElementById("duration").value = elements[j].duration.value;
                                        var dist_fare = parseInt(elements[j].distance.value, 10) / parseInt(1000, 10);
                                        $('#dist_fare').text(Math.round(dist_fare));
                                        var time_fare = parseInt(elements[j].duration.value, 10) / parseInt(60, 10);
                                        $('#time_fare').text(Math.round(time_fare));
                                        var vehicleId = $('#iVehicleTypeId').val();
                                        $.ajax({
                                            type: "POST",
                                            url: 'admin/ajax_find_rider_by_number.php',
                                            data: 'vehicleId=' + vehicleId,
                                            success: function (dataHtml) {
                                                if (dataHtml != "") {
                                                    var result = dataHtml.split(':');
                                                    $('#minimum_fare_price').text(parseFloat(result[3]).toFixed(2));
                                                    $('#base_fare_price').text(parseFloat(result[0]).toFixed(2));
                                                    $('#dist_fare_price').text(parseFloat(result[1] * $('#dist_fare').text()).toFixed(2));
                                                    $('#time_fare_price').text(parseFloat(result[2] * $('#time_fare').text()).toFixed(2));
                                                    var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                    if (parseInt(totalPrice) >= parseInt($('#minimum_fare_price').text())) {
                                                        $('#total_fare_price').text(totalPrice);
                                                    } else {
                                                        $('#total_fare_price').text($('#minimum_fare_price').text());
                                                    }
                                                } else {
                                                    $('#minimum_fare_price').text('0');
                                                    $('#base_fare_price').text('0');
                                                    $('#dist_fare_price').text('0');
                                                    $('#time_fare_price').text('0');
                                                    $('#total_fare_price').text('0');
                                                }
                                            }
                                        });
                                        document.getElementById("location_found").value = 1;
                                        break;
                                    case "NOT_FOUND":
                                        document.getElementById("location_found").value = 0;
                                        break;
                                    case "ZERO_RESULTS":
                                        document.getElementById("location_found").value = 0;
                                        break;
                                }
                            }
                        }
                    } else {
                        html = "error";
                    }
                    $("#results").html(html);
                }
            }
        });
    }

</script>
<script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    function changeCode(id) {
        var request = $.ajax({
            type: "POST",
            url: 'change_code.php',
            data: 'id=' + id,
            success: function (data) {
                document.getElementById("code").value = data;
            }
        });
    }

    function validate_email(id) {

        var request = $.ajax({
            type: "POST",
            url: 'validate_email.php',
            data: 'id=' + id,
            success: function (data) {
                if (data == 0) {
                    $('#emailCheck').html('<i class="icon icon-remove alert-danger alert">Already Exist,Select Another</i>');
                    setTimeout(function () {
                        $('#emailCheck').html('');
                    }, 3000);
                    $('input[type="submit"]').attr('disabled', 'disabled');
                } else if (data == 1) {
                    var eml = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    result = eml.test(id);
                    if (result == true) {
                        $('#emailCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                        setTimeout(function () {
                            $('#emailCheck').html('');
                        }, 3000);
                        $('input[type="submit"]').removeAttr('disabled');
                    } else {
                        $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Email</i>');
                        setTimeout(function () {
                            $('#emailCheck').html('');
                        }, 3000);
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    }
                }
            }
        });
    }

    function showhideDrivers(dtype) {
        if (dtype == "manual") {
            $('.show_drivers_lists').slideDown();
            $('select[name="iDriverId"]').attr('required', 'required');
        } else {
            $('.show_drivers_lists').slideUp();
            $('select[name="iDriverId"]').removeAttr('required');
        }
    }

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 35 || charCode > 57))
            return false;
        return true;
    }

    $("#reset12").on('click', function () {
        $('#dist_fare').text('0');
        $('#time_fare').text('0');
        $('#minimum_fare_price').text('0');
        $('#base_fare_price').text('0');
        $('#dist_fare_price').text('0');
        $('#time_fare_price').text('0');
        $('#total_fare_price').text('0');
    });

    function shoeDriverDetail002(id) {
        if (id != "") {
            var request2 = $.ajax({
                type: "POST",
                url: 'show_driver.php',
                dataType: 'html',
                data: 'id=' + id,
                success: function (data) {
                    $("#showDriver003").html(data);
                }, error: function (data) {

                }
            });
        } else {
            $("#showDriver003").html('');
        }
    }
</script>
<!-- End: Footer Script -->
</body>
</html>
