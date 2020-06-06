<?php
include_once('../common.php');

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
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	  <script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
		<link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet' />
	<title>نقشه رانندگان | ادمین</title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<!--meta http-equiv="refresh" content="60"-->
	<!--[if IE]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<![endif]-->
	<!-- GLOBAL STYLES -->
	<?php include_once('global_files.php');?>
	<link rel="stylesheet" href="css/style.css" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
	<script type='text/javascript' src='../assets/map/gmaps.js'></script>
	<!--END GLOBAL STYLES -->
<link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/mapp.min.css">
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/fa/style.css">
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
					<h1>نقشه رانندگان</h1>
				</div>
			</div>
			<hr />

			<!-- COMMENT AND NOTIFICATION  SECTION -->
			<div class="row">
				<div class="col-lg-12">

					<div class="chat-panel panel panel-default">
						<div class="panel-heading">
							<i class="icon-map-marker"></i>
							مختصات
						</div>



					</br>
					<div style="margin: 13px;margin-top: -6px;">
					<?php echo $langage_lbl['LBL_Company']; ?><br>
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











<?php if($_GET['id'] != '')
{
echo '</br>
<div style="margin: 13px;margin-top: -6px;">
'.$langage_lbl['LBL_ONLINE'].'
	<br>
	<select class="form-control" name = "iCompanyId" id = "iCompanyId" onchange="location = this.value;">';


			if('' != 2)
			{
				for ($i = 0; $i < count($db_drivercompany); $i++) {
					$curtime2 = time();
					$last_driver_online_time2 = strtotime($db_drivercompany[$i]['tLastOnline']);
					$online_time_difference2 = $curtime2-$last_driver_online_time2;
					if($db_drivercompany[$i]['vLatitude'] != '' && $db_drivercompany[$i]['vAvailability'] == 'Available' && $online_time_difference2 <= 300)
					{

						if($db_drivercompany[$i]['iDriverId'] == $_GET['driver'])
						{
						echo '<option selected value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					 }
					 else {
						echo '<option value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					 }



					}

}
echo	"</select>";
}
		 echo
'	</select>
</div>
';
}?>


<?php if($_GET['id'] != '')
{
echo '</br>
<div style="margin: 13px;margin-top: -6px;">
'.$langage_lbl['LBL_ON_TRIP_TXT'].'
	<br>
	<select class="form-control" name = "iCompanyId" id = "iCompanyId" onchange="location = this.value;">';


			if('' != 2)
			{
				for ($i = 0; $i < count($db_drivercompany); $i++) {
					if($db_drivercompany[$i]['vLatitude'] != '' && ($db_drivercompany[$i]['vTripStatus'] == 'Active' || $db_drivercompany[$i]['vTripStatus'] == 'On Going Trip' || $db_drivercompany[$i]['vTripStatus'] == 'Arrived'))
					{

						if($db_drivercompany[$i]['iDriverId'] == $_GET['driver'])
						{
						echo '<option selected value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					 }
					 else {
						echo '<option value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					 }



					}

}
echo	"</select>";
}
		 echo
'	</select>
</div>
';
}?>



<?php if($_GET['id'] != '')
{
echo '</br>
<div style="margin: 13px;margin-top: -6px;">
'.$langage_lbl['LBL_OFFLINE'].'
	<br>
	<select class="form-control" name = "iCompanyId" id = "iCompanyId" onchange="location = this.value;">';


			if('' != 2)
			{
				for ($i = 0; $i < count($db_drivercompany); $i++) {
					$curtime2 = time();
					$last_driver_online_time2 = strtotime($db_drivercompany[$i]['tLastOnline']);
					$online_time_difference2 = $curtime2-$last_driver_online_time2;
					if($db_drivercompany[$i]['vLatitude'] != '' && $db_drivercompany[$i]['vTripStatus'] != 'On Going Trip' && $db_drivercompany[$i]['vTripStatus'] != 'Arrived' &&  $online_time_difference2 > 300)
					{

						if($db_drivercompany[$i]['iDriverId'] == $_GET['driver'])
						{
						echo '<option selected value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					 }
					 else {
						echo '<option value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
					 }



					}

}
echo	"</select>";
}
		 echo
'	</select>
</div>
';
}?>









						<div class="panel-heading" style="background:none;">
							<div class="google-map-wrap" >
								<div id="app" style="width: 100;height: 65em;"></div>
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
							<span>'.$langage_lbl['LBL_ONLINE'].'</span>
							<span id="onlineCnt"></span>
						</a>
						<a class="quick-btn" href="map.php?type=enroute&id=' . $_GET['id'] . '">
							<img src="../webimages/upload/mapmarker/orange.png">
							<span>'.$langage_lbl['LBL_ON_TRIP_TXT'].'</span>
							<span id="onTripCnt"></span>
						</a>
						<a class="quick-btn" href="map.php?type=offline&id=' . $_GET['id'] . '">
							<img src="../webimages/upload/mapmarker/gray.png">
							<span>'.$langage_lbl['LBL_OFFLINE'].'</span>
							<span id="offlineCnt"></span>
						</a>
						<a class="quick-btn" href="map.php?id=' . $_GET['id'] . '">
							<b><i class="icon-align-justify icon-1x"></i></b>
							<span>همه</span>
							<span id="allCnt"></span>
						</a>
					</div>
					<select class="form-control" name = "driver" id = "driver" onchange="location = this.value;">
					<option value="map.php?id=' . $_GET['id'] . '">--select--</option>
										';

										for ($i = 0; $i < count($db_drivercompany); $i++) {
											if($db_drivercompany[$i]['vLatitude'] != '')
											{
												if($db_drivercompany[$i]['iDriverId'] == $_GET['driver'])
												{
												echo '<option selected value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
											 }
											 else {
												echo '<option value ="map.php?id=' . $_GET['id'] . '&driver=' . $db_drivercompany[$i]['iDriverId'] . '">' . $db_drivercompany[$i]['vName'] . " " . $db_drivercompany[$i]['vLastName'] . " </option>";
											 }
											}

					}
		 echo	'</select>

		 <span>
		   <input type="text"  title="Enter Mobile Number." class="form-control add-book-input" name="search_driver_ajax2"  id="search_driver_ajax2" value="' . $vPhone . '" placeholder="نام یا نام خانوادگی یا شماره موبایل راننده را وارد کنید"  style="">
		 <a class="btn btn-sm btn-info" id="search_driver_ajax" >جست و جو</a>
		 </span>
		 ';



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
	<?php include_once('header_map_ir.php'); ?>
<script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.env.js"></script>
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.min.js"></script>

<script>






$('#search_driver_ajax').on('click', function () {
  $('#driver')
      .find('option')
      .remove()

  ;
    var phone = $('#search_driver_ajax2').val();
    $.ajax({
        type: "POST",
        url: 'search_driver_ajax.php',
        data: 'phone=' + phone,
        success: function (dataHtml)
        {
console.log(dataHtml);
            if (dataHtml != "" || dataHtml != ":::~" || dataHtml != " ") {
              var result;
                var result1 = dataHtml.split('~');
                //alert(result1.length);
                for(i = 0; i<result1.length-1;i++)
                {
                  result = result1[i].split(':');
                  //alert(result1[1]);
                                          $('#driver')
                                              .find('option')
                                                              .end()
                                              .append('<option value="' + result[2] + '">' + result[0] + ' ' + result[1] + '</option>')
                                              .val(result[2])
                                          ;
                }


  }else {
    $('#driver')
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










	jQuery( document ).ready( function($) {
		/* Do not drag on mobile. */
		var is_touch_device = 'ontouchstart' in document.documentElement;
		var newName; var newAddr; var newOnlineSt; var newLat; var newLong; var newImg; var app;
		var bounds = [];
		var markers = [];
		var latlng;
		var type = '<?php echo $_REQUEST['type']; ?>';
		 var app = new Mapp({
			element: '#app',
			presets: {
				latlng: {
					lat: 35.757448286487595,
					lng: 51.40876293182373,
				},
				zoom: 8,
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


    	var LeafIcon = L.Icon.extend({
    		options: {

    		}
    	});

    	var greenIcon =  L.icon({iconUrl: '../webimages/upload/mapmarker/green.png',
			iconSize:     [35, 35], // size of the icon
			shadowSize:   [50, 64], // size of the shadow
			iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
			shadowAnchor: [4, 62],  // the same for the shadow
			popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
}),
    		redIcon =  L.icon({iconUrl: '../webimages/upload/mapmarker/gray.png',
				iconSize:     [35, 35], // size of the icon
				shadowSize:   [50, 64], // size of the shadow
				iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
				shadowAnchor: [4, 62],  // the same for the shadow
				popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
}),
    		orangeIcon =  L.icon({iconUrl: '../webimages/upload/mapmarker/orange.png',
				 iconSize:     [35, 35], // size of the icon
				 shadowSize:   [50, 64], // size of the shadow
				 iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
				 shadowAnchor: [4, 62],  // the same for the shadow
				 popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
});

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
			   //if($online_time_difference <= 300 && $db_total_driver[$ji]['vAvailability'] == "Available"){
				 if($db_total_driver[$ji]['vAvailability'] == "Available"){
					 if($online_time_difference > 300)
					 {
						 $tot_ofline = $tot_ofline+1;
					 }
					 else {
					 	$tot_online = $tot_online+1;
					 }

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
			function getaddress($lat,$lng,$D)
			{
			   $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false&key='.$D;
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
			      if($value['vLatitude'] != '' && ($value['vTripStatus'] == 'Active' || $value['vTripStatus'] == 'On Going Trip' || $value['vTripStatus'] == 'Arrived')){
			         //$tot_ontrip = $tot_ontrip+1;
			         $db_records[$i]['vAvailability'] = "Ontrip";
			      }else{
			         //$tot_ofline = $tot_ofline+1;
			         $db_records[$i]['vAvailability'] = "Not Available";
			      }
			   }
			   //$db_records[$i]['vServiceLoc'] = getaddress($db_records[$i]['vLatitude'],$db_records[$i]['vLongitude'],$GOOGLE_SEVER_API_KEY_WEB);
			}
			#echo "<pre>";print_r($db_records);exit;
			#echo "<pre>"; print_r($db_records);echo "</pre>";
			$locations = array();

			#marker Add
			if($_REQUEST['type'] == ''){
			  foreach ($db_records as $key => $value) {
					$time4 = time();
					$last_online_time4 = strtotime($value['tLastOnline']);
					$time_difference4 = $time4-$last_online_time4;
					if($value['vLatitude'] != '' && ($value['vTripStatus'] == 'Active' || $value['vTripStatus'] == 'On Going Trip' || $value['vTripStatus'] == 'Arrived'))
					{
						$locations[] = array(
						  		'google_map' => array(
						  			'lat' => $value['vLatitude'],
						  			'lng' => $value['vLongitude'],
						  		),
						  		'location_address' => $value['vServiceLoc'],
						  		'location_name'    => $value['FULLNAME'],
						  		'location_online_status'    => $value['vAvailability'],
						  	);
					}else {


					if($value['vLatitude'] != '' && $time_difference4 <= 300)
			{  	$locations[] = array(
			  		'google_map' => array(
			  			'lat' => $value['vLatitude'],
			  			'lng' => $value['vLongitude'],
			  		),
			  		'location_address' => $value['vServiceLoc'],
			  		'location_name'    => $value['FULLNAME'],
			  		'location_online_status'    => $value['vAvailability'],
			  	);




				}
				else {
					$locations[] = array(
					  		'google_map' => array(
					  			'lat' => $value['vLatitude'],
					  			'lng' => $value['vLongitude'],
					  		),
					  		'location_address' => $value['vServiceLoc'],
					  		'location_name'    => $value['FULLNAME'],
					  		'location_online_status'    => 'offline',
					  	);
				}
			}

			  }
			}else if($_REQUEST['type'] == 'online'){
			  foreach ($db_records as $key => $value) {
					$time4 = time();
					$last_online_time4 = strtotime($value['tLastOnline']);
					$time_difference4 = $time4-$last_online_time4;
			    if($value['vAvailability'] == "Available" && $value['vLatitude'] != '' && $time_difference4 <= 300){
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
					//die("sssss = " . $value['vAvailability']);
			    if($value['vLatitude'] != '' && ($value['vTripStatus'] == 'Active' || $value['vTripStatus'] == 'On Going Trip' || $value['vTripStatus'] == 'Arrived')){
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
			    		'location_online_status'    => 'offline',
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
								//}

								$newLat = $returnArr['locations'][$i]['google_map']['lat'];
								$newLong = $returnArr['locations'][$i]['google_map']['lng'];
			if(1 == 2)
			{

			}else {


							echo   "

							newName = '$newName';
							newAddr =  ".'"' . $newAddr . '"'.";
							newOnlineSt = ".'"' . $newOnlineSt . '"'.";
							newLat = '$newLat';
							newLong = '$newLong';


							if(newOnlineSt == 'Available') {L.marker([newLat, newLong], {icon: greenIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(Mapp);} else if(newOnlineSt == 'Ontrip' || newOnlineSt == 'Not Available') { L.marker([newLat, newLong], {icon: orangeIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(Mapp);}else { L.marker([newLat, newLong], {icon: redIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(Mapp); }";

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
							//if($online_time_difference > 300)
							//{
								//$newOnlineSt = "offline";
							//}
							//else {
								$newOnlineSt = $returnArr['locations'][$i]['location_online_status'];
							//}
							$newLat = $returnArr['locations'][$i]['google_map']['lat'];
							$newLong = $returnArr['locations'][$i]['google_map']['lng'];
			if(1 == 2)
			{

			}else {


						echo   "

						newName = '$newName';
						newAddr =  ".'"' . $newAddr . '"'.";
						newOnlineSt = ".'"' . $newOnlineSt . '"'.";
						newLat = '$newLat';
						newLong = '$newLong';


						if(newOnlineSt == 'Available') {L.marker([newLat, newLong], {icon: greenIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(Mapp);} else if(newOnlineSt == 'Ontrip' || newOnlineSt == 'Not Available') { L.marker([newLat, newLong], {icon: orangeIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(Mapp);}else { L.marker([newLat, newLong], {icon: redIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>Last Location </td><td>- '+newAddr+'</tr></table>').addTo(Mapp); }";
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
