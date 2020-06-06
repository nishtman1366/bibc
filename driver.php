<?php
include_once('common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');


$generalobj->check_member_login();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$iCompanyId = $_SESSION['sess_iUserId'];

//echo "<pre>";print_r($_SESSION);exit;

$sql = "select * from country";
$db_country = $obj->MySQLSelect($sql);

$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);

$script = 'Driver';
if ($action == 'delete') {
	// if(SITE_TYPE != 'Demo')
	// {
	$query = "UPDATE register_driver SET eStatus = 'Deleted' WHERE iDriverId = '" . $hdn_del_id . "'";
	$obj->sql_query($query);
	header("Location:driver.php?success=1&var_msg=".$langage_lbl['LBL_DRIVER_DELETED']);
	// }
	// else
	// {
	// header("Location:driver.php?success=2");

	// }
}

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLname = isset($_POST['vLname']) ? $_POST['vLname'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = $generalobj->encrypt($vPassword);
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$tbl_name = "register_driver";

if (isset($_POST['submit'])) {

	$q = "INSERT INTO ";
	$where = '';

	if ($action == 'Edit') {
		$eStatus = ", eStatus = 'Inactive' ";
	} else {
		$eStatus = '';
	}

	if ($id != '') {
		$q = "UPDATE ";
		$where = " WHERE `iDriverId` = '" . $id . "'";
	}


	$query = $q . " `" . $tbl_name . "` SET
	`vName` = '" . $vName . "',
	`vLastName` = '" . $vLname . "',
	`vCountry` = '" . $vCountry . "',
	`vCode` = '" . $vCode . "',
	`vEmail` = '" . $vEmail . "',
	`vLoginId` = '" . $vEmail . "',
	`vPassword` = '" . $vPass . "',
	`vPhone` = '" . $vPhone . "',
	`vLang` = '" . $vLang . "',
	`eStatus` = '" . $eStatus . "',
	`iCompanyId` = '" . $iCompanyId . "'" . $where;

	$obj->sql_query($query);
	$id = ($id != '') ? $id : mysql_insert_id();
	header("Location:driver.php?id=" . $id . '&success=1&var_msg='.$langage_lbl['LBL_DRIVER_ADDED']);
}



if ($action == 'view') {

	//echo "<pre>";print_r($_SESSION);exit;
	// ADDED BY SEYYED AMIR
	$sql = "SELECT `iCompanyId` FROM `company` where `iParentId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
	$comp_childs = $obj->MySQLSelect($sql);
	$comp_list = $iCompanyId;

	foreach($comp_childs as $comp)
	{
		$comp_list .= ',' . $comp['iCompanyId'];
	}
	////////////////////////

	$sql = "SELECT * FROM register_driver where iCompanyId IN (" . $comp_list . ") and eStatus != 'Deleted'";

	if(isset($_GET['online']) || $_GET['type'] == 'online')
	$sql .= "AND vAvailability != 'Not Available' and vLongitude != ''";
	// $sql .= " AND tLastOnline > '" .date("Y-m-d H:i:s", strtotime("-60 min")). "'";
	//die($sql);
	$data_drv = $obj->MySQLSelect($sql);

	//echo "<pre>";print_r($vehicles);echo "</pre>";
	//echo "<pre>";print_r($_SESSION);exit;
}
if ($action == 'edit') {
	// echo "<script>document.getElementById('cancel-add-form').style.display='';document.getElementById('show-add-form').style.display='none';document.getElementById('add-hide-div').style.display='none';</script>";
}


// Mehrshad Added
//For Area
$sql="SELECT area.aid,area.sAreaNamePersian,area.mapCenter,area.mapZoom FROM `savar_area` as area,`company` as com where com.iCompanyId = {$iCompanyId} AND com.iAreaId = area.aid " . $conmanyareaadmin . "";
$db_area=$obj->MySQLSelect($sql);

$mapCenter = '';
$mapZoom = '14';
$iAreaId = '';
if(count($db_area) > 0)
{
	$db_area = $db_area[0];
	$iAreaId = $db_area['aid'];
	$mapCenter = $db_area['mapCenter'];
	//$mapZoom = $db_area['mapZoom'];
}

$sql = "SELECT `iCompanyId` FROM `company` where `iParentId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
$comp_childs = $obj->MySQLSelect($sql);
$comp_list = $iCompanyId;

foreach($comp_childs as $comp)
{
	$comp_list .= ',' . $comp['iCompanyId'];
}
////////////////////////

$sql = "SELECT * FROM register_driver where iCompanyId IN (" . $comp_list . ") and eStatus != 'Deleted'";

if(isset($_GET['online']))
$sql .= "AND vAvailability != 'Not Available' AND tLastOnline > '" .date("Y-m-d H:i:s", strtotime("-2 min")). "'";
//die($sql);
$data_drv_me = $obj->MySQLSelect($sql);
//echo "sssss" . $data_drv_me[1][vLongitude];
// echo "<pre>";print_r($data_drv_me);echo "</pre>";die();
// Mehrshad Added



?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
<link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet' />
	
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?php echo $SITE_NAME?> | Driver</title>
	<!-- Default Top Script and css -->
	<?php include_once("top/top_script.php");?>

	<!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
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
			<div class="page-contant-inner">
				<h2 class="header-page trip-detail driver-detail1"><?php echo $langage_lbl['LBL_DRIVER_COMPANY_TXT']; ?><a href="javascript:void(0);" onClick="add_driver_form();"><?php echo $langage_lbl['LBL_ADD_DRIVER_COMPANY_TXT']; ?></a> <a href="?type=online">رانندگان آنلاین</a> <a href="driver_requests.php">آخرین درخواست ها</a></h2>

				<!-- trips page -->
				<div class="trips-page trips-page1">

					<div class="col-lg-12">
						<div class="gmap-div gmap-div1" style="height:400px;"><div id="map" class="gmap3" style="height:400px;"></div></div>
					</div>

					<script type="text/javascript" src="admin/js/gmap3.js"></script>
					<script>

					var markers = [];
          var zoommap;
          var centermap;


          <?php if($mapCenter != '') : ?>
            zoommap = <?php echo  $mapZoom ?>;
            centermap = <?php echo  $mapCenter ?>;
          <?php else : ?>
            zoommap = 4;
            centermap = "[36.8446013, 54.433746]";
          <?php endif; ?>


          L.cedarmaps.accessToken = '4a0a95307ce57f099d59085bf0b36c46668124b2'; // See the note below on how to get an access token

          // Getting maps info from a tileJSON source
          var tileJSONUrl = 'https://api.cedarmaps.com/v1/tiles/cedarmaps.streets.json?access_token=' + L.cedarmaps.accessToken;

          // initilizing map into div#map
          var map = L.cedarmaps.map('map', tileJSONUrl, {
              scrollWheelZoom: true
          }).setView(centermap, zoommap);
          var LeafIcon = L.Icon.extend({
        		options: {

        		}
        	});

					//function initialize() {













//alert(<?php echo $comp_list;?>);

					<?php

					//Get Driver list




					/* START COUNT QUERY */
					if($_GET['id'] == ""){$whereicpmpany = " and iCompanyId=".$comp_list;

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
						 //die("ssssssssssss = ".$online_time_difference);
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
					      if($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived'){
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

					 var greenIcon = new LeafIcon({iconUrl: 'assets/img/savar/car_green.png'}),
		     		redIcon = new LeafIcon({iconUrl: 'assets/img/savar/car_red.png'}),
		     		orangeIcon = new LeafIcon({iconUrl: 'assets/img/savar/car_orn.png'});
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
									newAddr =  ".'"' . $newAddr . '"'.";
									newOnlineSt = ".'"' . $newOnlineSt . '"'.";
									newLat = '$newLat';
									newLong = '$newLong';



									if(newOnlineSt == 'Available') {L.marker([newLat, newLong], {icon: greenIcon}).bindPopup('<table style=font-weight:bold;><tr><td>نام </td><td>- '+newName+'</tr><tr><td>آخرین موقعیت </td><td>- '+newAddr+'</tr></table>').addTo(map);} else if(newOnlineSt == 'Ontrip' || newOnlineSt == 'Not Available') { L.marker([newLat, newLong], {icon: orangeIcon}).bindPopup('<table style=font-weight:bold;><tr><td>Name </td><td>- '+newName+'</tr><tr><td>آخرین موقعیت </td><td>- '+newAddr+'</tr></table>').addTo(map);}else { L.marker([newLat, newLong], {icon: redIcon}).bindPopup('<table style=font-weight:bold;><tr><td>نام </td><td>- '+newName+'</tr><tr><td>آخرین موقعیت </td><td>- '+newAddr+'</tr></table>').addTo(map); }";
										}}?>





















						for(x = 0 ; x < markers.length ; x++)
						{
							markers[x].setMap(null);
						}

						markers = [];

            <?php for ($i = 0; $i < count($data_drv); $i++) { ?>
              //var myLat = <?php echo  floatval($data_drv[$i]['vLatitude']) ?>; var myLng = <?php echo  floatval($data_drv[$i]['vLongitude']) ?>;



								<?php //foreach ($data_drv_me as $mark) : ?>

										<?php if($data_drv[$i]['vLatitude'] != '' && $data_drv[$i]['vLongitude'] != '') : ?>

											var myLat = <?php echo  floatval($data_drv[$i]['vLatitude']) ?>; var myLng = <?php echo  floatval($data_drv[$i]['vLongitude']) ?>;
//window.alert([myLat, myLng]);
											var image = {
												url: 'assets/img/savar/StartPoint.png',
												//anchor: new google.maps.Point(8,8)
											};
											//new google.maps.LatLng( floatval(drivers[i]['vLatitude']),floatval(drivers[i]['vLongitude']));

											<?php if ($data_drv[$i]['tLastOnline'] > date("Y-m-d H:i:s", strtotime("-60 min"))) : ?>


                        var greenIcon = new LeafIcon({iconUrl: 'assets/img/savar/car_green.png'});
                        //L.marker([myLat, myLng], {icon: greenIcon}).bindPopup("<?php echo $data_drv[$i]['vName']." ".$data_drv[$i]['vLastName'] ?>").addTo(map);
image.url = '';

											<?php endif; ?>

											<?php if ($data_drv[$i]['tLastOnline'] < date("Y-m-d H:i:s", strtotime("-60 min"))) : ?>

                      var  redIcon = new LeafIcon({iconUrl: 'assets/img/savar/car_red.png'});
                      //L.marker([myLat, myLng], {icon: redIcon}).bindPopup("<?php echo $data_drv[$i]['vName']." ".$data_drv[$i]['vLastName'] ?>").addTo(map);
                      image.url = '';

											<?php endif; ?>

											<?php if ($data_drv[$i]['vAvailability'] == 'Available' && $data_drv[$i]['vTripStatus'] == 'Active') : ?>
var  orangeIcon = new LeafIcon({iconUrl: 'assets/img/savar/car_orn.png'});
//L.marker([myLat, myLng], {icon: orangeIcon}).bindPopup("<?php echo $data_drv[$i]['vName']." ".$data_drv[$i]['vLastName'] ?>").addTo(map);
												image.url = '';
											<?php endif; ?>



                      //window.alert("[newLat, newLong]");
										//	marker.addListener('click', function() {
											//	var str = "";
												//str = "<?php echo $data_drv[$i]['vName']." ".$data_drv[$i]['vLastName'] ?>";
												//alert(str);
												// var infowindow = new google.maps.InfoWindow({
												//
												// 	content: str,
												// 	position:marker.getPosition(),
												// 	maxWidth: 300
												// });
										    //  infowindow.open(map);
							        //});
											//markers.push(marker);

										<?php endif ; ?>
								<?php //endforeach; ?>
                <?}?>
					//}

					</script>


					<?php if ($_REQUEST['success']==1) {?>
						<div class="alert alert-success alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<?php echo  $var_msg ?>
						</div>
						<?}else if($_REQUEST['success']==2){ ?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
							</div>
							<?php
						} else if(isset($_REQUEST['success']) && $_REQUEST['success']==0){?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								<?php echo  $var_msg ?>
							</div>
						<?php }
						?>
						<div class="trips-table trips-table-driver trips-table-driver-res">
							<div class="trips-table-inner">
								<div class="driver-trip-table">
									<table width="100%" border="0" cellpadding="0" cellspacing="0" id="dataTables-example">
										<thead>
											<tr>
												<th width="25%">ID</th>
												<th width="25%"><?php echo $langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?></th>
												<th width="20%"><?php echo $langage_lbl['LBL_DRIVER_EMAIL_LBL_TXT']; ?></th>
												<!--<th>Service Location</th>-->
												<th>آخرین زمان آنلاین</th>
												<th>تاریخ عضویت</th>
												<th width="10%"><?php echo $langage_lbl['LBL_MOBILE_NUMBER_HEADER_TXT']; ?></th>
												<th width="15%" style="width: 67px;"><?php echo "میانگین امتیاز"; ?></th>
												<th width="14%"><?php echo $langage_lbl['LBL_EDIT_DOCUMENTS_TXT']; ?></th>
												<th width="8%"><?php echo $langage_lbl['LBL_DRIVER_EDIT']; ?></th>
												<th width="8%"><?php echo $langage_lbl['LBL_DRIVER_DELETE']; ?></th>
											</tr>
										</thead>
										<tbody>
											<?php for ($i = 0; $i < count($data_drv); $i++) { ?>
												<?php
												if($data_drv[$i]['eStatus'] == "active")
												$color = "color:green;";
												else
												$color = "color:red";

												?>
												<tr class="gradeA">
													<td><?php echo  $data_drv[$i]['iDriverId']?></td>
													<td style="<?php echo $color ?>"><?php echo  $data_drv[$i]['vName'] . ' ' . $data_drv[$i]['vLastName']; ?></td>
													<td><?php echo  $data_drv[$i]['vEmail']; ?></td>
													<!--<td class="center"><?php echo  $data_drv[$i]['vServiceLoc']; ?></td>-->
													<?php
													$lastOnlineTime = strtotime($data_drv[$i]['tLastOnline']);
													$registerationDate = strtotime($data_drv[$i]['tRegistrationDate']);
													?>
													<td class="center">
														<?php if($data_drv[$i]['tLastOnline'] == '0000-00-00 00:00:00') :
															?>
															---
														<?php else : ?>
															<a href="./driver_online_times.php?id=<?php echo  $data_drv[$i]['iDriverId'] ?>">
																<?php echo  jdate("Y-m-d H:i:s",$lastOnlineTime); ?>
															</a>
															<?php if($data_drv[$i]['vLatitude'] != '') : ?>
																<a href="https://maps.google.com/?q=<?php echo  $data_drv[$i]['vLatitude']; ?>,<?php echo  $data_drv[$i]['vLongitude']; ?>" target="_blank" <?php if(time() - $lastOnlineTime < 1800) echo 'style="color:red"' ?>>نمایش</a>
																<?php endif; ?>
															<?php endif; ?>

														</td>
														<td><?php echo  jdate("Y-m-d",$registerationDate); ?></td>
														<td><?php echo  $data_drv[$i]['vPhone']; ?></td>
														<td><?php echo  $data_drv[$i]['vAvgRating']; ?></td>
														<td align="center" >
															<a href="driver_document_action.php?id=<?php echo  $data_drv[$i]['iDriverId']; ?>&action=edit">
																<button class="btn btn-primary">
																	<i class="icon-pencil icon-white"></i> <?php echo $langage_lbl['LBL_EDIT_DOCUMENTS_TXT']; ?>
																</button>
															</a>
														</td>
														<td align="center" >
															<a href="driver_action.php?id=<?php echo  $data_drv[$i]['iDriverId']; ?>&action=edit">
																<button class="btn btn-primary">
																	<i class="icon-pencil icon-white"></i> <?php echo $langage_lbl['LBL_DRIVER_EDIT']; ?>
																</button>
															</a>
														</td>
														<td align="center" >
															<form name="delete_form_<?php echo  $data_drv[$i]['iDriverId']; ?>" id="delete_form_<?php echo  $data_drv[$i]['iDriverId']; ?>" method="post" action="" class="margin0">
																<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo  $data_drv[$i]['iDriverId']; ?>">
																<input type="hidden" name="action" id="action" value="delete">
																<button type="button" class="btn btn-danger" onClick="confirm_delete('<?php echo  $data_drv[$i]['iDriverId']; ?>');">
																	<i class="icon-remove icon-white"></i> <?php echo $langage_lbl['LBL_DRIVER_DELETE']; ?>
																</button>
															</form>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>

									</div>  </div>
								</div>
								<!-- -->
								<?php //if(SITE_TYPE=="Demo"){?>
									<!--<div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
									This feature will be enabled in the main product we will provide you.</span> </div>
									<?php //}?> -->
									<!-- -->
								</div>
								<!-- -->
								<div style="clear:both;"></div>
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
					<script src="assets/js/jquery-ui.min.js"></script>
					<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
					<script type="text/javascript">

					$(document).ready(function () {
						$('#dataTables-example').dataTable({
							"language": {
								<?php echo Datatablelang?>
							},
						});
						//google.maps.event.addDomListener(window, 'load', initialize);
					});
					function confirm_delete(id)
					{
						bootbox.confirm("<?php echo $langage_lbl['LBL_CONFIRM_DELETE_DRIVER'];?>", function(result) {
							if(result){
								document.getElementById('delete_form_'+id).submit();
							}
						});
					}
					function changeCode(id)
					{
						var request = $.ajax({
							type: "POST",
							url: 'change_code.php',
							data: 'id=' + id,
							success: function (data)
							{
								document.getElementById("code").value = data;
								//window.location = 'profile.php';
							}
						});
					}

					function add_driver_form(){
						window.location.href = "driver_action.php";
					}
					</script>

					<script type="text/javascript">
					$(document).ready(function(){
						$("[name='dataTables-example_length']").each(function(){
							$(this).wrap("<em class='select-wrapper'></em>");
							$(this).after("<em class='holder'></em>");
						});
						$("[name='dataTables-example_length']").change(function(){
							var selectedOption = $(this).find(":selected").text();
							$(this).next(".holder").text(selectedOption);
						}).trigger('change');
					})
					</script>
					<!-- End: Footer Script -->
				</body>
				</html>
