<?php
include_once('common.php');

require_once(TPATH_CLASS .'savar/jalali_date.php');

$generalobj->check_member_login();
$abc = 'admin,driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$start = @jdate("Y");
$end = '1370';

$script="Vehicle";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
if($action == 'Edit'){
	$action_PS=$langage_lbl['LBL_EDIT_VEHICLE_TXT'];
}else{
	$action_PS=$langage_lbl['LBL_ADD_VEHICLE_TEXT'];
}
$tbl_name = 'driver_vehicle';
if ($_SESSION['sess_user'] == 'driver') {
	$sql = "select iCompanyId from `register_driver` where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
	$db_usr = $obj->MySQLSelect($sql);
	$iCompanyId = $db_usr[0]['iCompanyId'];
}
if ($_SESSION['sess_user'] == 'company') {
	$iCompanyId = $_SESSION['sess_iCompanyId'];
	$sql = "select * from register_driver where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
	$db_drvr = $obj->MySQLSelect($sql);
}
$sql = "select * from driver_vehicle where iDriverVehicleId = '" . $id . "' ";
$db_mdl = $obj->MySQLSelect($sql);


$sql = "select iAreaId,iParentId from `company` where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
$db_areaid = $obj->MySQLSelect($sql);

$company_area_id = 0;
if(count($db_areaid) > 0)
{
	$company_area_id = $db_areaid[0]['iAreaId'];
}

// set all variables with either post (when submit) either blank (when insert)
$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : '';

$vLicencePlate_city = isset($_POST['vLicencePlate_city']) ? $_POST['vLicencePlate_city'] : '';
$vLicencePlate_place2 = isset($_POST['vLicencePlate_place2']) ? $_POST['vLicencePlate_place2'] : '';
$vLicencePlate_alphabet = isset($_POST['vLicencePlate_alphabet']) ? $_POST['vLicencePlate_alphabet'] : '';
$vLicencePlate_place1 = isset($_POST['vLicencePlate_place1']) ? $_POST['vLicencePlate_place1'] : '';

$vLicencePlate = "$vLicencePlate_place2   $vLicencePlate_city $vLicencePlate_alphabet  $vLicencePlate_place1";
$vLicencePlate = trim($vLicencePlate);
//IRAN|00|A|000|CT
$vLicencePlate_local = "IRAN|$vLicencePlate_place1|$vLicencePlate_alphabet|$vLicencePlate_place2|$vLicencePlate_city";


$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : '';
$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : '';
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : '';
$iColor = isset($_POST['iColor']) ? $_POST['iColor'] : '';

if(isset($_POST['iYear']))
{
	// added by seyyed amir
	$mdate = jalali_to_gregorian($_POST['iYear'],6,01);
	$iYear = $_POST['iYear'] = $mdate[0];
}

$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : $_SESSION['sess_iUserId'];
$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$sql = "SELECT * from make WHERE eStatus='Active' ORDER BY vMake ASC";
$db_make = $obj->MySQLSelect($sql);

