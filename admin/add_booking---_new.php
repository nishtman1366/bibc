<?php
include_once('../common.php');

include_once('savar_check_permission.php');
if(checkPermission('BOOKING') == false)
die('you dont`t have permission...');

$curtime2 = time();
if (!isset($generalobjAdmin)) {
  require_once(TPATH_CLASS . "class.general_admin.php");
  $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$script = "booking";
$tbl_name = 'cab_booking';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$iCabBookingId = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : '';
$action = ($iCabBookingId != '') ? 'Edit' : 'Add';
$iAreaId = $_GET['iAreaId'];
// $iCompanyId = $_GET['iCompanyId'];

$sql="select cn.vCountry,cn.vPhoneCode from country cn inner join
configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);

//For Country
$sql = "SELECT * from country where eStatus = 'Active'" ;
$db_code = $obj->MySQLSelect($sql);

//For Currency
$sql="select * from  currency where eStatus='Active' and eDefault = 'Yes'";
$db_currency=$obj->MySQLSelect($sql);

$sql="select * from  savar_area where sActive='Yes'";
$db_areas=$obj->MySQLSelect($sql);

$company_sql = "select iCompanyId from company WHERE eStatus != 'Deleted'";
$sql2 = '';
if ($iAreaId && $iAreaId != '') {

  $company_sql .= " AND iAreaId= ".$iAreaId;
  $sqlArea = "SELECT * FROM `savar_area` WHERE aId=".$iAreaId;
  $db_area = $obj->MySQLSelect($sqlArea);
  $mapCenter = $db_area[0]['mapCenter'];
  $mapZoom = $db_area[0]['mapZoom'];
  $sql2 = "select * FROM register_driver WHERE 1 AND vAvailability = 'Available' AND vTripStatus <> 'On Going Trip' AND vTripStatus <> 'Active' AND eStatus='active' and iCompanyId IN (" . $company_sql . ") ORDER BY vName ASC";
  $sql1 = "SELECT * FROM `vehicle_type` WHERE vSavarArea = '".$iAreaId."'";
  $db_carType = $obj->MySQLSelect($sql1);
}
else {
  $sql1 = "SELECT * FROM `vehicle_type` WHERE 1";
  $db_carType = $obj->MySQLSelect($sql1);
}

$db_company=$obj->MySQLSelect($company_sql);
$db_records_online = $obj->MySQLSelect($sql2);

