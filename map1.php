<?php
include_once('../common.php');



//Get Driver list






//End
















	include_once('savar_check_permission.php');
	if(checkPermission('MAP') == false)
		die('you dont`t have permission...');


if(!isset($generalobjAdmin)){
	require_once(TPATH_CLASS."class.general_admin.php");
	$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script="Map";
































$sql = "select * from company WHERE eStatus != 'Deleted'";
$db_company = $obj->MySQLSelect($sql);

if($_GET['id'] != '')
{
	$sql = "select * from register_driver WHERE iCompanyId = '" . $_GET['id'] . "'";
	$db_drivercompany = $obj->MySQLSelect($sql);
}





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
	<title>Admin | Dashboard</title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<!--meta http-equiv="refresh" content="60"-->
	<!--[if IE]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<![endif]-->
	<!-- GLOBAL STYLES -->
	<?php include_once('global_files.php');?>
	<link rel="stylesheet" href="css/style.css" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
	<!--<script src="http://freegoogle.ir/http://maps.google.com/maps/api/js?sensor=true&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>" type="text/javascript"></script>-->
	<script type='text/javascript' src='../assets/map/gmaps.js'></script>
	<!--END GLOBAL STYLES -->

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

		<div class="inner" style="min-height: 700px;">
			<div class="row">
				<div class="col-lg-12">
					<h1> God's View / Map View</h1>
				</div>
			</div>
			<hr />

			<!-- COMMENT AND NOTIFICATION  SECTION -->
			<div class="row">
				<div class="col-lg-12">

					<div class="chat-panel panel panel-default">
						<div class="panel-heading">
							<i class="icon-map-marker"></i>
							Locations
						</div>



					</br>
					<div style="margin: 13px;margin-top: -6px;">
						Company<br>
						<select class="form-control" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">
							<option value="map.php">--select--</option>
							<?php for ($i = 0; $i < count($db_company); $i++) {
								if($db_company[$i]['iCompanyId'] == $_GET['id'])
								{
								echo '<option selected value ="map.php?id=' . $db_company[$i]['iCompanyId'] . '">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
							 }
							 else {
							 	echo '<option value ="map.php?id=' . $db_company[$i]['iCompanyId'] . '">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
							 }
} ?>
						</select>
</div>








						<div class="panel-heading" style="background:none;">
							<div class="google-map-wrap" >
								<div id="map" style="position: relative;width: 100;height: 65em;" class="google-map">
								</div><!-- #google-map -->
							</div>
						</div>

					</div>
				</div>
			</div>
			<div class="hrhr"><hr /></div>
			<div class="row">
				<div class="col-lg-12">
											<?php if($_GET['id'] != '')
											{
					echo '<div style="" class="quick-btn-bottom-part">
						<a class="quick-btn" href="map.php?type=online&id=' . $_GET['id'] . '">
							<img src="../webimages/upload/mapmarker/green.png">
							<span>Online</span>
							<span id="onlineCnt"></span>
						</a>
						<a class="quick-btn" href="map.php?type=enroute&id=' . $_GET['id'] . '">
							<img src="../webimages/upload/mapmarker/orange.png">
							<span>On' . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . '</span>
							<span id="onTripCnt"></span>
						</a>
						<a class="quick-btn" href="map.php?type=offline&id=' . $_GET['id'] . '">
							<img src="../webimages/upload/mapmarker/gray.png">
							<span>Offline</span>
							<span id="offlineCnt"></span>
						</a>
						<a class="quick-btn" href="map.php?id=' . $_GET['id'] . '">
							<b><i class="icon-align-justify icon-1x"></i></b>
							<span>All</span>
							<span id="allCnt"></span>
						</a>
					</div>
					<select class="form-control" name = "driver" id = "driver" onchange="location = this.value;">
					<option value="map.php?id=' . $_GET['id'] . '">--select--</option>
										';

					for ($i = 0; $i < count($db_drivercompany); $i++) {
					 if($db_drivercompany[$i]['iDriverId'] == $_GET['driver'])
					 {
					 echo '<option selected value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					}
					else {
					 echo '<option value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					}
}
		 echo	"</select>";



		 }?>
				</div>

			</div>

		</div>
		<!-- END COMMENT AND NOTIFICATION  SECTION -->
	</div>
</div>

<!--END PAGE CONTENT -->
</div>

<?php include_once('footer.php'); ?>


<script>
	jQuery( document ).ready( function($) {
		/* Do not drag on mobile. */
		var is_touch_device = 'ontouchstart' in document.documentElement;
		var newName; var newAddr; var newOnlineSt; var newLat; var newLong; var newImg; var map;
		var bounds = [];
		var markers = [];
		var latlng;
		var type = '<?php echo $_REQUEST['type']; ?>';
    L.cedarmaps.accessToken = 'a3783e960e2a0d124a73c400af439fd4c21f2ed2'; // See the note below on how to get an access token

    // Getting maps info from a tileJSON source
    var tileJSONUrl = 'https://api.cedarmaps.com/v1/tiles/cedarmaps.streets.json?access_token=' + L.cedarmaps.accessToken;

    // initilizing map into div#map
    var map = L.cedarmaps.map('map', tileJSONUrl, {
        scrollWheelZoom: true
    }).setView([35.757448286487595, 51.40876293182373], 8);


    	var LeafIcon = L.Icon.extend({
    		options: {

    		}
    	});

    	var greenIcon = new LeafIcon({iconUrl: '../webimages/upload/mapmarker/green.png'}),
    		redIcon = new LeafIcon({iconUrl: '../webimages/upload/mapmarker/gray.png'}),
    		orangeIcon = new LeafIcon({iconUrl: '../webimages/upload/mapmarker/orange.png'});

    	//L.marker([35.757448286487595, 51.40876293182373], {icon: greenIcon}).bindPopup("I am a green leaf.").addTo(map);
    	//L.marker([51.495, -0.083], {icon: redIcon}).bindPopup("I am a red leaf.").addTo(map);
    	//L.marker([51.49, -0.1], {icon: orangeIcon}).bindPopup("I am an orange leaf.").addTo(map);

			<?php

			//Get Driver list




			/* START COUNT QUERY */
			if($_GET['id'] != ""){$whereicpmpany = " and iCompanyId=".$_GET['id'];

			if($_GET['driver'] != ""){$whereicpmpany .= " and iDriverId=".$_GET['driver'];}
			}else{$whereicpmpany =  " and iCompanyId='' and vLongitude != ''";}
			/* START COUNT QUERY */
			$sql = "select count(iDriverId) AS ONLINE FROM register_driver WHERE vLatitude !='' AND vLongitude !='' AND vAvailability = 'Available' $whereicpmpany";
			$db_records_online = $obj->MySQLSelect($sql);
			$sql = "select count(iDriverId) AS OFFLINE FROM register_driver WHERE vLatitude !='' AND vLongitude !='' AND vAvailability = 'Not Available' $whereicpmpany";
			$db_records_offline = $obj->MySQLSelect($sql);
			#echo "<pre>"; print_r($db_records_online );echo "</pre>";
			$sql = "select iDriverId,tLastOnline,vAvailability,vTripStatus FROM register_driver WHERE vLatitude !='' AND vLongitude !='' $whereicpmpany";
			$db_total_driver = $obj->MySQLSelect($sql);
			#echo "<pre>"; print_r($db_total_driver );echo "</pre>";exit;
			$tot_online = 0;
			$tot_ofline = 0;
			$tot_ontrip = 0;
			for($ji=0;$ji<count($db_total_driver);$ji++){
			   $curtime = time();
			   $last_driver_online_time = strtotime($db_total_driver[$ji]['tLastOnline']);
			   $online_time_difference = $curtime-$last_driver_online_time;
			   if($online_time_difference <= 300 && $db_total_driver[$ji]['vAvailability'] == "Available"){
			      $tot_online = $tot_online+1;
			   }else{
			      $vTripStatus = $db_total_driver[$ji]['vTripStatus'];
			      if($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived'){
			         $tot_ontrip = $tot_ontrip+1;
			      }else{
			         $tot_ofline = $tot_ofline+1;
			      }
			   }
			}
			$newStatus['ONLINE'] = $tot_online;
			$newStatus['OFFLINE'] = $tot_ofline;
			$newStatus['ONTRIP'] = $tot_ontrip;
			$newStatus['All'] = $tot_online+$tot_ofline+$tot_ontrip;
			#echo date("Y-m-d H:i:s"); echo "<br/>";
			#echo $tot_online;echo "<br/>";
			#echo $tot_ofline;echo "<br/>";  exit;

			/* END COUNT QUERY */
			function getaddress($lat,$lng)
			{
			   $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
			   $json = @file_get_contents($url);
			   $data=json_decode($json);
			   $status = $data->status;
			   if($status=="OK")
			   {
			     return $data->results[0]->formatted_address;
			   }
			   else
			   {
			     return "Address Not Found";
			   }
			}

			//echo "<pre>"; print_r($_SESSION);echo "</pre>";
			if(isset($_REQUEST['type']) && $_REQUEST['type'] != '')
			{
				if($_REQUEST['type'] == 'online' )
					//$tsql = " AND vAvailability = 'Available'";
			    $tsql ="";
				else if($_REQUEST['type'] == 'offline' )
					//$tsql = " AND vAvailability = 'Not Available'";
			    $tsql ="";
				else
					$tsql ="";
			}

			$sql = "SELECT iDriverId,iCompanyId, CONCAT(vName,' ',vLastName) AS FULLNAME,vLatitude,vLongitude,vServiceLoc,vAvailability,vTripStatus,tLastOnline
										FROM register_driver
											WHERE vLatitude !='' AND vLongitude !='' $tsql $whereicpmpany";
			$db_records = $obj->MySQLSelect($sql);
			//change count($db_records) to 20 Mamad H . A . M
			for($i=0;$i<20;$i++){
			   $time = time();
			   $last_online_time = strtotime($db_records[$i]['tLastOnline']);
			   $time_difference = $time-$last_online_time;
			   if($time_difference <= 300 && $db_records[$i]['vAvailability'] == "Available"){
			      $db_records[$i]['vAvailability'] = "Available";
			   }else{
			      //$db_records[$i]['vAvailability'] = "Not Available";
			      $vTripStatus = $db_records[$i]['vTripStatus'];
			      if($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived'){
			         //$tot_ontrip = $tot_ontrip+1;
			         $db_records[$i]['vAvailability'] = "Ontrip";
			      }else{
			         //$tot_ofline = $tot_ofline+1;
			         $db_records[$i]['vAvailability'] = "Not Available";
			      }
			   }
			   $db_records[$i]['vServiceLoc'] = getaddress($db_records[$i]['vLatitude'],$db_records[$i]['vLongitude']);
			}
			#echo "<pre>";print_r($db_records);exit;
			#echo "<pre>"; print_r($db_records);echo "</pre>";
			$locations = array();

			#marker Add
			if($_REQUEST['type'] == ''){
			  foreach ($db_records as $key => $value) {
					if($value['vLatitude'] != '')
			{  	$locations[] = array(
			  		'google_map' => array(
			  			'lat' => $value['vLatitude'],
			  			'lng' => $value['vLongitude'],
			  		),
			  		'location_address' => $value['vServiceLoc'],
			  		'location_name'    => $value['FULLNAME'],
			  		'location_online_status'    => $value['vAvailability'],
			  	);}

			  }
			}else if($_REQUEST['type'] == 'online'){
			  foreach ($db_records as $key => $value) {
			    if($value['vAvailability'] == "Available" && $value['vLatitude'] != ''){
			    	$locations[] = array(
			    		'google_map' => array(
			    			'lat' => $value['vLatitude'],
			    			'lng' => $value['vLongitude'],
			    		),
			    		'location_address' => $value['vServiceLoc'],
			    		'location_name'    => $value['FULLNAME'],
			    		'location_online_status'    => $value['vAvailability'],
			    	);
			    }
			  }
			}else if($_REQUEST['type'] == 'enroute'){
			  foreach ($db_records as $key => $value) {
			    if($value['vAvailability'] == "Ontrip" && $value['vLatitude'] != ''){
			    	$locations[] = array(
			    		'google_map' => array(
			    			'lat' => $value['vLatitude'],
			    			'lng' => $value['vLongitude'],
			    		),
			    		'location_address' => $value['vServiceLoc'],
			    		'location_name'    => $value['FULLNAME'],
			    		'location_online_status'    => $value['vAvailability'],
			    	);
			    }
			  }
			}else{
			  foreach ($db_records as $key => $value) {
			    if($value['vAvailability'] == "Not Available" && $value['vLatitude'] != ''){
			    	$locations[] = array(
			    		'google_map' => array(
			    			'lat' => $value['vLatitude'],
			    			'lng' => $value['vLongitude'],
			    		),
			    		'location_address' => $value['vServiceLoc'],
			    		'location_name'    => $value['FULLNAME'],
			    		'location_online_status'    => $value['vAvailability'],
			    	);
			    }
			  }
			}

			$returnArr['Action'] = "0";
			$returnArr['locations'] = $locations;
			$returnArr['db_records'] = $db_records;
			$returnArr['newStatus'] = $newStatus;




			//END


			 ?>












					//$.ajax({

						//type: "POST",
						//url: "",
						//dataType: "json",
						//data: {type: type},
						//success: function(dataHtml){
							$("#onlineCnt").text(<?php echo $newStatus['ONLINE'];?>);
							$("#offlineCnt").text(<?php echo $newStatus['OFFLINE'];?>);
							$("#onTripCnt").text(<?php echo $newStatus['ONTRIP'];?>);
							$("#allCnt").text(<?php echo $newStatus['All'] = $tot_online+$tot_ofline+$tot_ontrip;?>);
							//var newLocations = <?php echo $returnArr['locations']; ?>;


			<?php
							for ($i = 0; $i < count($returnArr['locations']); $i++) {
								$newName = $returnArr['locations'][$i]['location_name'];
								$newAddr = $returnArr['locations'][$i]['location_address'];
								$newOnlineSt = $returnArr['locations'][$i]['location_online_status'];
								$newLat = $returnArr['locations'][$i]['google_map']['lat'];
								$newLong = $returnArr['locations'][$i]['google_map']['lng'];
			if(1 == 2)
			{

			}else {


							echo   "

							newName = '$newName';
							newAddr = '$newAddr';
							newOnlineSt = '$newOnlineSt';
							newLat = '$newLat';
							newLong = '$newLong';


							if(newOnlineSt == 'Available') {L.marker([newLat, newLong], {icon: greenIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(map);} else if(newOnlineSt == 'Ontrip') { L.marker([newLat, newLong], {icon: orangeIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(map);}else { L.marker([newLat, newLong], {icon: redIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(map); }";
								}}?>

							//map.fitLatLngBounds(bounds);
						//},

					//});
					/* Map Reload after a minute */
					setInterval(function() {
						$("#onlineCnt").text(<?php echo $newStatus['ONLINE'];?>);
						$("#offlineCnt").text(<?php echo $newStatus['OFFLINE'];?>);
						$("#onTripCnt").text(<?php echo $newStatus['ONTRIP'];?>);
						$("#allCnt").text(<?php echo $newStatus['All'] = $tot_online+$tot_ofline+$tot_ontrip;?>);
						//var newLocations = <?php echo $returnArr['locations']; ?>;


			<?php
						for ($i = 0; $i < count($returnArr['locations']); $i++) {
							$newName = $returnArr['locations'][$i]['location_name'];
							$newAddr = $returnArr['locations'][$i]['location_address'];
							$newOnlineSt = $returnArr['locations'][$i]['location_online_status'];
							$newLat = $returnArr['locations'][$i]['google_map']['lat'];
							$newLong = $returnArr['locations'][$i]['google_map']['lng'];
			if(1 == 2)
			{

			}else {


						echo   "

						newName = '$newName';
						newAddr = '$newAddr';
						newOnlineSt = '$newOnlineSt';
						newLat = '$newLat';
						newLong = '$newLong';


						if(newOnlineSt == 'Available') {L.marker([newLat, newLong], {icon: greenIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(map);} else if(newOnlineSt == 'Ontrip') { L.marker([newLat, newLong], {icon: orangeIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(map);}else { L.marker([newLat, newLong], {icon: redIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(map); }";
							}}?>
							console.log("Reloded Marker");
					},30000);
					/* Map Reload after a minute */


					var $window = $(window);
					function mapWidth() {
						var size = $('.google-map-wrap').width();
						$('.google-map').css({width: size + 'px', height: (size/2) + 'px'});
					}
					mapWidth();
					$(window).resize(mapWidth);
				});
			</script>
			</body>
			<!-- END BODY-->
			</html>
