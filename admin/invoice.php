<?
	include_once('../common.php');

	$tbl_name 	= 'trips';
	if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}

	include_once('send_invoice_receipt.php');
	$generalobjAdmin->check_member_login();
	$iTripId = isset($_REQUEST['iTripId'])?$_REQUEST['iTripId']:'';
	$script="Trips";
	$sql = "select trips.*,vVehicleType as eCarType from trips left join vehicle_type on vehicle_type.iVehicleTypeId=trips.iVehicleTypeId where iTripId = '".$iTripId."'";
	$db_trip = $obj->MySQLSelect($sql);

	$sql = "SELECT vt.*,vc.vCategory_EN as vehcat from vehicle_type as vt LEFT JOIN vehicle_category as vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId where iVehicleTypeId = '".$db_trip[0]['iVehicleTypeId']."'";
	$db_vtype = $obj->MySQLSelect($sql);
	#echo '<pre>'; print_R($db_trip); echo '</pre>'; exit;


	/* #echo '<pre>'; print_R($db_trip); echo '</pre>';
		$to_time = @strtotime($db_trip[0]['tStartDate']);
		$from_time = @strtotime($db_trip[0]['tEndDate']);
		$diff=round(abs($to_time - $from_time) / 60,2);
		$db_trip[0]['starttime'] = $generalobj->DateTime($db_trip[0]['tStartDate'],18);
		$db_trip[0]['endtime'] = $generalobj->DateTime($db_trip[0]['tEndDate'],18);
		$db_trip[0]['triptime'] = $diff;
	*/
	$sql = "select * from ratings_user_driver where iTripId = '".$iTripId."'";
	$db_ratings = $obj->MySQLSelect($sql);

	$rating_width = ($db_ratings[0]['vRating1'] * 100) / 5;
	$db_ratings[0]['vRating1'] = '<span style="display: block; width: 65px; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 0;">
		<span style="margin: 0;display: block; width: '.$rating_width.'%; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 -13px;"></span>
		</span>';

	$sql = "select * from register_driver where iDriverId = '".$db_trip[0]['iDriverId']."' LIMIT 0,1";
	$db_driver = $obj->MySQLSelect($sql);

	$sql = "select * from register_user where iUserId = '".$db_trip[0]['iUserId']."' LIMIT 0,1";
	$db_user = $obj->MySQLSelect($sql);

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

    if($driverStartLocation == '' || $driverStartLocation == ',')
        $driverStartLocation = $sourceLat .','.$sourceLon;

    if($driverEndLocation == '' || $driverEndLocation == ',')
        $driverEndLocation = $destLat .','.$destLon;


////////////////////////////////////////////////


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

	 //$currencySymbol = ($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE eDefault='Yes' ")[0]['vSymbol']);

	 //$tripFareResults= $generalobj->getFinalFare($db_trip[0]['iBaseFare'],$db_trip[0]['fPricePerMin'],$totalTimeInMinutes_trip,$db_trip[0]['fPricePerKM'],$db_trip[0]['fDistance'],$db_trip[0]['fCommision'],1,$db_trip[0]['vCurrencyPassenger'],$date1,$date2);


	//$time1=$currencySymbol . ' '. round($tripFareResults['FareOfMinutes']* 1,1);
	//$distance=$currencySymbol . ' '. round($tripFareResults['FareOfDistance']* 1,1);
	// $row[$i]['iFare']=$row[$i]['iFare'] * $priceRatio;
	// $row[$i]['iFare']=$tripFareResults['FinalFare'];
	//$total_fare=$currencySymbol . ' '. round($db_trip[0]['iFare'] * 1,1);
	//$iBaseFare=$currencySymbol . ' '. round($tripFareResults['iBaseFare'] * 1,1);
	//$Commision= $currencySymbol . ' '. round($tripFareResults['FareOfCommision']* 1,1) ;
	/*$refDiscount  = 0;
	$refAmount = $db_trip[0]['iRefAmount'];
	if($refAmount > 0){
		$refamount = $db_trip[0]['iFare'] - $db_trip[0]['iRefAmount'] ;
		$refDiscount= $currencySymbol . ' '. round($refamount ,1) ;
	}
	if($refAmount > 0){
		$refDiscount_temp = round($db_trip[0]['iRefAmount'],1) ;
		$refDiscount =  $currencySymbol . ' '. $refDiscount_temp;
		$refamount = $db_trip[0]['iFare'] - $refDiscount_temp ;
		$total_fare_temp = round($db_trip[0]['iFare'] ,1) ;
		$total_fare =  $currencySymbol . ' '. round($total_fare_temp - $refDiscount_temp,1);

	}*/
	 // $distance=$db_trip[0]['fPricePerKM']*$db_trip[0]['fDistance'];
	 // $time1=$db_trip[0]['fPricePerMin']*$totalTimeInMinutes_trip;
	// $total_fare=$db_trip[0]['iBaseFare']+($time1)+($distance);
	// $commision=($total_fare*$db_trip[0]['fCommision'])/100;
	// $tot = $db_trip[0]['iFare'];