$dBooking_date = date("Y-m-d");
$dBooking_time = date("H:i:s");
if ($action == 'Edit') {
  $sql = "SELECT * FROM " . $tbl_name . " LEFT JOIN register_user on register_user.iUserId=" . $tbl_name . ".iUserId WHERE " . $tbl_name . ".iCabBookingId = '" . $iCabBookingId . "'";
  $db_data = $obj->MySQLSelect($sql);
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
      $from_lat_long = '('.$value['vSourceLatitude'].', '.$value['vSourceLongitude'].')';
      $from_lat = $value['vSourceLatitude'];
      $from_long = $value['vSourceLongitude'];
      $to_lat_long = '('.$value['vDestLatitude'].', '.$value['vDestLongitude'].')';
      $to_lat = $value['vDestLatitude'];
      $to_long = $value['vDestLongitude'];
      $eAutoAssign = $value['eAutoAssign'];
    }
  }
}
$mapCenter = str_replace("\"","",$mapCenter);
?>
<!DOCTYPE html>
<html lang="en">
<html class=" geolocation history svg localstorage atobbtoa atob-btoa canvas webgl supports fullscreen cssfilters cssmask" data-locale="fa"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!-- BEGIN HEAD-->
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  
  <title>ادمین | رزرو دستی</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
  <?
  include_once('global_files.php');
  ?>
  <!-- On OFF switch -->
  <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
  <!-- Google Map Js -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
  <script type='text/javascript' src='../assets/map/gmaps.js'></script>
  
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 " >

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
        <hr />
        <div class="body-div add-booking1">
          <a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModal"><i class="fa fa-question-circle" style="font-size: 18px;"></i> چجوری کار میکنه؟</a>

          <div class="form-group">
            <?php if ($success == "1") {?>
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

            <?php if ($success == 2) {?>
              <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
              </div><br/>
            <?php } ?>
            <?php if ($success == 0 && $var_msg != "") {?>
              <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                <?php echo $var_msg;?>
              </div><br/>
            <?php } ?>
            <div class="col-lg-5">
              <form name="add_booking_form" id="add_booking_form" method="post" action="action_booking.php" enctype="multipart/form-data">
                <input type="hidden" name="distance" id="distance" value="<?php echo  $vDistance; ?>">
                <input type="hidden" name="duration" id="duration" value="<?php echo  $vDuration; ?>">
                <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?php echo  $from_lat_long; ?>" >
                <input type="hidden" name="from_lat" id="from_lat" value="<?php echo  $from_lat; ?>" >
                <input type="hidden" name="from_long" id="from_long" value="<?php echo  $from_long; ?>" >
                <input type="hidden" name="to_lat_long" id="to_lat_long" value="<?php echo  $to_lat_long; ?>" >
                <input type="hidden" name="to_lat" id="to_lat" value="<?php echo  $to_lat; ?>" >
                <input type="hidden" name="to_long" id="to_long" value="<?php echo  $to_long; ?>" >
                <input type="hidden" value="1" id="location_found" name="location_found">
                <input type="hidden" value="" id="user_type" name="user_type" >
                <input type="hidden" value="<?php echo  $iUserId; ?>" id="iUserId" name="iUserId" >
                <input type="hidden" value="<?php echo  $iCabBookingId; ?>" id="iCabBookingId" name="iCabBookingId" >
                <input type="hidden" value="<?php echo $GOOGLE_SEVER_API_KEY_WEB; ?>" id="google_server_key" name="google_server_key" >
                <input type="hidden" value="" id="fromlatitude" name="fromlatitude" >
                <input type="hidden" value="" id="fromlongitude" name="fromlongitude" >
                <input type="hidden" value="" id="getradius" name="getradius" >
                <input type="hidden" value="<?php echo $db_con[0]['vCountry'];?>" id="vCountry" name="vCountry" >
                <input type="hidden" value="<?php echo $db_con[0]['vPhoneCode']; ?>" id="vPhoneCode" name="vPhoneCode" >

                <div class="add-booking-form">

                  <span>لطفا نام شهر مورد نظر را انتخاب کنید
                  </br>
                  <select class="form-control form-control-select" name = 'iAreaId' id = 'iAreaId' onchange="location = this.value;">
                    <option value="add_booking_new.php">--select--</option>
                    <?
                    for ($i = 0; $i < count($db_areas); $i++) {
                      if($db_areas[$i]['aId'] == $iAreaId) {
                        echo '<option selected value ="add_booking_new.php?iAreaId=' . $db_areas[$i]['aId'] . '">' . $db_areas[$i]['sAreaNamePersian'] . "</option>";
                      }
                      else {
                        echo '<option value ="add_booking_new.php?iAreaId=' . $db_areas[$i]['aId'] . '">' . $db_areas[$i]['sAreaNamePersian'] . " </option>";
                      }
                    } ?>
                  </select>
                </span>


                <span>
                  <input type="text" class="form-control first-name1" name="" value="<?php echo $db_con[0]['vCountry'].' (+'.$db_con[0]['vPhoneCode'].')';?>" readonly>
                </span>
                <span>
                  <span>
                    <input type="text" pattern="[0-9]{1,}" title="Enter Mobile Number." class="form-control add-book-input" name="vPhone"  id="vPhone" value="<?php echo  $vPhone; ?>" placeholder="شماره تلفن را وارد کنید" required style="">
                    <a class="btn btn-sm btn-info" id="get_details" >دریافت اطلاعات</a>
                  </span>

                  <span> <input type="text" title="Only Alpha character allow" class="form-control first-name1" name="vName"  id="vName" value="<?php echo  $vName; ?>" placeholder="نام" required>  <input type="text" title="Only Alpha character allow" class="form-control last-name1" name="vLastName"  id="vLastName" value="<?php echo  $vLastName; ?>" placeholder="نام خانوادگی" required></span>
                  <span><input type="hidden" class="form-control" name="vEmail" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" id="vEmail" value="<?php echo  $vEmail; ?>" placeholder="ایمیل" >
                    <div id="emailCheck"></div></span>
                    <span> <input type="text" class="ride-location1 highalert txt_active form-control first-name1" name="vSourceAddresss"  id="from" value="<?php echo  $vSourceAddresss; ?>" placeholder="مبدا" required><input type="text" class="ride-location1 highalert txt_active form-control last-name1" name="tDestAddress"  id="to" value="<?php echo  $tDestAddress; ?>" placeholder="مقصد" required></span>
                    <span> <input type="datetime-local" class=" form-control" name="dBooking_date"  id="datetimepicker4" value="<?php echo  $dBooking_date . "T" . $dBooking_time; ?>" placeholder="Select Date / Time" required></span>

                    <span>
                      <textarea type="text" class="ride-location1 highalert txt_active form-control" name="tTripComment"  id="tTripComment" value="<?php echo  $tTripComment; ?>" placeholder="توضیحات"></textarea>
                    </span>

                    <?php  $radius_driver = array(5,10,15,20,25); ?>
                    <span>
                      <a class="btn btn-sm btn-info" id="select_manual" >انتخاب دستی مبدا و مقصد</a>
                      <select class="form-control form-control-select" name='radius-id' id="radius-id" required onChange="getDriverRadius(this.value)">
                        <?php foreach ($radius_driver as $value) { ?>
                          <option value="<?php echo $value ?>"><?php echo $value . ' km Radius'; ?></option>
                        <?php } ?>
                      </select>
                    </span>

                    <span>
                      <input type="text"  title="Enter Mobile Number." class="form-control add-book-input" name="search_driver_ajax2"  id="search_driver_ajax2" value="<?php echo  $vPhone; ?>" placeholder="نام یا نام خانوادگی یا شماره موبایل راننده را وارد کنید"  style="">
                      <a class="btn btn-sm btn-info" id="search_driver_ajax" >جست و جو</a>
                    </span>

                    <?php if(!empty($db_records_online)) { ?>
                      <span><select class="form-control form-control-select" name='iDriverId' id="iDriverId"  >
                        <option value="" >انتخاب <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></option>
                        <?php foreach ($db_records_online as $db_online) { 	$last_driver_online_time2 = strtotime($db_online['tLastOnline']);
                          $online_time_difference2 = $curtime2-$last_driver_online_time2;
                          //die("ss".$last_driver_online_time2);
                          if($online_time_difference2 <= 900){?>
                            <option value="<?php echo $db_online['iDriverId']; ?>"><?php echo $db_online['vName'].' '.$db_online['vLastName']; ?></option>
                          <?php }} ?>
                        </select></span>
                      <?php }else { ?>
                        <div class="row show_drivers_lists">
                          <div class="col-lg-12">
                            <h5>راننده ای وجود ندارد</h5>
                          </div>
                        </div>
                      <?php } ?>
                      <span>
                        <select class="form-control form-control-select" name='iVehicleTypeId' id="iVehicleTypeId" required onChange="getFarevalues(this.value)">
                          <option value="" >انتخاب نوع خودرو</option>

                          <?	if($areaid != '')
                          {?>
                            <?php foreach ($db_carType as $db_car) { ?>
                              <option value="<?php echo $db_car['iVehicleTypeId']; ?>" <?php if($iVehicleTypeId == $db_car['iVehicleTypeId']){ echo "selected"; } ?> ><?php echo $db_car['vVehicleType']; ?></option>
                            <?php } ?>

                            <?}else{?>

                              <?php foreach ($db_carType as $db_car) { ?>
                                <option value="<?php echo $db_car['iVehicleTypeId']; ?>" <?php if($iVehicleTypeId == $db_car['iVehicleTypeId']){ echo "selected"; } ?> ><?php echo $db_car['vVehicleType']; ?></option>
                              <?php }} ?>


                            </select></span>
                            <span> <input type="submit" class="save btn-info button-submit" name="submit" id="submit" value="افزودن رزرو" >
                              <input type="button" class="save btn-info button-submit" name="estimatefare" id="estimatefare" value="محاسبه کرایه" onclick="estimateFare()">

                              <input type="reset" class="save btn-info button-submit" name="reset" id="reset12" value="پاک کردن لیست" ></span>
                            </div>

                          </form>
                          <div class="total-price">
                            <ul>
                              <li><b>حداقل کرایه</b> : <?php echo $db_currency[0]['vSymbol']?> <em id="minimum_fare_price">0</em></li>
                              <li><b>کرایه پایه</b> : <?php echo $db_currency[0]['vSymbol']?> <em id="base_fare_price">0</em></li>
                              <li><b>فاصله (<em id="dist_fare">0</em> KMs)</b> : <?php echo $db_currency[0]['vSymbol']?> <em id="dist_fare_price">0</em></li>
                              <li><b>زمان (<em id="time_fare">0</em> دقیقه)</b> : <?php echo $db_currency[0]['vSymbol']?> <em id="time_fare_price">0</em></li>
                            </ul>
                            <span>کرایه کل<b><?php echo $db_currency[0]['vSymbol']?> <em id="total_fare_price">0</em></b></span>
                          </div>
                        </div>
                        <div class="col-lg-7">
                          <div class="gmap-div gmap-div1" style="float:right;width:100%;">
                            <div id="app" style="position: relative;width: 100;height: 65em;" class="google-map"></div>
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
              <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
              <script type="text/javascript" src="js/moment.min.js"></script>
              <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
              <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
              <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
              <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

              <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/mapp.min.css">
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/fa/style.css" data-path="https://docs.map.ir/dist/css/" data-file="style.css">
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.env.js"></script>
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.min.js"></script>


              <script type="text/javascript">
              let uri = "https://map.ir";
              let key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImRjM2RiMTUxODI1NWE2ZDEzZTY2ZTAwZGZkMGE4NWQzYzNiNzQ4OTI5ZjQwM2M1ODlhY2E4ZTBiZTBlNGEyNTFlOGMzODNhYjMxZjZkNzIxIn0.eyJhdWQiOiI3MDEzIiwianRpIjoiZGMzZGIxNTE4MjU1YTZkMTNlNjZlMDBkZmQwYTg1ZDNjM2I3NDg5MjlmNDAzYzU4OWFjYThlMGJlMGU0YTI1MWU4YzM4M2FiMzFmNmQ3MjEiLCJpYXQiOjE1NzYwMDIwNjcsIm5iZiI6MTU3NjAwMjA2NywiZXhwIjoxNTc4NTA3NjY3LCJzdWIiOiIiLCJzY29wZXMiOlsiYmFzaWMiXX0.P2EzSOm44btMq0T5JM8d4XjO0T3rgA_wpV_Cby2B0Oc0MhErlt9GqfsrWYJaRQnQWSxTlxyyxPu-Ts2JoPvtdcqXpzxERmh6lxOqsqYbzjNF6fNXwwAB480_7J5PzCE2Raw4tGY5HNJKgqHK5_xNvGev8KS3M9UDsvzTGQbcwNTb21_JG2xHpetLt5NUKtj4YEdFFxnO7z3J6I06fKRrS7l6ty1-IIEqDGf6LgqDAtj_ongpIvaEWO9HSrM9OP_PtDfgBjb2GCf398bkEhVBstp5Qs40hhXdzJEzt2O6NsnzkYGldR8n6H6FmFb68TycqwOuLcfUMjET318xC45FQw";
              var map;
              jQuery( document ).ready( function($) {
                var app = new Mapp({
                  element: '#app',
                  presets: {
                    latlng:<?php echo $mapCenter; ?>,
                    zoom:<?php echo $mapZoom; ?>
                  },
                  apiKey: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImRjM2RiMTUxODI1NWE2ZDEzZTY2ZTAwZGZkMGE4NWQzYzNiNzQ4OTI5ZjQwM2M1ODlhY2E4ZTBiZTBlNGEyNTFlOGMzODNhYjMxZjZkNzIxIn0.eyJhdWQiOiI3MDEzIiwianRpIjoiZGMzZGIxNTE4MjU1YTZkMTNlNjZlMDBkZmQwYTg1ZDNjM2I3NDg5MjlmNDAzYzU4OWFjYThlMGJlMGU0YTI1MWU4YzM4M2FiMzFmNmQ3MjEiLCJpYXQiOjE1NzYwMDIwNjcsIm5iZiI6MTU3NjAwMjA2NywiZXhwIjoxNTc4NTA3NjY3LCJzdWIiOiIiLCJzY29wZXMiOlsiYmFzaWMiXX0.P2EzSOm44btMq0T5JM8d4XjO0T3rgA_wpV_Cby2B0Oc0MhErlt9GqfsrWYJaRQnQWSxTlxyyxPu-Ts2JoPvtdcqXpzxERmh6lxOqsqYbzjNF6fNXwwAB480_7J5PzCE2Raw4tGY5HNJKgqHK5_xNvGev8KS3M9UDsvzTGQbcwNTb21_JG2xHpetLt5NUKtj4YEdFFxnO7z3J6I06fKRrS7l6ty1-IIEqDGf6LgqDAtj_ongpIvaEWO9HSrM9OP_PtDfgBjb2GCf398bkEhVBstp5Qs40hhXdzJEzt2O6NsnzkYGldR8n6H6FmFb68TycqwOuLcfUMjET318xC45FQw'
                });
                    app.addLayers();

                $.Mapp.layers.static.build({
                  layers: {
                    base: {
                      default: {
                        server: 'https://map.ir/shiveh',
                        layers: 'Shiveh:ShivehNOPOI',
                        format: 'image/png',
                      },
                    },
                  },
                });

                $.Mapp.logo.implement();
                $.Mapp.zoomControl.implement();
                $.Mapp.fullscreen.implement();
                $.Mapp.userLocation.implement();
              });

              $("#to").on("input", function() {
                var centerr = map.getCenter();
                var toaddrescompl = $(this).val();
                var requestURL = 'http://enocb.i2xi.com/index.php?key=AIzaSyC0LmZzEX0NUlVM2UEwHBcthDCc_6dwpe8&input=' + toaddrescompl + '&location='+ centerr.lat +',' + centerr.lng;
                var proxyURL = 'https://cors-anywhere.herokuapp.com';

                $("#to").autocomplete({
                  source: function (request, response){
                    $.getJSON(proxyURL + '/' + requestURL, function (data) {

                      response($.map( data, function(item) {

                        return {
                          label: item['formatted_address'] + "------->"+ item['name'],
                          name: item['geometry']['location']['lat']+','+item['geometry']['location']['lng'],
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
                    estimateFare();
                  }
                });
              });

              $("#from").on("input", function() {
                var centerr = map.getCenter();
                var toaddrescompl = $(this).val();
                var requestURL = 'http://enocb.i2xi.com/index.php?key=AIzaSyCA7rAQp_i5VH92MKfnhKaXK10MgGhbSIs&input=' + toaddrescompl + '&location='+ centerr.lat +',' + centerr.lng;
                var proxyURL = 'https://cors-anywhere.herokuapp.com';
                console.log(requestURL);

                $("#from").autocomplete({
                  source: function (request, response){
                    $.getJSON(proxyURL + '/' + requestURL, function (data) {
                      response($.map( data, function(item) {

                        return {
                          label: item['formatted_address'] + "------->"+ item['name'],
                          name: item['geometry']['location']['lat']+','+item['geometry']['location']['lng'],
                        };
                      }));
                    });
                  },
                  delay: 0,
                  select: function (event, ui) {
                    $("#from_lat_long").val('(' + ui.item.name + ')');
                    var wwslkjdfksldjfksww = ui.item.name.split(',')
                    $("#from_lat").val(wwslkjdfksldjfksww[0]);
                    $("#from_long").val(wwslkjdfksldjfksww[1]);
                    $("#from").val(ui.item.label);  // ui.item.value contains the id of the selected label
                    go_for_action('from');
                    estimateFare();
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
                  success: function (dataHtml)
                  {
                    if (dataHtml != "" || dataHtml != ":::~" || dataHtml != " ") {
                      var result;
                      var result1 = dataHtml.split('~');
                      //alert(result1.length);
                      for(i = 0; i<result1.length-1;i++)
                      {
                        result = result1[i].split(':');
                        //alert(result1[1]);
                        $('#iDriverId')
                        .find('option')
                        .end()
                        .append('<option value="' + result[2] + '">' + result[0] + ' ' + result[1] + '</option>')
                        .val(result[2])
                        ;
                      }
                    }else {
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

              function estimateFare()
              {
                //alert("sorry my fault, didn't mean to but now I am in byte nirvana");
                if( $("#from_lat").val()  == '' ||
                $("#from_long").val()  == '' ||
                $("#to_lat").val()  == '' ||
                $("#to_long").val()  == '')
                {
                  return;
                }
                else if($("#iVehicleTypeId").val() == '')
                {
                  alert("نوع وسیله نقلیه انتخاب نشده است");
                  return;
                }
                else
                {

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
                    'from_lat='  + $("#from_lat").val() + "&" +
                    'from_long=' + $("#from_long").val() + "&" +
                    'to_lat=' + $("#to_lat").val() + "&" +
                    'to_long=' + $("#to_long").val() + "&" +
                    'vehicle_type_id=' + $("#iVehicleTypeId").val() + "&"
                    ,
                    success: function (datajson)
                    {
                      if(datajson != '')
                      {
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
                if(clikceddasti == 0)
                {
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
                var centerLat = allcentermap3[0];
                var centerLon = allcentermap3[1];
                if($("#from_lat_long").val() == '')
                {
                  $("#from_lat_long").val('(' + centerLat + ',' + centerLon + ')');
                  $("#from_lat").val(centerLat);
                  $("#from_long").val(centerLon);
                  go_for_action('from');
                }

                if($("#to_lat_long").val() == '')
                {
                  $("#to_lat_long").val('(' + centerLat + ',' + centerLon + ')');
                  $("#to_lat").val(centerLat);
                  $("#to_long").val(centerLon);
                  go_for_action('to');
                }
              });

              <?	if($areaid != '')
              {?>
                function getFarevalues(vehicleId) {
                  $.ajax({
                    type: "POST",
                    url: 'ajax_find_rider_by_number.php',
                    data: 'vehicleId=' + vehicleId + '&iAreaId=<?php echo $areaid?>',
                    success: function (dataHtml)
                    {
                      if (dataHtml != "") {
                        var result = dataHtml.split(':');
                        $('#minimum_fare_price').text(parseFloat(result[3]).toFixed(2));
                        $('#base_fare_price').text(parseFloat(result[0]).toFixed(2));
                        $('#dist_fare_price').text(parseFloat(result[1]*$('#dist_fare').text()).toFixed(2));
                        $('#time_fare_price').text(parseFloat(result[2]*$('#time_fare').text()).toFixed(2));
                        var totalPrice = (parseFloat($('#base_fare_price').text())+parseFloat($('#dist_fare_price').text())+parseFloat($('#time_fare_price').text())).toFixed(2);
                        if(parseInt(totalPrice) >= parseInt($('#minimum_fare_price').text())) {
                          $('#total_fare_price').text(totalPrice);
                        }else {
                          $('#total_fare_price').text($('#minimum_fare_price').text());
                        }
                      }else {
                        $('#minimum_fare_price').text('0');
                        $('#base_fare_price').text('0');
                        $('#dist_fare_price').text('0');
                        $('#time_fare_price').text('0');
                        $('#total_fare_price').text('0');
                      }
                      estimateFare();
                    }
                  });
                }
                <?}else {?>
                  function getFarevalues(vehicleId) {
                    $.ajax({
                      type: "POST",
                      url: 'ajax_find_rider_by_number.php',
                      data: 'vehicleId=' + vehicleId,
                      success: function (dataHtml)
                      {
                        if (dataHtml != "") {
                          var result = dataHtml.split(':');
                          $('#minimum_fare_price').text(parseFloat(result[3]).toFixed(2));
                          $('#base_fare_price').text(parseFloat(result[0]).toFixed(2));
                          $('#dist_fare_price').text(parseFloat(result[1]*$('#dist_fare').text()).toFixed(2));
                          $('#time_fare_price').text(parseFloat(result[2]*$('#time_fare').text()).toFixed(2));
                          var totalPrice = (parseFloat($('#base_fare_price').text())+parseFloat($('#dist_fare_price').text())+parseFloat($('#time_fare_price').text())).toFixed(2);
                          if(parseInt(totalPrice) >= parseInt($('#minimum_fare_price').text())) {
                            $('#total_fare_price').text(totalPrice);
                          }else {
                            $('#total_fare_price').text($('#minimum_fare_price').text());
                          }
                        }else {
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
                      success: function (dataHtml)
                      {
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

                        }else {
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

                  function getDriverRadius(Radius)
                  {
                    var fromlocation = document.getElementById('from').value;
                  }

                  function go_for_action(where) {
                    if (where == 'from') {
                      show_locationNew('from');
                    }
                    else if (where == 'to') {
                      show_locationNew('to');
                    }
                  }
                  </script>
                  <script type="text/javascript">
                  var marker1;
                  var marker;

                  function show_locationNew(where) {

                    $.sMap.features();
                    if(where == 'to')
                    {
                      address = $("#to").val();
                      lat = $("#to_lat").val();
                      long = $("#to_long").val();
                      if (marker1 == undefined || marker1 == null) {
                        marker1 = $.sMap.features.marker.create({
                          before: function() {
                          },
                          after: function() {
                          },
                          name: 'destinationMarker',
                          popup: {
                            title: {
                              html: 'مقصد',
                              i18n: '',
                            },
                            description: {
                              html: address,
                              i18n: '',
                            },
                            custom: false,
                          },
                          class: 'default',
                          latlng: {lat: lat, lng: long},
                          popupOpen: false,
                          pan: true,
                          draggable: true,
                          on: {
                            click: function (feature) {
                              var lat = feature["latlng"]["lat"];
                              var lon = feature["latlng"]["lng"];
                              reserve(lat, lon,'to');
                            },
                            contextmenu: function (feature) {}
                          }
                        });
                        //ToDo: marker icon = '../assets/img/savar/EndLoc.png';
                      }
                      else {
                        //ToDo: change marker location and zoom to
                      }
                      $('#to').removeAttr('required');
                    }
                    else if(where == 'from')
                    {
                      address = $("#from").val();
                      lat = $("#from_lat").val();
                      long = $("#from_long").val();

                      if (marker == undefined || marker == null) {
                        marker = $.sMap.features.marker.create({
                          before: function() {
                          },
                          after: function() {
                          },
                          name: 'sourceMarker',
                          popup: {
                            title: {
                              html: 'مبدا',
                              i18n: '',
                            },
                            description: {
                              html: address,
                              i18n: '',
                            },
                            custom: false,
                          },
                          class: 'default',
                          latlng: {lat: lat, lng: long},
                          popupOpen: false,
                          pan: true,
                          draggable: true,
                          on: {
                            click: function (feature) {
                              var lat = feature["latlng"]["lat"];
                              var lon = feature["latlng"]["lng"];
                              reserve(lat, lon,'from');
                            },
                            contextmenu: function (feature) {}
                          }
                        });
                        //ToDo: marker icon = '../assets/img/savar/StartLoc.png';
                      }
                      else {
                        //ToDo: change marker location and zoom to
                      }
                      $('#from').removeAttr('required');
                    }
                  }

                  function reserve(lat, lon,type) {

                    $.ajax({
                      type: 'GET',
                      url: `${uri}/reverse?lat=${lat}&lon=${lon}`,
                      headers: {
                        "x-api-key": key,
                      }
                    }).done(function (data) {

                      if (type == 'from') {
                        $("#from").val(data.postal_address);
                        $("#from_lat").val(lat);
                        $("#from_long").val(lon);
                        $("#from_lat_long").val('(' + lat + ',' + lon + ')');
                      }
                      else {
                        $("#to").val(data.postal_address);
                        $("#to_lat").val(lat);
                        $("#to_long").val(lon);
                        $("#to_lat_long").val('(' + lat + ',' + lon + ')');
                      }
                      estimateFare();
                    });
                  }

                  </script>
                  <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

                  <script>
                  $("#reset12").on('click',function(){
                    $('#dist_fare').text('0');
                    $('#time_fare').text('0');
                    $('#minimum_fare_price').text('0');
                    $('#base_fare_price').text('0');
                    $('#dist_fare_price').text('0');
                    $('#time_fare_price').text('0');
                    $('#total_fare_price').text('0');
                  });

                  $("#eAutoAssign").on('change', function(){
                    if($(this).prop('checked')) {
                      $("#iDriverId").val('');
                      $("#iDriverId").attr('disabled','disabled');
                    }else {
                      $("#iDriverId").removeAttr('disabled');
                    }
                  });
                  var bookId = '<?php echo $iCabBookingId; ?>';
                  if(bookId != "") {
                    if($("#eAutoAssign").prop('checked')) {
                      $("#iDriverId").val('');
                      $("#iDriverId").attr('disabled','disabled');
                    }else {
                      $("#iDriverId").removeAttr('disabled');
                    }
                  }
                  </script>
                  <style>
                  </style>
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
                        <p><b>Flow </b>: Through "Manual Taxi Dispatch" Feature, you can book Rides for customers who ordered for a Ride by calling you. There will be customers who may not have iPhone or Android Phone or may not have app installed on their phone. In this case, they will call Taxi Company (your company) and order Ride which may be needed immediately or after some time later.</p>
                        <p>Here, you will fill their info in the form and dispatch book a taxi ride for him.</p>
                        <p>The Driver will receive info on his App and will pickup the rider at the scheduled time.</p>
                        <p>- If the customer is already registered with us, just enter his phone number and his info will be fetched from the database when "Get Details" button is clicked. Else fill the form.</p>
                        <p>- Once the Trip detail is added, Fare estimate will be calculated based on Pick-Up Location, Drop-Off Location and Car Type.</p>
                        <p>- Admin will need to communicate & confirm with Driver and then select him as Driver so the Ride can be allotted to him. </p>
                        <p>- Clicking on "Book" Button, the Booking detail will be saved and will take Administrator to the "Ride Later Booking" Section. This page will show all such bookings.</p>
                        <p>- The assigned Driver can see the upcoming Bookings from his App under "My Bookings" section.</p>
                        <p>- Driver will have option to "Start Trip" when he reaches the Pickup Location at scheduled time or "Cancel Trip" if he cannot take the ride for some reason. If the Driver clicks on "Cancel Trip", a notification will be sent to Administrator so he can make alternate arrangements.</p>
                        <p>- Upon clicking on "Start Trip", the ride will start in driver's App in regular way.</p>
                        <p><span><img src="images/mobile_app_booking.png"></img></span></p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