$error = '';
if (isset($_POST['submit'])) {

	if($vLicencePlate != '' && check_plate() == false)
	{
		$error = $langage_lbl['LBL_LICENCE_PLATE_EXIST'];
	}
	if($vLicencePlate_city == '' || $vLicencePlate_place2 == '' ||  $vLicencePlate_alphabet == '' ||  $vLicencePlate_place1 == '' )
	{
		$error = $langage_lbl['LBL_LICENCE_PLATE_EMPTY'];
	}


	if($error != '')
	{
		// if(SITE_TYPE=='Demo' && $action=='Edit')
		// {
		// $error_msg="Edit / Delete Record Feature has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.";
		// header("Location:form.php?id=" .$id."&error_msg=".$error_msg."&success=2");
		// exit;
		// }
		//echo "<pre>";print_r($_POST);exit;
		if(!isset($_REQUEST['vCarType'])) {
			$error_msg = $langage_lbl['LBL_PLEASE_SELECT_CAR_TYPE'];
			header("Location:form.php?id=".$id."&error_msg=".$error_msg."&success=2");
			exit;
		}

		if($APP_TYPE == 'UberX'){

			$vLicencePlate ='My Services';
		}else{
			$vLicencePlate = $vLicencePlate;
		}

		$dsql="";
		if($id!='')
		{
			$dsql=" and iDriverVehicleId != '$id'";
		}
		$sql="select * from driver_vehicle where vLicencePlate='".$vLicencePlate."' and eStatus!='Deleted' ".$dsql;
		$db_li_plate=$obj->MySQLSelect($sql);
		//echo "<pre>";print_r($db_li_plate);exit;
		if(count($db_li_plate)>0){
			$error_msg= $langage_lbl['LBL_LICENCE_PLATE_EXIST'];
			header("Location:form.php?id=".$id."&error_msg=".$error_msg."&success=2");
			exit;
		}
		else
		{
			$q = "INSERT INTO ";
			$where = '';
			//echo "<pre>";print_R($_REQUEST);exit;

			if ($action == 'Edit') {
				$str = ' ';
			} else {
				if(SITE_TYPE=='Demo')
				$str = ", eStatus = 'Active' ";
				else
				$str = ", eStatus = 'Inactive' ";

			}

			$cartype = implode(",", $_REQUEST['vCarType']);
			if ($id != '') {
				$q = "UPDATE ";
				$where = " WHERE `iDriverVehicleId` = '" . $id . "'";
			}


			$query = $q . " `" . $tbl_name . "` SET
			`iModelId` = '" . $iModelId . "',
			`vLicencePlate` = '" . $vLicencePlate . "',
			`vLicencePlate_local` = '" . $vLicencePlate_local . "',
			`iYear` = '" . $iYear . "',
			`iColor` = '" . $iColor . "',
			`iMakeId` = '" . $iMakeId . "',
			`iCompanyId` = '" . $iCompanyId . "',
			`iDriverId` = '" . $iDriverId . "',
			`vCarType` = '" . $cartype . "' $str"
			. $where;


			//die($query);
			$obj->sql_query($query);
			$id = ($id != '') ? $id : mysql_insert_id();

			if($action=="Add")
			{
				$sql="SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
				$db_compny = $obj->MySQLSelect($sql);

				$sql="SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
				$db_status = $obj->MySQLSelect($sql);

				$maildata['EMAIL'] =$db_status[0]['vEmail'];
				$maildata['NAME'] = $db_status[0]['vName']." ".$db_status[0]['vLastName'];
				//$maildata['LAST_NAME'] = $db_compny[0]['vName'];
				//$maildata['DETAIL']="Your Vehicle is Added For ".$db_compny[0]['vCompany']." and will process your document and activate your account ";
				$maildata['DETAIL']=$langage_lbl['LBL_THANKS_ADDING_VEHICLE']."<br/>".$langage_lbl['LBL_VEHICLE_EMAIL_TEXT']."<br/>".$langage_lbl['LBL_VEHICLE_EMAIL_TEXT2'];

				$generalobj->send_email_user("VEHICLE_BOOKING",$maildata);
				$maildata['DETAIL']="Vehicle is Added For ".$db_compny[0]['vCompany']." . Below is link to activate.<br>
				<p><a href='".$tconfig["tsite_url"]."admin/form.php?id=$id'>Active this Vehicle</a></p>";
				$generalobj->send_email_user("VEHICLE_BOOKING_ADMIN",$maildata);
				//print_R($maildata);


				// added by seyyed amir
				$iDriverId;
				$iDriverVehicleId = $id;

				$query = "UPDATE `register_driver` SET `iDriverVehicleId` = {$iDriverVehicleId} WHERE `iDriverId` = {$iDriverId} AND `iDriverVehicleId` = 0";
				$obj->sql_query($query);
			}

			$var_msg = $langage_lbl['LBL_RECORD_INSERTED'];
			if($action != "Add"){
				$var_msg = $langage_lbl['LBL_Record_Updated_successfully.'];
			}
			header("Location:vehicle.php?success=1&var_msg=".$var_msg);
		}
	}
}

