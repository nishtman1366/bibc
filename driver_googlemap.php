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

	$sql = "SELECT * FROM register_driver where iCompanyId IN (" . $comp_list . ") and eStatus != 'Deleted' ";

	if(isset($_GET['online']))
	$sql .= "AND vAvailability != 'Not Available' AND tLastOnline > '" .date("Y-m-d H:i:s", strtotime("-2 min")). "'";
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
$sql="SELECT area.aid,area.sAreaNamePersian,area.mapCenter,area.mapZoom FROM `savar_area` as area,`company` as com where com.iCompanyId = {$iCompanyId} AND com.iAreaId = area.aid";
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

// echo "<pre>";print_r($data_drv_me);echo "</pre>";die();
// Mehrshad Added



?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
	<meta charset="UTF-8">
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
				<h2 class="header-page trip-detail driver-detail1"><?php echo $langage_lbl['LBL_DRIVER_COMPANY_TXT']; ?><a href="javascript:void(0);" onClick="add_driver_form();"><?php echo $langage_lbl['LBL_ADD_DRIVER_COMPANY_TXT']; ?></a> <a href="?online">رانندگان آنلاین</a> <a href="driver_requests.php">آخرین درخواست ها</a></h2>

				<!-- trips page -->
				<div class="trips-page trips-page1">

					<div class="col-lg-12">
						<div class="gmap-div gmap-div1" style="height:400px;"><div id="map-canvas" class="gmap3" style="height:400px;"></div></div>
					</div>

					<script type="text/javascript" src="admin/js/gmap3.js"></script>
					<script>

					var markers = [];

					function initialize() {

						var geocoder = new google.maps.Geocoder();

						<?php if($mapCenter != '') : ?>
						var mapOptions = {
							zoom: <?php echo  $mapZoom ?>,
							center: <?php echo  $mapCenter ?>
						};
						<?php else : ?>
						var mapOptions = {
							zoom: 4,
							center: new google.maps.LatLng('36.8446013', '54.433746')
						};
						<?php endif; ?>

						var map = new google.maps.Map(document.getElementById('map-canvas'),
						mapOptions);

						for(x = 0 ; x < markers.length ; x++)
						{
							markers[x].setMap(null);
						}
						markers = [];

								<?php foreach ($data_drv_me as $mark) : ?>

										<?php if($mark['vLatitude'] != '' && $mark['vLongitude'] != '') : ?>

											var myLatLng = {lat: <?php echo  floatval($mark['vLatitude']) ?> , lng: <?php echo  floatval($mark['vLongitude']) ?>};
											var image = {
												url: 'assets/img/savar/StartPoint.png',
												anchor: new google.maps.Point(8,8)
											};
											//new google.maps.LatLng( floatval(drivers[i]['vLatitude']),floatval(drivers[i]['vLongitude']));

											<?php if ($mark['tLastOnline'] > date("Y-m-d H:i:s", strtotime("-60 min"))) : ?>

												image.url = 'assets/img/savar/car_green.png'

											<?php endif; ?>

											<?php if ($mark['tLastOnline'] < date("Y-m-d H:i:s", strtotime("-60 min"))) : ?>

												image.url = 'assets/img/savar/car_red.png'
											<?php endif; ?>

											<?php if ($mark['vAvailability'] == 'Available' && $mark['vTripStatus'] == 'Active') : ?>

												image.url = 'assets/img/savar/car_orn.png'
											<?php endif; ?>

											var marker = new google.maps.Marker({
													position: myLatLng,
													map: map,
													animation: google.maps.Animation.DROP,
													icon: image,
													title:'hello'
											});
											marker.addListener('click', function() {
												var str = "";
												str = "<?php echo $mark['vName']." ".$mark['vLastName'] ?>";
												alert(str);
												// var infowindow = new google.maps.InfoWindow({
												//
												// 	content: str,
												// 	position:marker.getPosition(),
												// 	maxWidth: 300
												// });
										    //  infowindow.open(map);
							        });
											markers.push(marker);

										<?php endif ; ?>
								<?php endforeach; ?>
					}

					</script>
					<script
						async defer src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>&callback=initialize">
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
						google.maps.event.addDomListener(window, 'load', initialize);
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
