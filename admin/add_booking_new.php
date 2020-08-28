<?php
include_once('../common.php');

include_once('savar_check_permission.php');
if (checkPermission('BOOKING') == false)
    die('you dont`t have permission...');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$tbl_name = 'cab_booking';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$iCabBookingId = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : '';
$action = ($iCabBookingId != '') ? 'Edit' : 'Add';
$script = "booking";

$sql = "select cn.vCountry,cn.vPhoneCode from country cn inner join
configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);

//For Country
$sql = "SELECT * from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
//For Currency
$sql = "select * from  currency where eStatus='Active' and eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);


$areaid = '';
if ($_GET['id'] != '') {
    $sqlcpArea = "select * FROM company WHERE 1 and iCompanyId = '" . $_GET['id'] . "'";
    $db_cparea = $obj->MySQLSelect($sqlcpArea);
    $areaid = $db_cparea[0]['iAreaId'];
    $sqlArea = "SELECT * FROM `savar_area` WHERE 1 and `aId` = '" . $areaid . "'";
    $db_area = $obj->MySQLSelect($sqlArea);
    $mapCenter = $db_area[0]['mapCenter'];
    $mapZoom = $db_area[0]['mapZoom'];
    $sql2 = "select * FROM register_driver WHERE 1 AND eStatus='active' and iCompanyId = '" . $_GET['id'] . "' ORDER BY vName ASC";
} else {
    $sql2 = "select * FROM register_driver WHERE 1 AND eStatus='active' ORDER BY vName ASC";
}


if ($areaid != '') {
    $sql1 = "SELECT * FROM `vehicle_type` WHERE vSavarArea = '" . $areaid . "'";
    $db_carType = $obj->MySQLSelect($sql1);
} else {
    $sql1 = "SELECT * FROM `vehicle_type` WHERE 1";
    $db_carType = $obj->MySQLSelect($sql1);
}

