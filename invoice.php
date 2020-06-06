<?
	include_once('common.php');
	require_once(TPATH_CLASS .'savar/jalali_date.php');
	$tbl_name 	= 'trips';
	$script="Trips";
	$generalobj->check_member_login();

	$CEDAR_API_KEY=$generalobj->getConfigurations("configurations","CEDAR_WEB");

	$_REQUEST['iTripId'] = base64_decode(base64_decode(trim($_REQUEST['iTripId'])));

	$iTripId = isset($_REQUEST['iTripId'])?$_REQUEST['iTripId']:'';
    //print_r($_SESSION); exit;
	//	exit;
	if($_SESSION['sess_user']== "company")
	{
		//			print_r($_SESSION);
		//			exit;
	}
	if($_SESSION['sess_user']== "driver")
	{
		$sess_iUserId = $_SESSION['sess_iUserId'];
		$sdsql = " AND iDriverId = '".$sess_iUserId."' ";
	}

	$sql = "select trips.*,vVehicleType as eCarType from trips left join vehicle_type on vehicle_type.iVehicleTypeId=trips.iVehicleTypeId where iTripId = '".$iTripId."'" . $sdsql;
	$db_trip = $obj->MySQLSelect($sql);
    #print_r($db_trip);die();
	/* if(count($db_trip) > 0)
	{

	}
	else
	{

	} */


	if($_SESSION['sess_user']== "driver")
	{
		$sql = "SELECT * FROM register_".$_SESSION['sess_user']." WHERE iDriverId='".$_SESSION['sess_iUserId']."'";
		$db_booking = $obj->MySQLSelect($sql);

		$sql = "SELECT Ratio, vName, vSymbol FROM currency WHERE vName='".$db_booking[0]['vCurrencyDriver']."'";
		$db_curr_ratio = $obj->MySQLSelect($sql);
	}
	else
	{
		$sql = "SELECT * FROM register_".$_SESSION['sess_user']." WHERE iUserId='".$_SESSION['sess_iUserId']."'";
		$db_booking = $obj->MySQLSelect($sql);

		$sql = "SELECT Ratio, vName, vSymbol FROM currency WHERE vName='".$db_booking[0]['vCurrencyPassenger']."'";
		$db_curr_ratio = $obj->MySQLSelect($sql);
	}
	$tripcursymbol=$db_curr_ratio[0]['vSymbol'];
	$tripcur=$db_curr_ratio[0]['Ratio'];
	$tripcurname=$db_curr_ratio[0]['vName'];



	$sql = "SELECT vt.*,vc.vCategory_EN as vehcat from vehicle_type as vt LEFT JOIN vehicle_category as vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId where iVehicleTypeId = '".$db_trip[0]['iVehicleTypeId']."'";
	$db_vtype = $obj->MySQLSelect($sql);

	 if($db_vtype[0]['vehcat'] != ""){
		   $car = $db_vtype[0]['vehcat'].' - '.$db_vtype[0]['vVehicleType'];
    }else{
       $car = $db_vtype[0]['vVehicleType_'.$_SESSION['sess_lang']];
    }

	$sql = "select * from ratings_user_driver where iTripId = '".$iTripId."'";
	$db_ratings = $obj->MySQLSelect($sql);

    $rate['DriverForPassenger'] = '';
    $rate['PassengerForDriver'] = '';

    foreach($db_ratings as $rating)
    {
        $rating_width = ($rating['vRating1'] * 100) / 5;
        $rateHTML = '<span style="display: block; width: 65px; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 0;">
        <span style="margin: 0;float:left;display: block; width: '.$rating_width.'%; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 -13px;"></span>
        </span>';

        if($rating['eUserType'] == 'Driver')
        {
            $rate['DriverForPassenger']['html'] = $rateHTML;
            $rate['DriverForPassenger']['message'] = $rating['vMessage'];
        }
        else
        {
            $rate['PassengerForDriver']['html'] = $rateHTML;
            $rate['PassengerForDriver']['message'] = $rating['vMessage'];
        }
    }

	$sql = "select * from register_driver where iDriverId = '".$db_trip[0]['iDriverId']."' LIMIT 0,1";
	$db_driver = $obj->MySQLSelect($sql);


	$sql = "select *,vImgName as vImage from register_user where iUserId = '".$db_trip[0]['iUserId']."' LIMIT 0,1";
	$db_user_rider = $obj->MySQLSelect($sql);

	$ts1 = strtotime($db_trip[0]['tStartDate']);
	$ts2 = strtotime($db_trip[0]['tEndDate']);
	$diff = abs($ts2 - $ts1);
	$years = floor($diff / (365*60*60*24)); $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
	$minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
	$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));
	$diff = $hours.':'.$minuts.':'.$seconds;

	$date1 = $db_trip[0]['tStartDate'];
	$date2 = $db_trip[0]['tEndDate'];
	$totalTimeInMinutes_trip=@round(abs(strtotime($date2) - strtotime($date1)) / 60,2);
	$distance=$db_trip[0]['fPricePerKM']*$db_trip[0]['fDistance'];
	$time=$db_trip[0]['fPricePerMin']*$totalTimeInMinutes_trip;
	$total_fare=$db_trip[0]['iBaseFare']+($time)+($distance);
	$commision=($total_fare*$db_trip[0]['fCommision'])/100;
	$tot = $total_fare + ($commision);


    $sourceLat = $db_trip[0]['tStartLat'];
    $sourceLon = $db_trip[0]['tStartLong'];

    $destLat = $db_trip[0]['tEndLat'];
    $destLon = $db_trip[0]['tEndLong'];

    if($destLat == '')
    {
        $destLat = $sourceLat;
        $destLon = $sourceLon;
    }

    // added by seyyed amir
    $driverStartLocation = $db_trip[0]['vDriverStartLocation'];
    $driverEndLocation   = $db_trip[0]['vDriverEndLocation'];
    $driverAcceptLocation= $db_trip[0]['vDriverAcceptLocation'];

    if($driverStartLocation == '' || $driverStartLocation == ',')
        $driverStartLocation = $sourceLat .','.$sourceLon;

    if($driverEndLocation == '' || $driverEndLocation == ',')
        $driverEndLocation = $destLat .','.$destLon;