// for Edit
if ($action == 'Edit') {
	$sql = "SELECT * from  $tbl_name where iDriverVehicleId = '" . $id . "'";
	$db_data = $obj->MySQLSelect($sql);
	$vLabel = $id;
	if (count($db_data) > 0) {
		foreach ($db_data as $key => $value) {
			$iMakeId = $value['iMakeId'];
			$iModelId = $value['iModelId'];
			$vLicencePlate = $value['vLicencePlate'];

			$vLicencePlate_local = $value['vLicencePlate_local'];

			$plate_split = explode('|',$vLicencePlate_local);
			if(count($plate_split) > 0 && $plate_split[0] = "IRAN")
			{
				$vLicencePlate_place1 = $plate_split[1];
				$vLicencePlate_alphabet = $plate_split[2];
				$vLicencePlate_place2 = $plate_split[3];
				$vLicencePlate_city = $plate_split[4];

				$iYear = $value['iYear'];
				$iColor = $value['iColor'];

				// added by seyyed amir
				$mdate = gregorian_to_jalali($iYear,9,1);
				$iYear = $_POST['iYear'] = $mdate[0];

				$eCarX = $value['eCarX'];
				$eCarGo = $value['eCarGo'];
				$iDriverId = $value['iDriverId'];
				$vCarType = $value['vCarType'];
			}

		}
	}
}
$vCarTyp = explode(",", $vCarType);

$Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;
if($Vehicle_type_name == "Ride-Delivery"){

	$vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver' or eType ='SchoolServices')  ORDER BY `vehicle_type`.`vSavarArea` ASC";
	$vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);


}else{

	if($APP_TYPE == 'UberX'){

		$vehicle_type_sql = "SELECT vt.*,vc.* from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='".$Vehicle_type_name."' ";
		$vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);

	}else{

		$vehicle_type_sql = "SELECT * from  vehicle_type  where eType='".$Vehicle_type_name."'  ORDER BY `vehicle_type`.`vSavarArea` ASC";
		$vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);

	}


}

//echo "</pre>"; print_r($vehicle_type_data); exit;

$q = "SELECT * FROM savar_area;";
$area_array  = $obj->MySQLSelect($q);

$areas = array();
$areas [0] = "بدون دسته";

foreach($area_array as  $area)
{
	$areas[$area['aId']] =  $area['sAreaName'] . '(' .$area['sAreaNamePersian'] .')';
}