$db_records_online = $obj->MySQLSelect($sql2);
// echo "<pre>";
// print_r($db_records_online); die;
$dBooking_date = date("Y-m-d");
$dBooking_time = date("H:i:s");
//die($dBooking_time . " " . $dBooking_date);
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
            $dBooking_date = $value['dBooking_date'];
            $vSourceAddresss = $value['vSourceAddresss'];
            $tDestAddress = $value['tDestAddress'];
            $iVehicleTypeId = $value['iVehicleTypeId'];
            $vPhone = $value['vPhone'];
            $tTripComment = $value['tTripComment'];
            $vName = $value['vName'];
            $vLastName = $value['vLastName'];
            $vAddress = $value['vAddress'];
            $vDescription = $value['vDescription'];
            $vEmail = $value['vEmail'];
            $vPhoneCode = $value['vPhoneCode'];
            $vCountry = $value['vCountry'];
            $from_lat_long = '(' . $value['vSourceLatitude'] . ', ' . $value['vSourceLongitude'] . ')';
            $from_lat = $value['vSourceLatitude'];
            $from_long = $value['vSourceLongitude'];
            $to_lat_long = '(' . $value['vDestLatitude'] . ', ' . $value['vDestLongitude'] . ')';
            $to_lat = $value['vDestLatitude'];
            $to_long = $value['vDestLongitude'];
            $eAutoAssign = $value['eAutoAssign'];
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
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
    <link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet'/>
    <title>ادمین | رزرو دستی</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <!-- Google Map Js -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en"></script>-->
    <!--   <script src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB ?>"></script>-->
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

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
                    <h2>رزرو دستی</h2>
                    <?php if ($action == 'Edit') { ?>
                        <a href="cab_booking.php">
                            <input type="button" value="Back to Listing" class="add-btn">
                        </a>
                    <?php } ?>
                </div>
            </div>
            <hr/>
            <div class="body-div add-booking1">
                <a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModal"><i
                            class="fa fa-question-circle" style="font-size: 18px;"></i> چجوری کار میکنه؟</a>

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
                            <input type="hidden" value="" id="user_type" name="user_type">
                            <input type="hidden" value="<?php echo $iUserId; ?>" id="iUserId" name="iUserId">
                            <input type="hidden" value="<?php echo $iCabBookingId; ?>" id="iCabBookingId"
                                   name="iCabBookingId">
                            <input type="hidden" value="<?php echo $GOOGLE_SEVER_API_KEY_WEB; ?>" id="google_server_key"
                                   name="google_server_key">
                            <input type="hidden" value="" id="fromlatitude" name="fromlatitude">
                            <input type="hidden" value="" id="fromlongitude" name="fromlongitude">
                            <input type="hidden" value="" id="getradius" name="getradius">
                            <input type="hidden" value="<?php echo $db_con[0]['vCountry']; ?>" id="vCountry"
                                   name="vCountry">
                            <input type="hidden" value="<?php echo $db_con[0]['vPhoneCode']; ?>" id="vPhoneCode"
                                   name="vPhoneCode">

                            <div class="add-booking-form">
                                <!--	<span>برای انتخاب راننده ها بر اساس کمپانی یک کمپانی را انتخاب کنید -->

                                </br>

                                <!--	<select class="form-control form-control-select" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">
										<option value="add_booking.php">--select--</option>
										<?
                                $sql = "select * from company WHERE eStatus != 'Deleted'";
                                $db_company = $obj->MySQLSelect($sql);
                                for ($i = 0; $i < count($db_company); $i++) {
                                    if ($db_company[$i]['iCompanyId'] == $_GET['id']) {
                                        echo '<option selected value ="add_booking.php?id=' . $db_company[$i]['iCompanyId'] . '">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
                                    } else {
                                        echo '<option value ="add_booking.php?id=' . $db_company[$i]['iCompanyId'] . '">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
                                    }
                                } ?>
									</select> -->
                                </span>
                                <span>
									<input type="text" class="form-control first-name1" name=""
                                           value="<?php echo $db_con[0]['vCountry'] . ' (+' . $db_con[0]['vPhoneCode'] . ')'; ?>"
                                           readonly></span>
                                <span>
										<span>
											<input type="text" pattern="[0-9]{1,}" title="Enter Mobile Number."
                                                   class="form-control add-book-input" name="vPhone" id="vPhone"
                                                   value="<?php echo $vPhone; ?>" placeholder="شماره تلفن را وارد کنید"
                                                   required style="">
											<a class="btn btn-sm btn-info" id="get_details">دریافت اطلاعات</a>
										</span>

										<span> <input type="text" title="Only Alpha character allow"
                                                      class="form-control first-name1" name="vName" id="vName"
                                                      value="<?php echo $vName; ?>" placeholder="نام" required>  <input
                                                    type="text" title="Only Alpha character allow"
                                                    class="form-control last-name1" name="vLastName" id="vLastName"
                                                    value="<?php echo $vLastName; ?>" placeholder="نام خانوادگی"
                                                    required></span>
										<span><input type="hidden" class="form-control" name="vEmail"
                                                     pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$"
                                                     id="vEmail" value="<?php echo $vEmail; ?>" placeholder="ایمیل">
											<div id="emailCheck"></div></span>
											<span> <input type="text"
                                                          class="ride-location1 highalert txt_active form-control first-name1"
                                                          name="vSourceAddresss" id="from"
                                                          value="<?php echo $vSourceAddresss; ?>" placeholder="مبدا"
                                                          required><input type="text"
                                                                          class="ride-location1 highalert txt_active form-control last-name1"
                                                                          name="tDestAddress" id="to"
                                                                          value="<?php echo $tDestAddress; ?>"
                                                                          placeholder="مقصد" required></span>

											<span> <input type="datetime-local" class=" form-control"
                                                          name="dBooking_date" id="datetimepicker4"
                                                          value="<?php echo $dBooking_date . "T" . $dBooking_time; ?>"
                                                          placeholder="Select Date / Time" required></span>

											<span>
												<textarea type="text"
                                                          class="ride-location1 highalert txt_active form-control"
                                                          name="tTripComment" id="tTripComment"
                                                          value="<?php echo $tTripComment; ?>"
                                                          placeholder="توضیحات"></textarea>
											</span>

											<?php $radius_driver = array(5, 10, 15, 20, 25); ?>
											<span>
												<a class="btn btn-sm btn-info" id="select_manual">انتخاب دستی مبدا و مقصد</a>
												<select class="form-control form-control-select" name='radius-id'
                                                        id="radius-id" required onChange="getDriverRadius(this.value)">
													<?php foreach ($radius_driver as $value) { ?>
                                                        <option value="<?php echo $value ?>"><?php echo $value . ' km Radius'; ?></option>
                                                    <?php } ?>
												</select>
											</span>

											<span>
												<input type="text" title="Enter Mobile Number."
                                                       class="form-control add-book-input" name="search_driver_ajax2"
                                                       id="search_driver_ajax2" value="<?php echo $vPhone; ?>"
                                                       placeholder="نام یا نام خانوادگی یا شماره موبایل راننده را وارد کنید"
                                                       style="">
												<a class="btn btn-sm btn-info" id="search_driver_ajax">جست و جو</a>
											</span>

											<?php if (!empty($db_records_online)) { ?>
                                                <span><select class="form-control form-control-select" name='iDriverId'
                                                              id="iDriverId">
													<option value="">انتخاب <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
													<?php foreach ($db_records_online as $db_online) { ?>
                                                        <option value="<?php echo $db_online['iDriverId']; ?>"><?php echo $db_online['vName'] . ' ' . $db_online['vLastName']; ?></option>
                                                    <?php } ?>
												</select></span>
                                            <?php } else { ?>
                                                <div class="row show_drivers_lists">
													<div class="col-lg-12">
														<h5>راننده ای وجود ندارد</h5>
													</div>
												</div>
                                            <?php } ?>
											<span>
												<select class="form-control form-control-select" name='iVehicleTypeId'
                                                        id="iVehicleTypeId" required
                                                        onChange="getFarevalues(this.value)">
													<option value="">انتخاب نوع خودرو</option>

													<? if ($areaid != '') {
                                                        ?>
                                                        <?php foreach ($db_carType as $db_car) { ?>
                                                            <option value="<?php echo $db_car['iVehicleTypeId']; ?>" <?php if ($iVehicleTypeId == $db_car['iVehicleTypeId']) {
                                                                echo "selected";
                                                            } ?> ><?php echo $db_car['vVehicleType']; ?></option>
                                                        <?php } ?>

                                                    <? } else { ?>

                                                        <?php foreach ($db_carType as $db_car) { ?>
                                                            <option value="<?php echo $db_car['iVehicleTypeId']; ?>" <?php if ($iVehicleTypeId == $db_car['iVehicleTypeId']) {
                                                                echo "selected";
                                                            } ?> ><?php echo $db_car['vVehicleType']; ?></option>
                                                        <?php }
                                                    } ?>


														</select></span>
														<span> <input type="submit" class="save btn-info button-submit"
                                                                      name="submit" id="submit" value="افزودن رزرو">
															<input type="button" class="save btn-info button-submit"
                                                                   name="estimatefare" id="estimatefare"
                                                                   value="محاسبه کرایه" onclick="estimateFare()">

															<input type="reset" class="save btn-info button-submit"
                                                                   name="reset" id="reset12"
                                                                   value="پاک کردن لیست"></span>
                            </div>


                        </form>
                        <div class="total-price">
                            <ul>
                                <li><b>حداقل کرایه</b> : <?php echo $db_currency[0]['vSymbol'] ?> <em
                                            id="minimum_fare_price">0</em></li>
                                <li><b>کرایه پایه</b> : <?php echo $db_currency[0]['vSymbol'] ?> <em
                                            id="base_fare_price">0</em></li>
                                <li><b>فاصله (<em id="dist_fare">0</em> KMs)</b>
                                    : <?php echo $db_currency[0]['vSymbol'] ?> <em id="dist_fare_price">0</em></li>
                                <li><b>زمان (<em id="time_fare">0</em> دقیقه)</b>
                                    : <?php echo $db_currency[0]['vSymbol'] ?> <em id="time_fare_price">0</em></li>
                            </ul>
                            <span>کرایه کل<b><?php echo $db_currency[0]['vSymbol'] ?> <em
                                            id="total_fare_price">0</em></b></span>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="gmap-div gmap-div1" style="float:right;width:100%;">
                            <div id="map" class="gmap3"></div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->


