<?php
$ISDEBUG;
	include_once('../common.php');
#echo '<pre>';print_r($_SERVER);die();
	include_once('savar_check_permission.php');
	if(checkPermission('AREA') == false)
		die('you dont`t have permission...');

		//exit;

	if (!isset($generalobjAdmin)) {
		require_once(TPATH_CLASS . "class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	$script = "Area";

	$error ='';

	$aId='';
	$sAreaName='';
	$sAreaNamePersian='';
	$sPriority='';
	$sFeatureCollection='';
	$sActive = '';
	$mapCenter = '';
	$mapZoom = '';

	$action = 'add';


	if(isset($_GET['edit']) && isset($_GET['id']))
	{
		$aId = $_GET['id'];
		$res = $obj->MySQLSelect("SELECT * FROM savar_area WHERE  `aId` = '$aId'");

		if(count($res) > 0)
		{
			$aId = $res[0]['aId'];
			$sAreaName = $res[0]['sAreaName'];
			$sAreaNamePersian = $res[0]['sAreaNamePersian'];
			$sPriority = $res[0]['sPriority'];
			$sFeatureCollection = $res[0]['sFeatureCollection'];
			$sActive = $res[0]['sActive'];
			$mapCenter = $res[0]['mapCenter'];
			$mapZoom = $res[0]['mapZoom'];


			$action = 'edit';
		}
	}

	if(isset($_POST['action']))
	{

		#echo "<pre>";print_r($_POST);die();
		// $sAreaName			= GetPost('sAreaName');
		// $sAreaNamePersian	= GetPost('sAreaNamePersian');
		// $sSpecialArea		= GetPost('sSpecialArea');
		// $sPriority			= GetPost('sPriority');
		// $sFeatureCollection = stripcslashes(GetPost('sFeatureCollection'));
		// $aId			    = GetPost('aId');
		// $mapCenter			= stripcslashes(GetPost('mapCenter'));
		// $mapZoom			= GetPost('mapZoom');
		// $sActive 			= GetPost('sActive');

		$sAreaName			= "Esfahan";
		$sAreaNamePersian	= "اصفهان";
		$sSpecialArea		= "No";
		$sPriority			= "5";
		$sFeatureCollection = stripcslashes(file_get_contents("area_new_str.txt"));
		$aId			    = GetPost('aId');
		$mapCenter			= "{\"lat\":32.61343575281241,\"lng\":51.68664923589722}";
		$mapZoom			= 12;
		$sActive 			= "Yes";


		$fCollectionArray = json_decode($sFeatureCollection,true);

		if($sAreaName == '' || $sAreaNamePersian == '')
			$error .= 'لطفا نام منطقه را بدرستی وارد نمایید<br>';


		else if($_POST['action'] == 'add' && count($obj->MySQLSelect("SELECT aId FROM savar_area WHERE  `sAreaName` = '$sAreaName'")) > 0)
			$error .= 'نام انتخابی تکراریست<br>';

		if($sFeatureCollection == '')
			$error .= 'لطفا یک منطقه را انتخاب نمایید<br>';
		else if(isset($fCollectionArray['features']) == false || count($fCollectionArray['features']) == 0)
			$error .= 'خطا در انتخاب منطقه<br>';
		else if(isset($fCollectionArray['features'][0]['geometry']['coordinates'][0]) == false
				|| count($fCollectionArray['features'][0]['geometry']['coordinates'][0]) == 0)
			$error .= 'خطا در تعداد مناطق<br>';


		if($sPriority == '')
			$sPriority = 5;



		//$sFeatureCollection = str_replace('\r\n',"\r\n",$sFeatureCollection);
		//echo $sFeatureCollection;


		if($error == '')
		{
			$polyText = 'POLYGON((';

			$points = $fCollectionArray['features'][0]['geometry']['coordinates'][0];


			$pointLen = count($points );
			// ezafe kardane noghte ebteda be enteha
			if($points[0][0] != $points[$pointLen - 1][0] && $points[0][1] != $points[$pointLen - 1][1] )
			{
				$points[] = $points[0];
				$pointLen++;
			}

			for( $i=0 ; $i<$pointLen ; $i++ )
			{
				$polyText .= $points[$i][0] . ' ' . $points[$i][1] . ',';
			}



			if (substr($polyText, -1, 1) == ',')
			{
			  $polyText = substr($polyText, 0, -1);
			}

			$polyText .= '))';

			if($_POST['action'] == 'add')
			{
				$sql = "INSERT INTO `savar_area` (`sAreaName`, `sAreaNamePersian`, `sSpecialArea`, `sPriority`, `sPolygonArea`, `sFeatureCollection`, `sActive`, `mapCenter`, `mapZoom`) VALUES ('$sAreaName', '$sAreaNamePersian', '$sSpecialArea', '$sPriority', PolyFromText('$polyText'), '$sFeatureCollection', '$sActive', '$mapCenter', '$mapZoom');";
				$res = $obj->sql_query($sql);

				if($res == false)
				{
					$error .= 'خطایی در ثبت اطلاعات به وجود آمد<br>';
				}
				else
				{
					$res = $obj->MySQLSelect("SELECT aId FROM savar_area WHERE  `sAreaName` = '$sAreaName'");
					if(count($res) > 0 )
					{
						$id = $res[0]['aId'];
						header("Location: " . $_SERVER['SCRIPT_NAME'] ."?edit&addmsg&id=" . $id);
					}
				}
			}
			else if($_POST['action'] == 'edit')
			{

				$sql = "UPDATE `savar_area` SET `sAreaName` = '$sAreaName', `sAreaNamePersian` = '$sAreaNamePersian', `sSpecialArea` = '$sSpecialArea', `sPriority` = '$sPriority', `sFeatureCollection` = '$sFeatureCollection', `sPolygonArea` = PolyFromText('$polyText'), `sActive` = '$sActive', `mapCenter` = '$mapCenter', `mapZoom` = '$mapZoom' WHERE `savar_area`.`aId` = $aId;";
				$res = $obj->sql_query($sql);

				if($res == false)
				{
					$error .= 'خطایی در ویرایش اطلاعات به وجود آمد<br>';
				}
				else
				{
					$res = $obj->MySQLSelect("SELECT aId FROM savar_area WHERE  `sAreaName` = '$sAreaName'");
					if(count($res) > 0 )
					{
						$id = $res[0]['aId'];
						header("Location: " . $_SERVER['SCRIPT_NAME'] ."?edit&editmsg&id=" . $id);
					}
				}
			}
		}

	}

	function GetPost($key)
	{
		return isset($_POST[$key]) ? $_POST[$key] : '';
	}

?><!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
<link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet' />
        <title>Area</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?
			include_once('global_files.php');
		?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <!-- Google Map Js -->
		<!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en"></script>-->

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
                            <h2>Area</h2>
							<?php if ($action == 'Edit') { ?>
								<a href="cab_booking.php">
									<input type="button" value="Back to Listing" class="add-btn">
								</a>
							<?php } ?>
						</div>
					</div>
                    <hr />
                    <div class="body-div add-booking1">
						<!--a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModal"><i class="fa fa-question-circle" style="font-size: 18px;"></i> How it works?</a-->

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

                            <?php if ($error !== '') {?>
								<div class="alert alert-danger alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
									<?php echo  $error ?>
								</div><br/>
							<?php } ?>
							<?php if ($success == 0 && $var_msg != "") {?>
								<div class="alert alert-danger alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
									<?php echo $var_msg;?>
								</div><br/>
							<?php } ?>
                            <div class="row">
                            <div class="col-lg-5">

                                <form name="add_booking_form" id="add_booking_form" method="post" action="area_new.php" enctype="multipart/form-data">
									<input type="hidden" name="action" value="<?php echo  $action ?>" />
									<input type="hidden" name="aId" value="<?php echo  $aId ?>" />
									<input type="hidden" name="mapCenter" id="mapCenter" value="<?php echo  htmlentities($mapCenter) ?>" />
									<input type="hidden" name="mapZoom" id="mapZoom" value="<?php echo  $mapZoom ?>" />
									<div class="add-booking-form">
									<span>
										<span>
											<span>
											  <input type="text" title="Enter Area Name." class="form-control add-book-input1" name="sAreaName"  id="sAreaName" value="<?php echo  $sAreaName; ?>" placeholder="Enter Area Name." required style="">
											</span>

											<span>
											<input type="text" title="Persian Area Name" class="form-control first-name2" name="sAreaNamePersian"  id="sAreaNamePersian" value="<?php echo  $sAreaNamePersian; ?>" placeholder="Persian Area Name" required>
											</span>
											<span>
												<?php
													$select1 = ($sSpecialArea == 'Yes' ? 'selected' : '');
													$select2 = ($sSpecialArea == 'No' ? 'selected' : '');
												?>
												<select class="form-control form-control-select" name='sSpecialArea' id="radius-id" required">
                                                <option value="No" <?php echo  $select1 ?>>Is Not Special Area</option>
                                                <option value="Yes" <?php echo  $select2 ?>>Special Area</option>

                                            </select></span>
											<span>
											<input type="text" class="form-control" name="sPriority"  id="sPriority" value="<?php echo  $sPriority; ?>" placeholder="Priority" required>
											</span>
											<span>
												<?php
													$select1 = ($sActive == 'Yes' ? 'selected' : '');
													$select2 = ($sActive == 'No' ? 'selected' : '');
												?>
												<select class="form-control form-control-select" name='sActive' id="radius-id" required">
                                                <option value="Yes" <?php echo  $select1 ?>>Active</option>
                                                <option value="No" <?php echo  $select2 ?>>Inactive</option>

                                            </select></span>
											<span>
											<?php $btnText = isset($_GET['edit']) ? "Edit Area" : "Add Area" ; ?>
												<input type="submit" class="save btn-info button-submit" name="submit" id="submit" value="<?php echo  $btnText ?>" >
												<input type="reset" class="save btn-info button-submit" name="reset" id="reset12" value="Reset" >
											</span>
											<span>
												<textarea style="display:none;" class="form-control" id="sFeatureCollection" name="sFeatureCollection"><?php echo  $sFeatureCollection; ?></textarea>
											</span>

									</div>

								</form>

							</div>
                            <div class="col-lg-7">
								<div class="gmap-div gmap-div1" style="float:right;width:100%;"><div id="map" class="gmap3"></div></div>
							</div>
							</div>

							<div class="row">
							<div class="col-lg-7">
								<input type="button" class="save btn-info button-submit" value="Reset Map" onclick="ResetMap()" >
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

		<link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
		<script type="text/javascript" src="js/moment.min.js"></script>
		<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>

		<script async defer src1="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>&callback=initMap"></script>

		<script src1="http://freegoogle.ir/https://maps.googleapis.ir/maps/api/js?key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>&v=3.exp"></script>

		<script src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>"></script>

		<script src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>&sensor=false"></script>

    <script src="./dist/leaflet-heat.js"></script>
    <script src="http://leaflet.github.io/Leaflet.markercluster/example/realworld.10000.js"></script>

    <script type="text/javascript">
        L.cedarmaps.accessToken = 'a3783e960e2a0d124a73c400af439fd4c21f2ed2'; // See the note below on how to get an access token

        // Getting maps info from a tileJSON source
        var tileJSONUrl = 'https://api.cedarmaps.com/v1/tiles/cedarmaps.streets.json?access_token=' + L.cedarmaps.accessToken;
        var map = L.cedarmaps.map('map', tileJSONUrl, {
                    scrollWheelZoom: true,
                    <?php if($mapCenter != '') {
                    echo'center: ' . $mapCenter . ',';
                  }
                  else {
                    echo 'center: {lat: 35.6899828, lng: 51.389644},';
                    //echo 'center: {lat: 51.505, lng: -0.09},';
                  }
                    if($mapZoom != '') {
                     echo 'zoom:' . $mapZoom . ',';

                   }
                   else {
              echo 'zoom: 15,';

                   } ?>
                    fullscreenControl: true
                });

                var heat = L.heatLayer([
                	[50.5, 30.5, 0.2],
                  [50.5, 30.5, 0.2],
                  [50.5, 30.5, 0.2],
                  [50.5, 30.5, 0.2],
                  [50.5, 30.5, 0.2],
                  [50.5, 30.5, 0.2],
                  [50.5, 30.5, 0.2], // lat, lng, intensity
                	[99.6, 70.4, 0.5]

                ], {radius: 25}).addTo(map);


                var polygon = L.polygon([
    {lat: 35.6899828, lng: 51.389644},
    {lat: 35.7899828, lng: 51.489644},
    {lat: 35.8899828, lng: 51.689644}
]).addTo(map);
    </script>




<script>
function mapinit() {
  // Initialise the map.
  map = new google.maps.Map(document.getElementById('map-canvas'), {
	center: {lat: 35.6899828, lng: 51.389644},
	zoom: 12,
	fullscreenControl: true
  });
  map.data.setControls(['Polygon']); //'Point', 'LineString',
  map.data.setStyle({
	editable: true,
	draggable: true
  });

  <?php if($mapCenter != '') : ?>
	map.setCenter(JSON.parse('<?php echo  $mapCenter ?>'));
  <?php endif; ?>
  <?php if($mapZoom != '') : ?>
	map.setZoom(<?php echo  $mapZoom ?>);
  <?php endif; ?>


  map.addListener('center_changed', function() {
    $("#mapCenter").val(JSON.stringify(map.getCenter()));
    $("#mapZoom").val(map.getZoom());
  });


  bindDataLayerListeners(map.data);

  if(typeof window.mapReset == "undefined" || window.mapReset != true)
  {
	  window.mapReset = false;
	  <?php
	  if($sFeatureCollection != '')
		  echo "map.data.addGeoJson(JSON.parse($('#sFeatureCollection').val()));\r\n";
	  ?>
  }
}
google.maps.event.addDomListener(window, 'load', mapinit);

function ResetMap()
{
	window.mapReset = true;
	mapinit();
}

// Apply listeners to refresh the GeoJson display on a given data layer.
function bindDataLayerListeners(dataLayer) {
  dataLayer.addListener('addfeature', refreshGeoJsonFromData);
  dataLayer.addListener('removefeature', refreshGeoJsonFromData);
  dataLayer.addListener('setgeometry', refreshGeoJsonFromData);
}

function refreshGeoJsonFromData() {
  map.data.toGeoJson(function(geoJson) {
    console.log(geoJson);
	$("#sFeatureCollection").val(JSON.stringify(geoJson, null, 2));
  });
}



</script>

        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script>


		</script>
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