function check_plate($plate,$id)
{
	if($plate != ""){
		$ssql1="";
		if($id!="")
		{
			$ssql1=" and iDriverVehicleId != '$id'";
		}
		$sql="select * from driver_vehicle where vLicencePlate='$plate' and eStatus!='Deleted'".$ssql1;
		$db_veh_det= $obj->MySQLSelect($sql);

		if(count($db_veh_det)>0)
		{
			return false;
		}else {
			return true;
		}
	}

	return false;
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?php echo $SITE_NAME?> | <?php echo  $action_PS; ?></title>
	<!-- Default Top Script and css -->
	<?php include_once("top/top_script.php");?>
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
		<link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
		<!-- End: Top Menu-->
		<!-- contact page-->
		<div class="page-contant">
			<div class="page-contant-inner page-trip-detail">
				<h2 class="header-page trip-detail driver-detail1"><?php echo  $action_PS; ?><a href="vehicle.php"><img src="assets/img/arrow-white.png" alt="" /><?php echo $langage_lbl['LBL_BACK_MY_TAXI_LISTING']; ?></a></h2>
				<!-- trips detail page -->
				<div class="driver-add-vehicle">

					<?php if($error != '') { ?>
						<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<?php echo  $error ?>
						</div>
					<?php } ?>
					<?php if($success == 1) { ?>
						<div class="alert alert-success alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<?php if($var_msg == ""){
								echo $langage_lbl['LBL_Record_Updated_successfully.'];
							}else{
								echo $var_msg;
							}
							?>

						</div>
					<?php }else if($success == 2){?>
						<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<?php echo  isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : ' '; ?>
						</div>
						<?} ?>
						<form method="post" action="">
							<input type="hidden" name="id" value="<?php echo  $id; ?>"/>
							<?php if($APP_TYPE != 'UberX'){ ?>

								<span>
									<b>
										<label><?php echo $langage_lbl['LBL_SELECT_MAKE']?></label>
										<select name = "iMakeId" id="iMakeId" class="custom-select-new" data-key="<?php echo $langage_lbl['LBL_CHOOSE_MAKE']; ?>" onChange="get_model(this.value, '')" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_ITEM'];?>')" oninput="setCustomValidity('')">
											<option value=""><?php echo $langage_lbl['LBL_CHOOSE_MAKE']; ?></option>
											<?php for ($j = 0; $j < count($db_make); $j++) { ?>
												<option value="<?php echo  $db_make[$j]['iMakeId'] ?>" <?php if ($iMakeId == $db_make[$j]['iMakeId']) { ?> selected <?php } ?>><?php echo  $db_make[$j]['vMake'] ?></option>
											<?php } ?>
										</select>
									</b>
									<b id="carmdl">
										<label><?php echo $langage_lbl['LBL_SELECT_VEHICLE_MODEL']?></label>
										<select name = "iModelId" id="iModelId" data-key="<?php echo $langage_lbl['LBL_CHOOSE_VEHICLE_MODEL']; ?>" class="custom-select-new validate[required]" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_ITEM'];?>')" oninput="setCustomValidity('')">
											<option value=""><?php echo $langage_lbl['LBL_CHOOSE_VEHICLE_MODEL']; ?> </option>
											<?php for ($j = 0; $j < count($db_model); $j++) { ?>
												<option value="<?php echo  $db_model[$j]['iModelId'] ?>" <?php if ($iModelId == $db_model[$j]['iModelId']) { ?> selected <?php } ?>><?php echo  $db_model[$j]['vModel'] ?></option>
											<?php } ?>
										</select>
									</b>
								</span>
								<span>
									<b>
										<label>Color</label>

										<input type="text" class="form-control" name="iColor" id="iColor" value="<?php echo  $iColor ?>" />
									</b>
								</span>
								<span>
									<b>
										<label><?php echo $langage_lbl['LBL_SELECT_YEAR']?></label>
										<select name = "iYear" data-key="<?php echo $langage_lbl['LBL_CHOOSE_YEAR']; ?>" id="iYear" class="custom-select-new" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_YEAR'];?>')" oninput="setCustomValidity('')">
											<option value=""><?php echo $langage_lbl['LBL_CHOOSE_YEAR']; ?> </option>
											<?php for ($j = $start; $j >= $end; $j--) { ?>
												<option value="<?php echo  $j ?>" <?php if($iYear == $j){?> selected <?} ?>><?php echo  $j ?></option>
											<?php } ?>
										</select>
									</b> 
									<b style="display:none">
										<label><?php echo $langage_lbl['LBL_LICENCE_PLATE_TXT']?></label>
										<input type="text" class="form-control" name="vLicencePlate"  id="vLicencePlate" value="<?php echo  $vLicencePlate; ?>" placeholder="<?php echo $langage_lbl['LBL_LICENCE_PLATE_TXT']; ?>" onblur="check_licence_plate(this.value,'<?php echo $id?>')"  >
										<span id="plate_warning" class="error-text"></span>
									</b>

									<b>
										<label style="width: 100%"><?php echo $langage_lbl['LBL_LICENCE_PLATE_TXT']?></label>
										<input type="text" class="form-control pelakinput" name="vLicencePlate_city"  id="vLicencePlate_city" value="<?php echo  $vLicencePlate_city; ?>" placeholder="کد شهر" required >
										<input type="text" class="form-control pelakinput" name="vLicencePlate_place2"  id="vLicencePlate_place2" value="<?php echo  $vLicencePlate_place2; ?>" placeholder="000"  required >
										<input type="text" class="form-control pelakinput" name="vLicencePlate_alphabet"  id="vLicencePlate_alphabet" value="<?php echo  $vLicencePlate_alphabet; ?>" placeholder="الف"  required >
										<input type="text" class="form-control pelakinput" name="vLicencePlate_place1"  id="vLicencePlate_place1" value="<?php echo  $vLicencePlate_place1; ?>" placeholder="00" required >
										<span id="plate_warning" class="error-text"></span>
									</b>

								</span>
								<span>

								</span>

								<?php if($_SESSION['sess_user'] == 'company') {?>
									<span>
										<b>
											<label><?php echo $langage_lbl['LBL_SELECT_DRIVER']?></label>
											<select name = "iDriverId" id="iDriverId" class="custom-select-new" required>
												<option value=""><?php echo $langage_lbl['LBL_CHOOSE_DRIVER']; ?></option>
												<?php for ($j = 0; $j < count($db_drvr); $j++) { ?>
													<option value="<?php echo  $db_drvr[$j]['iDriverId'] ?>" <?php if($db_drvr[$j]['iDriverId'] == $iDriverId){?> selected <?} ?>><?php echo  $db_drvr[$j]['vName'] . ' ' . $db_drvr[$j]['vLastName'] ?></option>
												<?php } ?>
											</select>
										</b>
									</span>

								<?php } ?>
								<h3><?php echo $langage_lbl['LBL_Car_Type']; ?></h3>
							<?php } ?>
							<div class="car-type" dir="ltr">

								<?php $lastArea = -1 ; ?>
								<ul>
									<?php
									foreach ($vehicle_type_data as $key => $value) {
										if($company_area_id != $value['vSavarArea'])
										continue;
										if($lastArea != $value['vSavarArea'])
										{
											$lastArea = $value['vSavarArea'];
											echo '<li class="area-title">' . $areas[$value['vSavarArea']] . '</li>';
										}
										if($APP_TYPE == 'UberX'){

											$vName = 'vCategory_'.$_SESSION['sess_lang'];
											$vehicle_typeName = $value[$vName].'-'.$value['vVehicleType'];

										}else{
											$vehicle_typeName = $value['vVehicleType_'.$_SESSION['sess_lang']];
										}?>
										<li>
											<b><?php echo $vehicle_typeName; ?></b>
											<div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
												<input type="checkbox" class="chk" name="vCarType[]" <?php if(in_array($value['iVehicleTypeId'],$vCarTyp)){?>checked<?php } ?> value="<?php echo $value['iVehicleTypeId'] ?>"/>
											</div>
										</li>
									<?php }?>
								</ul>
								<strong><input type="submit" class="save-vehicle" name="submit" id="submit" value="<?php echo  $action_PS; ?>"> </strong>
							</div>
							<!-- -->
						</form>
					</div>
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
		<script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
		<?php if ($action == 'Edit') { ?>
			<script>
			window.onload = function () {
				get_model('<?php echo $db_mdl[0]['iMakeId']; ?>', '<?php echo $db_mdl[0]['iModelId']; ?>');
			};
			</script>
			<?} ?>
			<script>
			function get_model(model, modelid) {
				//alert('dfdf');
				//$("#carmdl").html('Wait...');
				var request = $.ajax({
					type: "POST",
					url: 'ajax_find_model_new.php',
					data: "action=get_model&model=" + model + "&iModelId=" + modelid,
					success: function (data) {
						$("#iModelId").empty().append(data);
						var selectedOption = $('#iModelId').find(":selected").text();
						$('#iModelId').next(".holder").text(selectedOption);
					}
				});

				request.fail(function (jqXHR, textStatus) {
					alert("Request failed: " + textStatus);
				});
			}

			function check_licence_plate(plate,id1){
				if(plate != ""){
					var request= $.ajax({
						type: "POST",
						url: 'ajax_find_plate.php',
						data: "plate="+plate+"&id="+id1,
						success: function (data){
							if($.trim(data) == 'yes') {
								$('input[type="submit"]').removeAttr('disabled');
								$("#plate_warning").html("");
							}else {
								$("#plate_warning").html(data);
								$('input[type="submit"]').attr('disabled','disabled');
							}
						}
					});
				}
				else{
					$("#plate_warning").html('<?php echo $langage_lbl['LBL_PLEASE_ENTER_LICENCE_PLATE'];?>');
					$('input[type="submit"]').attr('disabled','disabled');
				}
			}

		</script>

		<!-- End: Footer Script -->
	</body>
	</html>