<?
include_once('footer.php');
?>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.12.0-rc.1/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" type="text/css" media="screen"
      href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<script type="text/javascript">

    L.cedarmaps.accessToken = '4a0a95307ce57f099d59085bf0b36c46668124b2'; // See the note below on how to get an access token

    // Getting maps info from a tileJSON source
    var tileJSONUrl = 'https://api.cedarmaps.com/v1/tiles/cedarmaps.streets.json?access_token=' + L.cedarmaps.accessToken;
    var map = L.cedarmaps.map('map', tileJSONUrl, {
        scrollWheelZoom: true,
        <?php if ($mapCenter != '') {
            echo 'center: ' . $mapCenter . ',';
        } else {
            echo 'center: {lat: 32.93493, lng: 53.87695},';
            //echo 'center: {lat: 51.505, lng: -0.09},';
        }
        if ($mapZoom != '') {
            echo 'zoom:' . $mapZoom . ',';
        } else {
            echo 'zoom: 6,';

        } ?>
        fullscreenControl: true
    });
    var LeafIcon = L.Icon.extend({
        options: {}
    });

    var geocoder = L.cedarmaps.geocoder('cedarmaps.streets');
    var centerr = map.getCenter();

    $("#to").on("input", function () {
        var toaddrescompl = $(this).val();
        var requestURL = 'http://enocb.i2xi.com/index.php?key=AIzaSyDXgughFrm5qOvITedJwPIf1NZp9aMN07A&input=' + toaddrescompl + '&location=' + centerr.lat + ',' + centerr.lng;
        var proxyURL = 'https://cors-anywhere.herokuapp.com';

        $("#to").autocomplete({
            source: function (request, response) {
                $.getJSON(proxyURL + '/' + requestURL, function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item['formatted_address'] + "------->" + item['name'],
                            name: item['geometry']['location']['lat'] + ',' + item['geometry']['location']['lng'],
                        };
                    }));
                });
            },
            delay: 0,
            select: function (event, ui) {
                $("#to_lat_long").val('(' + ui.item.name + ')');
                var wwslkjdfksldjfksww = ui.item.name.split(',')
                $("#to_lat").val(wwslkjdfksldjfksww[0]);
                $("#to_long").val(wwslkjdfksldjfksww[1]);
                $("#to").val(ui.item.label);  // ui.item.value contains the id of the selected label
                go_for_action('to');
            }
        });
    });

    $("#from").on("input", function () {
        var toaddrescompl = $(this).val();
        var requestURL = 'http://enocb.i2xi.com/index.php?key=AIzaSyDXgughFrm5qOvITedJwPIf1NZp9aMN07A&input=' + toaddrescompl + '&location=' + centerr.lat + ',' + centerr.lng;
        var proxyURL = 'https://cors-anywhere.herokuapp.com';

        $("#from").autocomplete({
            source: function (request, response) {
                $.getJSON(proxyURL + '/' + requestURL, function (data) {
                    response($.map(data, function (item) {

                        return {
                            label: item['formatted_address'] + "------->" + item['name'],
                            name: item['geometry']['location']['lat'] + ',' + item['geometry']['location']['lng'],
                        };
                    }));
                });
            },
            delay: 0,
            select: function (event, ui) {
                //console.log(sadasdF);
                $("#from_lat_long").val('(' + ui.item.name + ')');
                var wwslkjdfksldjfksww = ui.item.name.split(',')
                $("#from_lat").val(wwslkjdfksldjfksww[0]);
                $("#from_long").val(wwslkjdfksldjfksww[1]);
                $("#from").val(ui.item.label);  // ui.item.value contains the id of the selected label
                go_for_action('from');
            }
        });
    });