#print_r($_SESSION);die();

// check for owner
if($_SESSION['sess_iCompanyId'] != $db_driver[0]['iCompanyId'])
    $db_trip = array();

?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
  <script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
<link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet' />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $SITE_NAME?> | Invoice</title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>"></script>

    <!-- End: Default Top Script and css-->
</head>
<body>
    <!-- home page -->
    <div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- contact page-->
        <div class="page-contant">
    		<div class="page-contant-inner page-trip-detail">
          		<h2 class="header-page trip-detail"><?php echo $langage_lbl['LBL_Invoice']; ?>
          			<a href="company_trip.php" onClick="company_trip.php"><img src="assets/img/arrow-white.png" alt="" /><?php echo $langage_lbl['LBL-back_to_listing']; ?></a>
					<?php if(count($db_trip) > 0){?>

                    <?php
                        if($db_trip[0]['tStartDate'] != '0000-00-00 00:00:00')
                            $tripDate = $db_trip[0]['tStartDate'];
                        else
                            $tripDate = $db_trip[0]['tRequestDate'];
                        ?>

            		<p><?php echo $langage_lbl['LBL_RATING_PAGE_HEADER_TXT']; ?> <strong>
                        <?php echo @jdate('h:i A',@strtotime($tripDate));?> در تاریخ
                        <?php echo @jdate('d M Y',@strtotime($tripDate));?></strong></p>
					<?php }?>
          		</h2>
          		<!-- trips detail page -->
				<?php
				if(count($db_trip) > 0)
				{
				?>
          		<div class="trip-detail-page">
                <div class="trip-detail-page-inner">
            		<div class="col-md-6" style="background: #eeeeee;">
              			<div class="driver-info">
              				<div class="driver-img">
              					<span>
              					<?php if($db_user_rider[0]['vImage'] != '' && file_exists($tconfig["tsite_upload_images_passenger_path"]. '/' . $db_user_rider[0]['iUserId'] . '/2_' . $db_user_rider[0]['vImage'])){
          						?>
              						<img src = "<?php echo  $tconfig["tsite_upload_images_passenger"]. '/' . $db_user_rider[0]['iUserId'] . '/2_' .$db_user_rider[0]['vImage'] ?>" style="height:150px;"/>
          						<?php }else{ ?>
									<img src="assets/img/profile-user-img.png" alt="">
								<?php } ?>
              				</div>
                			<h3><?php echo $langage_lbl['LBL_You_ride_with']; ?> <?php echo  $db_user_rider[0]['vName'] . ' ' .$db_user_rider[0]['vLastName']?>
                            <?php

                            if( $_SESSION['sess_user'] == "company")
                            {
                            ?>
                                <br><?php echo $db_user_rider[0]['vPhone'] ?>
                            <?php } ?>
                                </h3>

              			</div>
          				<div class="fare-breakdown">
                			<div class="fare-breakdown-inner">
                  				<h3><?php echo $langage_lbl['LBL_FARE_BREAKDOWN_RIDE_NO_TXT'];?>. <b><?php echo  $db_trip[0]['vRideNo']; ?></b></h3>
                  				<ul>
									<?
									if($db_trip[0]['eFareType'] != 'Fixed')
									{
										?>
										<li><strong><?php echo $langage_lbl['LBL_Basic_Fare']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['iBaseFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<li><strong><?php echo $langage_lbl['LBL_DISTANCE_TXT']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['fPricePerKM'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<li><strong><?php echo $langage_lbl['LBL_TIME_TXT']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['fPricePerMin'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
									<?php }
									else
									{
										?>
										<li><strong><?php echo $langage_lbl['LBL_Total_Fare']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['iFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<?
									}
										if($db_trip[0]['fWalletDebit'] > 0)
										{
											?>
											<li><strong><?php echo $langage_lbl['LBL_WALLET_DEBIT_MONEY']; ?></strong><b> - <?php echo $generalobj->trip_currency($db_trip[0]['fWalletDebit'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
											<?
										}
										if($db_trip[0]['fDiscount'] > 0)
										{
											?>
											<li><strong><?php echo $langage_lbl['LBL_DISCOUNT']; ?></strong><b>
												  - <?php echo $generalobj->trip_currency($db_trip[0]['fDiscount'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
											<?
										}
										if($db_trip[0]['fSurgePriceDiff'] > 0)
										{
											?>
											<li><strong><?php echo $langage_lbl['LBL_SURGE_MONEY']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['fSurgePriceDiff'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
											<?
										}
									?>
                    <li><strong><?php echo $langage_lbl['LBL_Commision']; ?></strong><b>- <?php echo $generalobj->trip_currency($db_trip[0]['fCommision'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
                     <?php if($db_trip[0]['fMinFareDiff']!="" && $db_trip[0]['fMinFareDiff'] > 0){
                   // $minimum_fare=round($db_trip[0]['fMinFareDiff'] * $db_trip[0]['fRatioPassenger'],1);
				   $minimum_fare=$db_trip[0]['iBaseFare']+$db_trip[0]['fPricePerKM']+$db_trip[0]['fPricePerMin']+$db_trip[0]['fMinFareDiff'];
                    ?>

                   <li><strong><?php echo $generalobj->trip_currency($minimum_fare,$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b> <?php echo $langage_lbl['LBL_MINIMUM']; ?>
                      </strong><b>
                      <?php echo $generalobj->trip_currency($db_trip[0]['fMinFareDiff'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>


                          <?php }
                          ?>
                  				</ul>
                  				<span>
								<?php $paymentMode = ($db_trip[0]['vTripPaymentMode'] == 'Cash')? $langage_lbl['LBL_VIA_CASH_TXT']: $langage_lbl['LBL_VIA_CARD_TXT']?>
                  					<h4><?php echo $langage_lbl['LBL_Total_Fare']; ?> (<?php echo $paymentMode;?>)</h4>
                  					<em><?php echo $generalobj->trip_currency($db_trip[0]['iFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></em>
              					</span>
                  				<div style="clear:both;"></div>
                          <?php if($db_trip[0]['eType'] == 'Deliver'){ ?>
                          <br>
                        <h3><?php echo $langage_lbl['LBL_DELIVERY_DETAILS']?></h3><hr/>

                        <ul style="border-bottom:none">
                            <li><strong><?php echo $langage_lbl['LBL_RECEIVER_NAME']?> </strong><b><?php echo $db_trip[0]['vReceiverName'];?></b></li>
                            <li><strong><?php echo $langage_lbl['LBL_RECEIVER_MOBILE']?></strong><b><?php echo $db_trip[0]['vReceiverMobile'];?></b></li>
                            <li><strong><?php echo $langage_lbl['LBL_PICKUP_INSTRUCTION']?> </strong><b><?php echo $db_trip[0]['tPickUpIns'];?></b></li>
                            <li><strong><?php echo $langage_lbl['LBL_DELIVERY_INSTRUCTION']?></strong><b><?php echo $db_trip[0]['tDeliveryIns'];?></b></li>
                            <li><strong><?php echo $langage_lbl['LBL_PACKAGE_DETAILS']?></strong><b><?php echo $db_trip[0]['tPackageDetails'];?></b></li>
                            <li><strong><?php echo $langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT']?></strong><b><?php echo $db_trip[0]['vDeliveryConfirmCode'];?></b></li>

                        </ul>

                        <?php } ?>

                        <div style="clear:both;"></div>
                        <?php if($APP_TYPE == 'UberX' && $db_trip[0]['vBeforeImage'] != ''){

                         ?>
                         <h3><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT'];?></b></h3>

                        <div class="invoice-right-bottom-img">
                          <div class="col-sm-6">
                            <h4>
                            <?php
                            $img_path = $tconfig["tsite_upload_trip_images"];
                            echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN'];?></h4>
                             <b><a href="<?php echo  $img_path .$db_trip[0]['vBeforeImage'] ?>" target="_blank" ><img src = "<?php echo  $img_path . $db_trip[0]['vBeforeImage'] ?>" style="width:200px;" alt ="Before Images"/></b></a>
                          </div>
                          <div class="col-sm-6">
                            <h4><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN'];?></h4>
                             <b><a href="<?php echo  $img_path .$db_trip[0]['vBeforeImage'] ?>" target="_blank" ><img src = "<?php echo  $img_path. $db_trip[0]['vAfterImage'] ?>" style="width:200px;" alt ="After Images"/></b></a>
                          </div>
                        </div>

                        <?php }

						 ?>

                			</div>
              			</div>
            		</div>

                <div class="col-md-6" style="1px solid #dfdfdf">
              			<div class="trip-detail-map"><div id="map" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div></div>
              			<div class="map-address">
                			<ul>
                  				<li>
                  					<b><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i></b>
              						<span>
                    					<h3><?php echo @jdate('h:i A',@strtotime($db_trip[0]['tStartDate']));?></h3>
                						<?php echo $db_trip[0]['tSaddress'];?>
            						</span>
        						</li>
                     <?php if($APP_TYPE != 'UberX'){ ?>
              					<li>
              						<b><i aria-hidden="true" class="fa fa-map-marker fa-22 red-location"></i></b>
          							<span>
                    					<h3><?php echo @jdate('h:i A',@strtotime($db_trip[0]['tEndDate']));?></h3>
                    					<?php echo $db_trip[0]['tDaddress'];?>
                    				</span>
                				</li>
                        <?php } ?>
                			</ul>
                            <h3>نام راننده: <?php echo  $db_driver[0]['vName'].' '.$db_driver[0]['vLastName'] ?></h3>
                            <hr />

                            <?php if($_SESSION['sess_user']== "company") : ?>
                            <div><h4>امتیاز مسافر به راننده </h4><?php echo  $rate['PassengerForDriver']['html'] ?>
                                <br><?php echo  $rate['PassengerForDriver']['message'] ?></div>
                            <hr />
                            <div><h4>امتیاز راننده به مسافر </h4><?php echo  $rate['DriverForPassenger']['html'] ?>
                                <br><?php echo  $rate['DriverForPassenger']['message'] ?></div>
                            <?php endif; ?>
              			</div>
                    <?php
                    if($APP_TYPE == 'UberX'){

                      $class_name = 'location-time location-time-second';

                    }else{

                      $class_name = 'location-time';
                    }
                    ?>
              			<div class="<?php echo $class_name?>">
	            			<ul>
	                  			<li>
	                    			<h3><?php echo $langage_lbl['LBL_INVOICE_Car']; ?></h3>
	                    			<?php echo $db_vtype[0]['vehcat'].$car;?>
	            				</li>
                      <?php if($APP_TYPE != 'UberX'){ ?>
	                  			<li>
	                    			<h3><?php echo $langage_lbl['LBL_DISTANCE_TXT']; ?></h3>
	                    			<?php echo $db_trip[0]['fDistance'];?> KM
	                			</li>
                          <?php } ?>
	                  			<li>
	                    			<h3><?php echo $langage_lbl['LBL_Trip_time']; ?></h3>
	                    			<?echo $diff;?>
	                			</li>
	                			<li>
	                    			<h3><?php echo $langage_lbl['LBL_WAITING_TIME']; ?></h3>
	                    			<?php echo $db_trip[0]['fWaitingTime'];?> Min
	                			</li>
	                		</ul>
              			</div>
            		</div>
            		 </div>
            		<!-- -->
        		 	<?php //if(SITE_TYPE=="Demo"){?>
            		<!-- <div class="record-feature">
            			<span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
              			This feature will be enabled in the main product we will provide you.</span>
              		</div> -->
              		<?php //}?>
        		<!-- -->
          		</div>
				<?php
				} else {
				?>
				<div class="trip-detail-page">
                <div class="trip-detail-page-inner">
					<?php echo $langage_lbl['LBL_INVOCE_WARNING']?>
				</div>
				</div>
				<?php } ?>
				<!-------------------------------------------------------------------------------------->
        	</div>
  		</div>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
    <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    	<?php include_once('./admin/header_map_ir.php'); ?>


<script>
jQuery( document ).ready( function($) {
  /* Do not drag on mobile. */
  var is_touch_device = 'ontouchstart' in document.documentElement;
  var newName; var newAddr; var newOnlineSt; var newLat; var newLong; var newImg; var map;
  var bounds = [];
  var markers = [];
  var latlng;
  var type = '<?php echo $_REQUEST['type']; ?>';
   map = $.sMap({
    element: '#map',
    presets: {
      latlng: {
        <?php if($sourceLat != '') {
        echo "lat: $sourceLat , lng:  $sourceLon ,";
      }
      else {
        echo "lat: $sourceLat , lng:  $sourceLon ,";
        //echo 'center: {lat: 51.505, lng: -0.09},';
      }?>
        lat: 35.757448286487595,
        lng: 51.40876293182373,
      },
      zoom: 13,
    },
  });

  $.sMap.layers.static.build({
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


    var LeafIcon = L.Icon.extend({
      options: {

      }
    });



    var redIcon = L.icon({iconUrl: 'assets/img/savar/EndPin.png',
    iconSize:     [35, 35], // size of the icon
    shadowSize:   [20, 20], // size of the shadow
    iconAnchor:   [20, 20], // point of the icon which will correspond to marker's location
    shadowAnchor: [20, 20],  // the same for the shadow
    popupAnchor:  [-20, -20] }),
    greenIcon = L.icon({iconUrl: 'assets/img/savar/StartPin.png',
    iconSize:     [35, 35], // size of the icon
    shadowSize:   [20, 20], // size of the shadow
    iconAnchor:   [20, 20], // point of the icon which will correspond to marker's location
    shadowAnchor: [20, 20],  // the same for the shadow
    popupAnchor:  [-20, -20] });
    L.marker([<?php echo  $driverStartLocation ?>], {icon: greenIcon}).addTo(map);
    L.marker([<?php echo  $driverEndLocation ?>], {icon: redIcon}).addTo(map);
//  var polyline = L.polyline(flightPlanCoordinates, {color: 'red'}).addTo(map);


  <?php if($driverAcceptLocation != '') : ?>
  var acgreenIcon = L.icon({iconUrl: 'assets/img/savar/AcceptPin.png',
  iconSize:     [35, 35], // size of the icon
  shadowSize:   [20, 20], // size of the shadow
  iconAnchor:   [20, 20], // point of the icon which will correspond to marker's location
  shadowAnchor: [20, 20],  // the same for the shadow
  popupAnchor:  [-20, -20] });
L.marker([<?php echo  $driverAcceptLocation ?>], {icon: acgreenIcon}).addTo(map);

  <?php endif; ?>

  <?php
    global $obj;

    $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$iTripId'";

    #die($sql);
    $Data_tripsLocations = $obj->MySQLSelect($sql);

    if(count($Data_tripsLocations)>0)
    {
        $trip_path_latitudes=$Data_tripsLocations[0]['tPlatitudes'];
        $trip_path_longitudes=$Data_tripsLocations[0]['tPlongitudes'];

        $trip_path_latitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_latitudes);
        $trip_path_longitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_longitudes);

        $TripPathLatitudes=explode(",",$trip_path_latitudes);

        $TripPathLongitudes=explode(",",$trip_path_longitudes);

        $pointsLen = count($TripPathLatitudes) ;

        ?>



  var flightPlanCoordinates = [
    <?php

      list($driverDesLat,$driverDestLon) = explode(',',$driverEndLocation,2);

    for($i=0;$i < $pointsLen -1;$i++) {
        $tempLat_current = $TripPathLatitudes[$i];
        $tempLon_current = $TripPathLongitudes[$i];

        if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0') {
            continue;
        }

        echo "{lat:$tempLat_current,lng:$tempLon_current},";
        if($i % 5 == 0)
            echo "\r\n\t";
    }
    ?>
      <?php echo  "\r\n{lat:$driverDesLat,lng:$driverDestLon}"; // add destination point for end ?>
];




$.sMap.features();

$.sMap.features.polyline.create({
	name: 'test-polyline',
	popup: {
		title: {
			html: '',
			i18n: '',
		},
		description: {
			html: 'مسیر طی شده',
			i18n: '',
		},
	},
	coordinates:flightPlanCoordinates,
	popupOpen: true,
});


//var polyline = L.polyline(flightPlanCoordinates, {color: 'red'}).addTo(map);
//window.alert("sometext");
      function initMap() {

          var directionsDisplay = new google.maps.DirectionsRenderer();
          var directionsService = new google.maps.DirectionsService();


        var source = {lat: <?php echo  $sourceLat ?>, lng: <?php echo  $sourceLon ?>};
        var dest = {lat: <?php echo  $destLat ?>, lng: <?php echo  $destLon ?>};

          var driverSource = new google.maps.LatLng( <?php echo  $driverStartLocation ?>);
          var driverDest   = new google.maps.LatLng( <?php echo  $driverEndLocation ?>  );

        var map = new google.maps.Map(document.getElementById('map-canvas'), {
          zoom: 13,
          center: source
        });

          window.map = map;

        directionsDisplay.setMap(map);

          var request = {
            origin: source,
            destination: dest,
            travelMode: 'DRIVING'
          };

          directionsService.route(request, function(result, status) {
            if (status == 'OK') {
              directionsDisplay.setDirections(result);
            }
          });


          var image = {
            url: 'assets/img/savar/StartPin.png',
            //size: new google.maps.Size(80, 80),
            // The origin for this image is (0, 0).
            //origin: new google.maps.Point(0, 0),
            // The anchor for this image is the base of the flagpole at (0, 32).
            anchor: new google.maps.Point(25,25)

          };


        var markerSource = new google.maps.Marker({
          position: driverSource,
          map: map,
          icon: image,
          opacity: 0.7
        });

          image['url'] = 'assets/img/savar/EndPin.png';
          var markerEnd = new google.maps.Marker({
          position: driverDest,
          map: map,
          icon: image,
          opacity: 0.7
        });

          <?php if($driverAcceptLocation != '') : ?>
          image['url'] = 'assets/img/savar/AcceptPin.png';
          var driverAccept   = new google.maps.LatLng( <?php echo  $driverAcceptLocation ?>  );
        var markerAccept = new google.maps.Marker({
          position: driverAccept,
          map: map,
          icon: image,
          opacity: 0.7
        });

          <?php endif; ?>

        var pointPin = '<?php echo $tconfig["tsite_url"] ?>/assets/img/point.png';






            var flightPlanCoordinates = [
              <?php

                list($driverDesLat,$driverDestLon) = explode(',',$driverEndLocation,2);

              for($i=0;$i < $pointsLen -1;$i++) {
                  $tempLat_current = $TripPathLatitudes[$i];
                  $tempLon_current = $TripPathLongitudes[$i];

                  if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0') {
                      continue;
                  }

                  echo "{lat:$tempLat_current,lng:$tempLon_current},";
                  if($i % 5 == 0)
                      echo "\r\n\t";
              }
              ?>
                <?php echo  "\r\n{lat:$driverDesLat,lng:$driverDestLon}"; // add destination point for end ?>
          ];
          var flightPath = new google.maps.Polyline({
              path: flightPlanCoordinates,
              geodesic: true,
              strokeColor: '#FF0000',
              strokeOpacity: 0.7,
              strokeWeight: 3
          });

          flightPath.setMap(map);

          <?php
          }
          ?>
      }
});
    </script>


        <script  async defer src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&callback=initMap&sensor=false&libraries=places&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>"></script>

    <!-- End: Footer Script -->
</body>
</html>