?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
    <script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
<link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet' />
		<title>Admin | Invoice</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="" name="keywords" />
		<meta content="" name="description" />
		<meta content="" name="author" />
		<?php include_once('global_files.php');?>

		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
	</head>
	<!-- END  HEAD-->
	<!-- BEGIN BODY-->
	<body class="padTop53 " >

		<!-- MAIN WRAPPER -->
		<div id="wrap">
			<?php include_once('header.php'); ?>
			<?php include_once('left_menu.php'); ?>

			<!--PAGE CONTENT -->
			<div id="content">
				<div class="inner" id="page_height" style="">
					<div class="row">
						<div class="col-lg-12">
							<h2>Invoice</h2>
							<!-- <a href="mytrip.php">-->
                            <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
							<!-- </a> -->
                            <div style="clear:both;"></div>
						</div>
					</div>
					<hr />
					<?php if ($_REQUEST['success'] ==1) { ?>
						 <div class="alert alert-success paddiing-10">
						 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						 Email send successfully.
						 </div>
				 <?php }?>
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										<b>Your <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'];?> </b> <?php if($db_trip[0]['tStartDate']== "0000-00-00 00:00:00"){ echo "Was Canceled.";}else{echo @date('h:i A',@strtotime($db_trip[0]['tStartDate']));?> on <?php echo @date('d M Y',@strtotime($db_trip[0]['tStartDate']));}?>
										<!-- <div class="pull-right">
											<a href="pdf.php"><i class="icon-download"> PDF </i></a>
										</div> -->
									</div>
									<div class="panel-body rider-invoice-new">
										<div class="row">

											<div class="col-sm-6 rider-invoice-new-left">
												<div id="map" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
												<span class="location-from"><i class="icon-map-marker"></i>
                                                <b><?php echo @date('h:i A',@strtotime($db_trip[0]['tStartDate']));?><p><?php echo $db_trip[0]['tSaddress'];?></p></b></span>
                                                 <?php if($APP_TYPE != 'UberX'){ ?>

												<span class="location-to"><i class="icon-map-marker"></i> <b><?php echo @date('h:i A',@strtotime($db_trip[0]['tEndDate']));?><p><?php echo $db_trip[0]['tDaddress'];?></p></b></span>
												<?php } ?>

												<?php
							              			if($APP_TYPE == 'UberX'){

							              				$class_name = 'col-sm-6';

							              			}else{

							              				$class_name = 'col-sm-4';
							              			}
							              			?>
                                                <div class="rider-invoice-bottom">
													<div class="<?php echo $class_name; ?>">
															<?php echo $langage_lbl_admin['LBL_CAR_TXT_ADMIN'];?> <br /> <b><?php echo $db_vtype[0]['vehcat'].$db_trip[0]['eCarType'];?></b><br/>
													</div>

													 <?php if($APP_TYPE != 'UberX'){ ?>

													<div class="<?php echo $class_name; ?>">
															Distance<br /> <b><?php echo $db_trip[0]['fDistance'];?> km</b> <br/>
													</div>
													 <?php } ?>


													<div class="<?php echo $class_name; ?>">
															<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'];?>  time<br /><b><?echo $diff;?></b>
													</div>
												</div>
											</div>

									<div class="col-sm-6 rider-invoice-new-right">
									<h4 style="text-align:center;">	<?php echo $langage_lbl_admin['LBL_FARE_BREAKDOWN_RIDE_NO_TXT'];?> :<?php echo  $db_trip[0]['vRideNo'];?></h4><hr/>
												<table style="width:100%" cellpadding="5" cellspacing="0" border="0">
												<?
												if($db_trip[0]['eFareType'] != 'Fixed')
												{
													?>
													<tr>
															<td>Basic Fare  </td>
															<td><?php echo $generalobj->trip_currency($db_trip[0]['iBaseFare']);?></td>
													</tr>
													<tr>
															<td>Distance</td>
															<td><?php echo $generalobj->trip_currency($db_trip[0]['fPricePerKM']);?></td>
													</tr>
													<tr>
															<td>Time</td>
															<td><?php echo $generalobj->trip_currency($db_trip[0]['fPricePerMin']);?></td>
													</tr>
													<?
												}
												else
												{
													?>
													<tr>
															<td><?php echo $langage_lbl_admin['LBL_Total_Fare_TXT'];?> </td>
															<td><?php echo $generalobj->trip_currency($db_trip[0]['iFare']);?></td>
													</tr>
													<?
												}
												if($db_trip[0]['fWalletDebit'] > 0)
												{
													?>
													<tr>
														<td><?php echo $langage_lbl_admin['LBL_WALLET_DEBIT_MONEY']; ?> </td>
														<td> - <?php echo $generalobj->trip_currency($db_trip[0]['fWalletDebit']);?> </td>
												</tr>
													<?
												}

												if($db_trip[0]['fDiscount'] > 0)
												{
													?>
													<tr>
														<td><?php echo $langage_lbl_admin['LBL_DISCOUNT']; ?> </td>
														<td> - <?php echo $generalobj->trip_currency($db_trip[0]['fDiscount']);?> </td>
												</tr>
													<?
												}

												if($db_trip[0]['fSurgePriceDiff'] > 0)
												{
													?>
													<tr>
														<td>Surge Price </td>
														<td><?php echo $generalobj->trip_currency($db_trip[0]['fSurgePriceDiff']);?></td>
													</tr>
														<?
													}
													?>
													<tr>
															<td>Commission </td>
															<td>- <?php echo $generalobj->trip_currency($db_trip[0]['fCommision']);?></td>
													</tr>
													<?php

													if($db_trip[0]['fMinFareDiff']!="" && $db_trip[0]['fMinFareDiff'] > 0){
														$minimum_fare=$db_trip[0]['iBaseFare']+$db_trip[0]['fPricePerKM']+$db_trip[0]['fPricePerMin']+$db_trip[0]['fMinFareDiff'];
														?>

													<tr>
															<td><b><?php echo $generalobj->trip_currency($minimum_fare);?></b> <?php echo $langage_lbl_admin['LBL_MINIMUM']; ?> </td>
															<td><?php echo $generalobj->trip_currency($db_trip[0]['fMinFareDiff']);?></td>
													</tr>

													<?php }
													?>
													<tr>
															<td colspan="2"><hr style="margin-bottom:0px"/></td>
													</tr>
													<tr>
															<td><b><?php echo $langage_lbl_admin['LBL_Total_Fare_TXT'];?> (Via <?php echo $db_trip[0]['vTripPaymentMode']?>)</b></td>
															<td><b><?php echo $generalobj->trip_currency($db_trip[0]['iFare']);?></b></td>
													</tr>


													<!--<tr>
														<td>Charged<br /><?php echo $db_trip[0]['vTripPaymentMode']?> </td>
														<td><b><?php echo $generalobj->trip_currency($tot,1,$db_trip[0]['vCurrencyPassenger']);?></b></td>
													</tr>-->
												</table><br>
												<?php if($db_trip[0]['eType'] == 'Deliver'){ ?>

												<h4 style="text-align:center;"><?php echo $langage_lbl_admin['LBL_DELIVERY_DETAILS_TXT_ADMIN'];?></h4><hr/>

												<table style="width:100%" cellpadding="5" cellspacing="0" border="0">
													<tr>
															<td><?php echo $langage_lbl_admin['LBL_RECEIVER_NAME'];?></td>
															<td><?php echo $db_trip[0]['vReceiverName'];?></td>
													</tr>
													<tr>
															<td><?php echo $langage_lbl_admin['LBL_RECEIVER_MOBILE'];?></td>
															<td><?php echo $db_trip[0]['vReceiverMobile'];?></td>
													</tr>
													<tr>
															<td><?php echo $langage_lbl_admin['LBL_PICK_UP_INS'];?></td>
															<td><?php echo $db_trip[0]['tPickUpIns'];?></td>
													</tr>
													<tr>
															<td><?php echo $langage_lbl_admin['LBL_DELIVERY_INS'];?></td>
															<td><?php echo $db_trip[0]['tDeliveryIns'];?></td>
													</tr>
													<tr>
															<td><?php echo $langage_lbl_admin['LBL_PACKAGE_DETAILS'];?></td>
															<td><?php echo $db_trip[0]['tPackageDetails'];?></td>
													</tr>
													<tr>
															<td><?php echo $langage_lbl_admin['LBL_DELIVERY_CONFIRMATION_CODE_TXT'];?></td>
															<td><?php echo $db_trip[0]['vDeliveryConfirmCode'];?></td>
													</tr>
												</table>

												<?php } ?>


												<?php if($APP_TYPE == 'UberX' && $db_trip[0]['vBeforeImage'] != ''){

												 ?>
												<h4 style="text-align:center;"><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT'];?></h4><hr/>

												<div class="invoice-right-bottom-img">
													<div class="col-sm-6">
														<h3>
														<?php
														$img_path = $tconfig["tsite_upload_trip_images"];
														echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN'];?></h3>
														 <b><a href="<?php echo  $img_path .$db_trip[0]['vBeforeImage']; ?>" target="_blank" ><img src = "<?php echo  $img_path.$db_trip[0]['vBeforeImage'] ?>" style="width:200px;" alt ="Before Images"/></b></a>
													</div>
													<div class="col-sm-6">
														<h3><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN'];?></h3>
														 <b><a href="<?php echo  $img_path .$db_trip[0]['vBeforeImage']; ?>" target="_blank" ><img src = "<?php echo  $img_path.$db_trip[0]['vAfterImage']; ?>" style="width:200px;" alt ="After Images"/></b></a>
												</div>

												<?php } ?>

											</div>
										</div>

										 <div class="clear"></div>
										<div class="row invoice-email-but">
											<!--<div class="col-sm-12">
													<!--<span class="invoice-img">
													<?php if($db_driver[0]['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $db_driver[0]['iDriverId'] . '/2_' . $db_driver[0]['vImage'])){?>
													<img src = "<?php echo  $tconfig["tsite_upload_images_driver"]. '/' . $db_driver[0]['iDriverId'] . '/2_' .$db_driver[0]['vImage'] ?>" style="height:150px;"/>
													<?php }else{ ?>
													<img src="assets/img/profile-user-img.png" alt="">
													<?php } ?></span>
													<span class="invoice-name">You ride with <?php echo  $db_driver[0]['vName']?></span>
													<span class="invoice-ride">RATE YOUR RIDE</span>
												    <span class="invoice-rating"><?php echo $db_ratings[0]['vRating1'];?></span>-->
														<span>

														<!-- <a href="invoice_print.php?action=mail&iTripId=<?php echo  $db_trip[0][iTripId]?>" onClick="return confirm('Are you sure you want to send email to '+<?echo $db_user[0]['vName']?>);"><button class="btn btn-primary ">E-mail</button></a>-->

														<a href="../send_invoice_receipt.php?action_from=mail&iTripId=<?php echo  $db_trip[0][iTripId]?>"><button class="btn btn-primary ">E-mail</button></a>
													</span>
											</div>
											<!--<div class="col-sm-4">
												<span style="margin:30px 0 0 20px; float:right;">
												<button class="btn btn-default">Print</button>
												<button class="btn btn-primary ">E-mail</button>
											</span>
										</div>-->
									</div>
									</div>
								</div>
							</div>
						</div>
                        <div class="clear"></div>
					</div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>

		<!--END MAIN WRAPPER -->

		<?php include_once('footer.php');?>
			<?php include_once('header_map_ir.php'); ?>
    <script src="./dist/leaflet-heat.js"></script>
    <script src="http://leaflet.github.io/Leaflet.markercluster/example/realworld.10000.js"></script>






    <script type="text/javascript">

</script>








    <script>

      //function initMap() {

          //var directionsDisplay = new google.maps.DirectionsRenderer();
          //var directionsService = new google.maps.DirectionsService();




          //var driverSource = new google.maps.LatLng( <?php echo  $driverStartLocation ?>);
          //var driverDest   = new google.maps.LatLng( <?php echo  $driverEndLocation ?>  );

        /*var map = new google.maps.Map(document.getElementById('map-canvas'), {
          zoom: 13,
          center: source
        });

        //directionsDisplay.setMap(map);

          var request = {
            origin: source,
            destination: dest,
            travelMode: 'DRIVING',
            //waypoints[]: DirectionsWaypoint,
            //optimizeWaypoints: Boolean,

          };

          directionsService.route(request, function(result, status) {
            if (status == 'OK') {
              directionsDisplay.setDirections(result);
            }
          });






        var pointPin = '<?php echo $tconfig["tsite_url"] ?>/assets/img/point.png';


*/



var heat;
jQuery( document ).ready( function($) {
    var map = $.sMap({
     element: '#map',
     presets: {
       latlng: {
                <?php
            echo 'lat:' . $sourceLat . ', lng: ' . $sourceLon . '';

?>
},
<?
        if($mapZoom != '') {
         echo 'zoom:' . $mapZoom . ',';

       }
       else {
  echo 'zoom: 13,';

       } ?>
		 }
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



var redIcon =  L.icon({iconUrl: 'http://maps.gstatic.com/mapfiles/ms2/micons/red-pushpin.png',
iconSize:     [35, 35], // size of the icon
shadowSize:   [35, 35], // size of the shadow
iconAnchor:   [35, 35], // point of the icon which will correspond to marker's location
shadowAnchor: [35, 35],  // the same for the shadow
popupAnchor:  [-35, -35]// point from which the popup should open relative to the iconAnchor
}),greenIcon =  L.icon({iconUrl: 'http://maps.gstatic.com/mapfiles/ms2/micons/grn-pushpin.png',
iconSize:     [35, 35], // size of the icon
shadowSize:   [35, 35], // size of the shadow
iconAnchor:   [35, 35], // point of the icon which will correspond to marker's location
shadowAnchor: [35, 35],  // the same for the shadow
popupAnchor:  [-35, -35] // point from which the popup should open relative to the iconAnchor
});


L.marker([<?php echo $driverStartLocation ?>], {icon: greenIcon}).bindPopup("Start").addTo(map);
L.marker([<?php echo $driverEndLocation ?>], {icon: redIcon}).bindPopup("End").addTo(map);

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
          ];

























  var latlngs = [
      [45.51, -122.68],
      [37.77, -122.43],
      [34.04, -118.2]
  ];
  var polyline = L.polyline(flightPlanCoordinates, {color: 'red'}).addTo(map);
  // zoom the map to the polyline
  map.fitBounds(polyline.getBounds());

          <?php
          }
          ?>});
      //}
    </script>


        <!--<script  async defer src="https://maps.googleapis.com/maps/api/js?v=3.exp&callback=initMap&sensor=false&libraries=places&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>"></script>-->

		<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>

		<script>
			h = window.innerHeight;
			$("#page_height").css('min-height', Math.round( h - 99)+'px');


		</script>
	</body>
	<!-- END BODY-->
</html>