</script>

<script>
    $('#search_driver_ajax').on('click', function () {
        $('#iDriverId')
            .find('option')
            .remove();

        var phone = $('#search_driver_ajax2').val();
        $.ajax({
            type: "POST",
            url: 'search_driver_ajax.php',
            data: 'phone=' + phone,
            success: function (dataHtml) {
                console.log(dataHtml);
                if (dataHtml != "" || dataHtml != ":::~" || dataHtml != " ") {
                    var result;
                    var result1 = dataHtml.split('~');
                    //alert(result1.length);
                    for (i = 0; i < result1.length - 1; i++) {
                        result = result1[i].split(':');
                        //alert(result1[1]);
                        $('#iDriverId')
                            .find('option')
                            .end()
                            .append('<option value="' + result[2] + '">' + result[0] + ' ' + result[1] + '</option>')
                            .val(result[2])
                        ;
                    }


                } else {
                    $('#iDriverId')
                        .find('option')
                        .remove()
                        .end()
                        .append('<option value="داده ای یافت نشد">داده ای یافت نشد</option>')
                        .val('داده ای یافت نشد')
                    ;
                }
            }
        });
    });


    function estimateFare() {
        //alert("sorry my fault, didn't mean to but now I am in byte nirvana");

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
                url: '../ajax_calc_estimatefare.php',
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


    var clikceddasti = 0;
    $('#select_manual').on('click', function () {
        if (clikceddasti == 0) {
            clikceddasti = 1;
            $("#from_lat_long").val('');
            $("#from_lat").val('');
            $("#from_long").val('');
            $("#to_lat_long").val('');
            $("#to_lat").val('');
            $("#to_long").val('');
        }
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


    var markers = [];
    var markers2 = [];
    var locations1;
    var locations2 = [];
    var content1;

    $('.gallery').each(function () { // the containers for all your galleries
        $(this).magnificPopup({
            delegate: 'a', // the selector for gallery item
            type: 'image',
            gallery: {
                enabled: true
            }
        });
    });

    /*$(function () {
    newDate = new Date('Y-M-D');
    $('#datetimepicker4').datetimepicker({
    format: 'YYYY-MM-DD HH:mm:ss',
    minDate: moment().format('l'),
    ignoreReadonly: true,
    sideBySide: true,
});
});*/
    <?    if($areaid != '')
    {?>


    function getFarevalues(vehicleId) {
        $.ajax({
            type: "POST",
            url: 'ajax_find_rider_by_number.php',
            data: 'vehicleId=' + vehicleId + '&iAreaId=<?php echo $areaid?>',
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
    <?}else {?>
    function getFarevalues(vehicleId) {
        $.ajax({
            type: "POST",
            url: 'ajax_find_rider_by_number.php',
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
    <?}?>





    $('#get_details').on('click', function () {
        var phone = $('#vPhone').val();
        $.ajax({
            type: "POST",
            url: 'ajax_find_rider_by_number.php',
            data: 'phone=' + phone,
            success: function (dataHtml) {
                if (dataHtml != "") {
                    $("#user_type").val('registered');
                    var result = dataHtml.split(':');
                    $('#vName').val(result[0]);
                    $('#vLastName').val(result[1]);
                    $('#vEmail').val(result[2]);
                    $('#iUserId').val(result[3]);
                    $('#vAddress').val(result[4]);
                    $('#vDescription').val(result[5]);
                    $('#from').val(result[6]);
                    $('#to').val(result[7]);
                    $("#from_lat_long").val(result[8]);
                    $("#from_lat").val(result[9]);
                    $("#from_long").val(result[10]);
                    $("#to_lat_long").val(result[11]);
                    $("#to_lat").val(result[12]);
                    $("#to_long").val(result[13]);
                    $('#tTripComment').val(result[14]);

                } else {
                    $("#user_type").val('');
                    $('#vName').val('');
                    $('#vLastName').val('');
                    $('#vEmail').val('');
                    $('#vAddress').val('');
                    $('#vDescription').val('');
                    $('#iUserId').val('');
                    $('#from').val('');
                    $('#to').val('');
                    $("#from_lat_long").val('');
                    $("#from_lat").val('');
                    $("#from_long").val('');
                    $("#to_lat_long").val('');
                    $("#to_lat").val('');
                    $("#to_long").val('');
                    $('#tTripComment').val('');
                }
            }
        });
    });

    var map;
    var geocoder;
    var autocomplete_from;
    var autocomplete_to;
    /* function initialize() {
    geocoder = new google.maps.Geocoder();
    var mapOptions = {
    zoom: 4,
    center: new google.maps.LatLng('20.1849963', '64.4125062')
};
map = new google.maps.Map(document.getElementById('map-canvas'),
mapOptions);
<?php if($action == "Edit") { ?>
						callEditFundtion();
						<?php } ?>
					}

					$(document).ready(function () {
					google.maps.event.addDomListener(window, 'load', initialize);
				});*/


    var map;
    var setZoom = 14;

    function initialize() {
        // geocoder = new google.maps.Geocoder();

        var mapOptions = {
            zoom: 4,
            center: new google.maps.LatLng('20.1849963', '64.4125062')
        };
        map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);

        //callForedit();

    }

    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initialize);

    });
    <?php if($action == "Edit") { ?>

    $(window).bind("load", function () {
        // Code here
        callEditFundtion();
    });

    <?php } ?>

    /*<?php //} ?>*/
    function getDriverRadius(Radius) {
        var fromlocation = document.getElementById('from').value;
        getLatitudeLongitude(showResult, fromlocation, Radius);
    }

    $(function () {

        var getradius1 = $("#radius-id option:selected").val();
        var getradius = $("#getradius").val(getradius1);

        var from = document.getElementById('from');
        autocomplete_from = new google.maps.places.Autocomplete(from);
        google.maps.event.addListener(autocomplete_from, 'place_changed', function () {
            var place = autocomplete_from.getPlace();
            //console.log(autocomplete_from.);
            $("#from_lat_long").val(place.geometry.location);
            $("#from_lat").val(place.geometry.location.lat());
            $("#from_long").val(place.geometry.location.lng());
            //go_for_action();
            var fromlocation = document.getElementById('from').value;
            getLatitudeLongitude(showResult, fromlocation, getradius);
        });

        var to = document.getElementById('to');
        autocomplete_to = new google.maps.places.Autocomplete(to);
        google.maps.event.addListener(autocomplete_to, 'place_changed', function () {
            var place = autocomplete_to.getPlace();
            $("#to_lat_long").val(place.geometry.location);
            $("#to_lat").val(place.geometry.location.lat());
            $("#to_long").val(place.geometry.location.lng());
            go_for_action();
        });


    });

    function go_for_action(where) {
        if (where == 'from') {
            show_locationNew('from');
        } else if (where == 'to') {
            show_locationNew('to');
        }
    }

    function getLatitudeLongitude(callback, address, getradius) {
        // If adress is not supplied, use default value 'Ferrol, Galicia, Spain'
        address = address || 'Ferrol, Galicia, Spain';
        // Initialize the Geocoder
        geocoder = new google.maps.Geocoder();
        if (geocoder) {
            geocoder.geocode({
                'address': address
            }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    callback(results[0], getradius);
                }
            });
        }
    }

    function showResult(result, limitdistance) {


        var limitdistance1 = document.getElementById('radius-id').value;
        //alert('showResult'+limitdistance1);
        var getgoogleserverkey = document.getElementById('google_server_key').value;
        var getLat = document.getElementById('fromlatitude').value = result.geometry.location.lat();


        var getLng = document.getElementById('fromlongitude').value = result.geometry.location.lng();


        $.ajax({
            type: "POST",
            url: 'ajax_find_driver_by_number.php',
            data: {getlan: getLat, getlng: getLng, googlekeydata: getgoogleserverkey, limitdistance: limitdistance1},
            dataType: 'json',
            success: function (response) {
                var locations = [];
                for (i = 0; i < response.length; i++) {
                    locations[i] = [];
                    locations[i].push(response[i].name);
                    locations[i].push(response[i].lat);
                    locations[i].push(response[i].lag);
                    locations[i].push(response[i].add);
                    locations[i].push(response[i].iDriverId);
                }
                if (limitdistance == 5) {
                    setZoom = 14;

                } else if (limitdistance == 10) {
                    setZoom = 13;

                } else if (limitdistance == 15) {
                    setZoom = 12;

                } else if (limitdistance == 20) {
                    setZoom = 11;

                } else if (limitdistance == 25) {
                    setZoom = 10;
                }

                latlngset = new google.maps.LatLng(getLat, getLng);
                markers2 = new google.maps.Marker({
                    map: map, title: name, position: latlngset, icon: "../webimages/upload/mapmarker/source_marker.png"
                });
                map.setCenter(markers2.getPosition());
                map.setZoom(setZoom);

                setMarkers(map, locations);
                if (response == "") {
                    latlngset = new google.maps.LatLng(getLat, getLng);
                    markers2 = new google.maps.Marker({
                        map: map,
                        title: name,
                        position: latlngset,
                        icon: "../webimages/upload/mapmarker/source_marker.png"
                    });
                    map.setCenter(markers2.getPosition());
                }
            }
        });
    }

    function setMarkers(map, locations) {

        var marker, i;
        for (i = 0; i < locations.length; i++) {
            var name = locations[i][0]
            var lat = locations[i][1]
            var long = locations[i][2]
            var add = locations[i][3]
            var driverId = locations[i][4]

            latlngset = new google.maps.LatLng(lat, long);
            marker = new google.maps.Marker({
                map: map, title: name, position: latlngset, icon: "../webimages/upload/mapmarker/car-green.png"
            });
            //map.setCenter(marker.getPosition())
            var content = "Driver Name: " + name + '</h3><br>' + " " + "Address: " + add + "<br> " + "<a href='javascript:void(0)' onClick='AssignDriver(" + driverId + ");'>Assign Driver</a>";

            var infowindow = new google.maps.InfoWindow();

            google.maps.event.addListener(marker, 'click', (function (marker, content, infowindow) {
                return function () {
                    infowindow.setContent(content);
                    infowindow.open(map, marker);
                };
            })(marker, content, infowindow));

        }
    }

    function AssignDriver(driverId) {

        $('#iDriverId').val(driverId);

    }

</script>
<script type="text/javascript" src="js/gmap3.js"></script>
<script type="text/javascript">
    var chk_route;
    var marker1;
    var marker;


    function show_locationNew(where) {
        var startIcon = new LeafIcon({iconUrl: '../assets/img/savar/StartLoc.png'});
        var endIcon = new LeafIcon({iconUrl: '../assets/img/savar/EndLoc.png'});
        if (where == 'to') {
            address = $("#to").val();
            lat = $("#to_lat").val();
            long = $("#to_long").val();
            mLabel = where;
            image = {
                url: '../assets/img/savar/EndLoc.png',
                //anchor: new google.maps.Point(25,25)
            };
            //var kolan = [lat,long];
            if (marker1 == undefined || marker1 == null) {
                marker1 = L.marker([lat, long], {icon: endIcon, draggable: true}).bindPopup(mLabel).addTo(map);
            } else {
                var newLatLng = new L.LatLng(lat, long);
                marker1.setLatLng(newLatLng);
            }

            $('#to').removeAttr('required');
        } else if (where == 'from') {
            address = $("#from").val();
            lat = $("#from_lat").val();
            long = $("#from_long").val();
            mLabel = where;
            image = {
                url: '../assets/img/savar/StartLoc.png',
                //anchor: new google.maps.Point(25,25)
            };

            if (marker == undefined || marker == null) {
                marker = L.marker([lat, long], {icon: startIcon, draggable: true}).bindPopup(mLabel).addTo(map);
            } else {
                var newLatLng = new L.LatLng(lat, long);
                marker.setLatLng(newLatLng);
            }

            $('#from').removeAttr('required');
        } else
            return;
        //var point = new google.maps.LatLng( lat,long);

        map.panTo(new L.LatLng(lat, long));

        if (typeof markers[mLabel] == "object")
            markers[mLabel].setMap(null);

        markers[mLabel] = marker;
        // marker.on('dragend', function (e) {
        // 	console.log('marker drag event');
        // 	if( $("#from_lat").val()  == '' ||
        // 	$("#from_long").val()  == '' ||
        // 	$("#to_lat").val()  == '' ||
        // 	$("#to_long").val()  == '')
        // 	{
        // 		//alert("مبدا و مقصد انتخاب نشده است");
        // 	}
        // 	else if($("#iVehicleTypeId").val() == '')
        // 	{
        // 		//alert("نوع وسیله نقلیه انتخاب نشده است");
        // 	}
        // 	else
        // 	{
        //
        // 		$('#total_fare_price').text('0');
        // 		$('#minimum_fare_price').text('0');
        // 		$('#base_fare_price').text('0');
        // 		$('#dist_fare_price').text('0');
        // 		$('#time_fare_price').text('0');
        // 		$('#dist_fare').text('0');
        // 		$('#time_fare').text('0');
        // 		$('#fGDdistance').val('');
        // 		$('#fGDtime').val('');
        //
        // 		$.ajax({
        // 			type: "POST",
        // 			url: '../ajax_calc_estimatefare.php',
        // 			data:
        // 			'from_lat='  + $("#from_lat").val() + "&" +
        // 			'from_long=' + $("#from_long").val() + "&" +
        // 			'to_lat=' + $("#to_lat").val() + "&" +
        // 			'to_long=' + $("#to_long").val() + "&" +
        // 			'vehicle_type_id=' + $("#iVehicleTypeId").val() + "&"
        // 			,
        // 			success: function (datajson)
        // 			{
        // 				console.log(data);
        // 				if(datajson != '')
        // 				{
        // 					var data = jQuery.parseJSON(datajson);
        //
        // 					$('#total_fare_price').text(data.total_fare);
        // 					$('#minimum_fare_price').text(data.iMinFare);
        // 					$('#base_fare_price').text(data.iBaseFare);
        // 					$('#dist_fare_price').text(data.fPricePerKM);
        // 					$('#time_fare_price').text(data.fPricePerMin);
        // 					$('#dist_fare').text(data.tripDistance);
        // 					$('#time_fare').text(data.tripDuration);
        // 					$('#fGDdistance').val(data.tripDistance);
        // 					$('#fGDtime').val(data.tripDuration);
        //
        // 				}
        //
        // 			}
        // 		});
        // 	}
        // });
        // marker1.on('dragend', function (e) {
        // 	console.log('marker drag event');
        // 	if( $("#from_lat").val()  == '' ||
        // 	$("#from_long").val()  == '' ||
        // 	$("#to_lat").val()  == '' ||
        // 	$("#to_long").val()  == '')
        // 	{
        // 		//alert("مبدا و مقصد انتخاب نشده است");
        // 	}
        // 	else if($("#iVehicleTypeId").val() == '')
        // 	{
        // 		//alert("نوع وسیله نقلیه انتخاب نشده است");
        // 	}
        // 	else
        // 	{
        //
        // 		$('#total_fare_price').text('0');
        // 		$('#minimum_fare_price').text('0');
        // 		$('#base_fare_price').text('0');
        // 		$('#dist_fare_price').text('0');
        // 		$('#time_fare_price').text('0');
        // 		$('#dist_fare').text('0');
        // 		$('#time_fare').text('0');
        // 		$('#fGDdistance').val('');
        // 		$('#fGDtime').val('');
        //
        // 		$.ajax({
        // 			type: "POST",
        // 			url: '../ajax_calc_estimatefare.php',
        // 			data:
        // 			'from_lat='  + $("#from_lat").val() + "&" +
        // 			'from_long=' + $("#from_long").val() + "&" +
        // 			'to_lat=' + $("#to_lat").val() + "&" +
        // 			'to_long=' + $("#to_long").val() + "&" +
        // 			'vehicle_type_id=' + $("#iVehicleTypeId").val() + "&"
        // 			,
        // 			success: function (datajson)
        // 			{
        // 				console.log(data);
        // 				if(datajson != '')
        // 				{
        // 					var data = jQuery.parseJSON(datajson);
        //
        // 					$('#total_fare_price').text(data.total_fare);
        // 					$('#minimum_fare_price').text(data.iMinFare);
        // 					$('#base_fare_price').text(data.iBaseFare);
        // 					$('#dist_fare_price').text(data.fPricePerKM);
        // 					$('#time_fare_price').text(data.fPricePerMin);
        // 					$('#dist_fare').text(data.tripDistance);
        // 					$('#time_fare').text(data.tripDuration);
        // 					$('#fGDdistance').val(data.tripDistance);
        // 					$('#fGDtime').val(data.tripDuration);
        //
        // 				}
        //
        // 			}
        // 		});
        // 	}
        // });

        var ssssss;
        marker.on('dragend', function (e) {

            //  alert(allcentermarker3[0]);

            lat = marker.getLatLng().lat;
            lon = marker.getLatLng().lng;
            ssssss = marker.getPopup();
            if (ssssss.getContent() == 'from') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#from").val(
                        ((res.result.city == undefined || res.result.city == "") ? "" : "," + res.result.city) +
                        ((res.result.name == undefined || res.result.name == "") ? "" : "," + res.result.name) +
                        ((res.result.address == undefined || res.result.address == "") ? "" : "," + res.result.address));
                });
                $("#from_lat").val(lat);
                $("#from_long").val(lon);
                $("#from_lat_long").val('(' + lat + ',' + lon + ')');
                estimateFare();
            } else if (ssssss.getContent() == 'to') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#to").val(
                        ((res.result.city == undefined || res.result.city == "") ? "" : "," + res.result.city) +
                        ((res.result.name == undefined || res.result.name == "") ? "" : "," + res.result.name) +
                        ((res.result.address == undefined || res.result.address == "") ? "" : "," + res.result.address));
                });
                $("#to_lat").val(lat);
                $("#to_long").val(lon);
                $("#to_lat_long").val('(' + lat + ',' + lon + ')');
                estimateFare();
            }
        });
        marker1.on('dragend', function (e) {

            //  alert(allcentermarker3[0]);

            lat = marker1.getLatLng().lat;
            lon = marker1.getLatLng().lng;
            ssssss = marker1.getPopup();
            if (ssssss.getContent() == 'from') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#from").val(
                        ((res.result.city == undefined || res.result.city == "") ? "" : "," + res.result.city) +
                        ((res.result.name == undefined || res.result.name == "") ? "" : "," + res.result.name) +
                        ((res.result.address == undefined || res.result.address == "") ? "" : "," + res.result.address)
                    );
                });
                $("#from_lat").val(lat);
                $("#from_long").val(lon);
                $("#from_lat_long").val('(' + lat + ',' + lon + ')');
                estimateFare();
            } else if (ssssss.getContent() == 'to') {
                geocoder.reverseQuery({lat: lat, lng: lon}, function callback(err, res) {
                    $("#to").val(
                        ((res.result.city == undefined || res.result.city == "") ? "" : "," + res.result.city) +
                        ((res.result.name == undefined || res.result.name == "") ? "" : "," + res.result.name) +
                        ((res.result.address == undefined || res.result.address == "") ? "" : "," + res.result.address));
                });
                $("#to_lat").val(lat);
                $("#to_long").val(lon);
                $("#to_lat_long").val('(' + lat + ',' + lon + ')');
                estimateFare();
            }
        });


    }


    function show_location(address) {
        //alert("show_location");
        clearThat();
        $('#map-canvas').gmap3({
            marker: {
                address: address
            },
            map: {
                options: {
                    zoom: 8
                }
            }
        });
    }

    function clearThat() {
        var opts = {};
        opts.name = ["marker", "directionsrenderer"];
        opts.first = true;
        $('#map-canvas').gmap3({clear: opts});
    }

    function from_to(from, to) {


        //clearThat();
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
                    /*  $(this).gmap3({
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
}); */
                }
            }
        });

        $("#map").gmap3({
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
                                            url: 'ajax_find_rider_by_number.php',
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

        /* if(from != ''){
        geocoder.geocode( { 'address': from}, function(results, status) {
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
} */
        /* if(to != ''){
        geocoder.geocode( { 'address': to}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
        if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
        $("#to_lat_long").val((results[0].geometry.location));
        } else {
        alert("No results found");
        }
        } else {
        //var place20 = autocomplete_to.getPlace();
        //$("#to_lat_long").val(place20.geometry.location);
        }
        });
        } */


        var fromLatlongs = $("#from_lat").val() + ", " + $("#from_long").val();
        var toLatlongs = $("#to_lat").val() + ", " + $("#to_long").val();
        var getradius = document.getElementById('radius-id').value;

//alert(getradius);
        getLatitudeLongitude(showResult, from, getradius);
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
                    /*$(this).gmap3({
                    map: {
                    options: {
                    zoom: 8,
                    //       center: [51.511214, -0.119824]
                    center: [58.0000, 20.0000]
                }
            },
            directionsrenderer: {
            options: {
            directions: results
        }
        }
        }); */
                }
            }
        });

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
                                            url: 'ajax_find_rider_by_number.php',
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
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
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

    $("#eAutoAssign").on('change', function () {
        if ($(this).prop('checked')) {
            $("#iDriverId").val('');
            $("#iDriverId").attr('disabled', 'disabled');
        } else {
            $("#iDriverId").removeAttr('disabled');
        }
    });
    var bookId = '<?php echo $iCabBookingId; ?>';
    if (bookId != "") {
        if ($("#eAutoAssign").prop('checked')) {
            $("#iDriverId").val('');
            $("#iDriverId").attr('disabled', 'disabled');
        } else {
            $("#iDriverId").removeAttr('disabled');
        }
    }

</script>
<!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD289fTsyrLYTErXMfNM06Ks4bN0ziq_CU&libraries=places&callback=initMap"
async defer></script>-->
</body>
<!-- END BODY-->
</html>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"> How It Works?</h4>
            </div>
            <div class="modal-body">
                <p><b>Flow </b>: Through "Manual Taxi Dispatch" Feature, you can book Rides for customers who ordered
                    for a Ride by calling you. There will be customers who may not have iPhone or Android Phone or may
                    not have app installed on their phone. In this case, they will call Taxi Company (your company) and
                    order Ride which may be needed immediately or after some time later.</p>
                <p>Here, you will fill their info in the form and dispatch book a taxi ride for him.</p>
                <p>The Driver will receive info on his App and will pickup the rider at the scheduled time.</p>
                <p>- If the customer is already registered with us, just enter his phone number and his info will be
                    fetched from the database when "Get Details" button is clicked. Else fill the form.</p>
                <p>- Once the Trip detail is added, Fare estimate will be calculated based on Pick-Up Location, Drop-Off
                    Location and Car Type.</p>
                <p>- Admin will need to communicate & confirm with Driver and then select him as Driver so the Ride can
                    be allotted to him. </p>
                <p>- Clicking on "Book" Button, the Booking detail will be saved and will take Administrator to the
                    "Ride Later Booking" Section. This page will show all such bookings.</p>
                <p>- The assigned Driver can see the upcoming Bookings from his App under "My Bookings" section.</p>
                <p>- Driver will have option to "Start Trip" when he reaches the Pickup Location at scheduled time or
                    "Cancel Trip" if he cannot take the ride for some reason. If the Driver clicks on "Cancel Trip", a
                    notification will be sent to Administrator so he can make alternate arrangements.</p>
                <p>- Upon clicking on "Start Trip", the ride will start in driver's App in regular way.</p>
                <p><span><img src="images/mobile_app_booking.png"></img></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
