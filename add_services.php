<?php
	include_once('common.php');
	$generalobj->check_member_login();
	$abc = 'admin,driver,company';
	$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$generalobj->setRole($abc, $url);

	$start = @date("Y");
	$end = '1970';

	//print_r($_SESSION['sess_iUserId']); exit;

	$script="My Availability";
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
	$action = ($id != '') ? 'Edit' : 'Add';
	$tbl_name = 'driver_vehicle';
	$tbl_name1 = 'service_pro_amount';
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
	$chngamt="Disabled";
	if($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes"){
		$chngamt="Enabled";
	}
	
	// $sql = "select * from driver_vehicle where iDriverVehicleId = '" . $id . "' ";
	// $db_mdl = $obj->MySQLSelect($sql);
	//echo "<pre>";print_r($_POST);exit;

	// set all variables with either post (when submit) either blank (when insert)
	$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : '';
	$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : '';
	$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : '';
	$fAmount = isset($_POST['fAmount']) ? $_POST['fAmount'] : '';
	$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : '';
	$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
	$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : $_SESSION['sess_iUserId'];
	$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
	$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
	
	$sql = "select iDriverVehicleId from driver_vehicle where iDriverId = '" . $iDriverId . "' ";
	$db_drv_veh=$obj->MySQLSelect($sql);
	//echo "<pre>";print_r($db_drv_veh);exit;
	
	$sql = "SELECT * from make WHERE eStatus='Active' ORDER BY vMake ASC";
	$db_make = $obj->MySQLSelect($sql);
	
	if (isset($_POST['submit1'])) {
		//echo "<pre>";print_r($_POST);exit;
		if(SITE_TYPE=='Demo' && $action=='Edit')
	{
		$error_msg="Edit / Delete Record Feature has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.";
		 header("Location:add_services.php?id=" .$id."&error_msg=".$error_msg."&success=2");
		 exit;
	}

		if(!isset($_REQUEST['vCarType'])) {
			$error_msg = "You must select at least one car type!";
			header("Location:add_services.php?id=".$id."&error_msg=".$error_msg."&success=2");
			exit;
		}

		if($APP_TYPE == 'UberX'){

			$vLicencePlate ='My Services';
		}else{
			$vLicencePlate = $vLicencePlate;
		}		
		
		if(SITE_TYPE=='Demo'){
			$str = ", eStatus = 'Active' ";

		}else{
			$str = ", eStatus = 'Inactive' ";

		}		

		$cartype = implode(",", $_REQUEST['vCarType']);
		//if ($id != '') {
			$q = "UPDATE ";
			$where = " WHERE `iDriverId` = '" .$_SESSION['sess_iUserId']. "'";
		//}
		  $query = $q . " `" . $tbl_name . "` SET
		`iModelId` = '" . $iModelId . "',
		`vLicencePlate` = '" . $vLicencePlate . "',
		`iYear` = '" . $iYear . "',
		`iMakeId` = '" . $iMakeId . "',
		`iCompanyId` = '" . $iCompanyId . "',
		`iDriverId` = '" . $iDriverId . "',
		`vCarType` = '" . $cartype . "' $str"
		. $where;
		
	
		$obj->sql_query($query);
		$id = ($id != '') ? $id : mysql_insert_id();
		
		if($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes"){
			
			$amt_man=$fAmount;
			//echo "<pre>";print_r($_POST);print_r($vCarType);print_r($fAmount);exit;
			// for($a=0;$a<count($vCarType);$a++)
			// {$type=$vCarType[$a];
				// foreach($amt_man as $key1=>$value1)	
				// {
					// if($key1==$type && $value1 == "")
					// {
						// $error_msg="Please Enter Amount.";
						// header("Location:add_services.php?success=2&error_msg=".$error_msg);
						// exit;}
					// }
			// }
		
			$sql = "select iServProAmntId,iDriverVehicleId from ".$tbl_name1." where iDriverVehicleId = '" . $db_drv_veh[0]['iDriverVehicleId'] . "' ";
			$db_drv_price=$obj->MySQLSelect($sql);
			//echo "<pre>";print_r($db_drv_veh);//exit;
			if(count($db_drv_price) > 0){
				$sql="delete from ".$tbl_name1." where iDriverVehicleId='".$db_drv_price[0]['iDriverVehicleId']."'";
				$obj->sql_query($sql);	
			}
			
			foreach($amt_man as $key=>$value)
			{
				if($value != ""){
					$q = "Insert Into ";
					$query = $q . " `" . $tbl_name1 . "` SET
					`iDriverVehicleId` = '" . $db_drv_veh[0]['iDriverVehicleId'] . "',
					`iVehicleTypeId` = '" . $key . "',
					`fAmount` = '" . $value . "'";  
					$db_parti_price=$obj->sql_query($query);
				}
			}
			
		}
		if($action=="Add")
		{
			$sql="SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
			$db_compny = $obj->MySQLSelect($sql);

			$sql="SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
			$db_status = $obj->MySQLSelect($sql);

			$maildata['EMAIL'] =$db_status[0]['vEmail'];
			$maildata['NAME'] = $db_status[0]['vName'];
			//$maildata['LAST_NAME'] = $db_compny[0]['vName'];
			$maildata['DETAIL']="Your Vehicle is Added For ".$db_compny[0]['vName']." and will process your document and activate your account ";

			$generalobj->send_email_user("VEHICLE_BOOKING",$maildata);
			//print_R($maildata);
		}
		$var_msg="Record Update successfully.";
		header("Location:add_services.php?success=1&var_msg=".$var_msg);
	//}
	}

	// for Edit
	//if ($action == 'Edit') {

		$sql = "SELECT t.*,t1.* from  $tbl_name as t left join $tbl_name1 t1
				on t.iDriverVehicleId=t1.iDriverVehicleId
				where t.iDriverId = '" . $iDriverId . "'";
		$db_data = $obj->MySQLSelect($sql);
		//echo "<pre>";print_r($db_data);exit; 
		$vLabel = $id;
		if (count($db_data) > 0) {
			foreach ($db_data as $key => $value) {
				$iMakeId = $value['iMakeId'];
				$iModelId = $value['iModelId'];
				$vLicencePlate = $value['vLicencePlate'];
				$iYear = $value['iYear'];
				$eCarX = $value['eCarX'];
				$eCarGo = $value['eCarGo'];
				$iDriverId = $value['iDriverId'];
				$vCarType = $value['vCarType'];
				$fAmount[$value['iVehicleTypeId']]=$value['fAmount'];
			}
		}//echo "<pre>";print_r($fAmount);exit;
	//}
	//echo "<pre>";print_r($vCarType);exit;
	$vCarTyp = explode(",", $vCarType);
	//echo "<pre>";print_r($vCarTyp);exit;
	$Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;	
	if($Vehicle_type_name == "Ride-Delivery"){

		$vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver')";
		$vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);


	}else{

		if($APP_TYPE == 'UberX'){

			$vehicle_type_sql = "SELECT vt.vVehicleType,vc.vCategory_".$_SESSION['sess_lang'].",vc.iVehicleCategoryId from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='".$Vehicle_type_name."' GROUP BY vc.iVehicleCategoryId";
			$vehicle_type_dataOld = $obj->MySQLSelect($vehicle_type_sql);
			//echo "<pre>"; print_r($vehicle_type_dataOld); exit;
			$vehicle_type_data = array();
			$i = 0;
			foreach($vehicle_type_dataOld as $vData) {
				$vehicle_type_sql1 = "SELECT vt.*,vc.* from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='".$Vehicle_type_name."' and vc.iVehicleCategoryId = '".$vData['iVehicleCategoryId']."'";;
				$vehicle_type_dataNew = $obj->MySQLSelect($vehicle_type_sql1);
				//echo "<pre>"; print_r($vehicle_type_dataNew); exit;

				$vehicle_type_data[$i] = $vData;
				$vehicle_type_data[$i]['newData'] = $vehicle_type_dataNew;
				$i++;
			}

		}else{

			$vehicle_type_sql = "SELECT * from  vehicle_type  where eType='".$Vehicle_type_name."' ";
			$vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
		}	

		
	}	

	//echo "<pre>"; print_r($vehicle_type_data); exit;



	
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $SITE_NAME?> | <?php echo $langage_lbl['LBL_MY_AVAILABILITY'];?></title>
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
		      	<h2 class="header-page trip-detail driver-detail1"><?php echo $langage_lbl['LBL_MY_AVAILABILITY'];?></h2>
		      	<!-- trips detail page -->
		      	<div class="driver-add-vehicle"> 
		      	<?php if($success == 1) { ?>
					<div class="alert alert-success alert-dismissable">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						<?php echo $langage_lbl['LBL_Record_Updated_successfully.']; ?>
					</div>
					<?php }else if($success == 2){?>
					<div class="alert alert-danger alert-dismissable">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						<?php echo  isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : ' '; ?>
					</div>
				<?} ?>
					<form name="frm1" method="post" action="">
						<input type="hidden" name="id" value="<?php echo  $id; ?>"/>
		    			<div class="car-type add-car-services-hatch add-services-hatch add-services-taxi">				          
				          	<ul>
		      				<?php		      				
								foreach ($vehicle_type_data as $value1) {

									if($APP_TYPE == 'UberX'){

										$vName = 'vCategory_'.$_SESSION['sess_lang'];
										$vehicleName =$value1[$vName];
									}else{
										$vehicle_typeName = $value1['vVehicleType'];
									}

									 ?>
							
								<fieldset>
								  <legend><strong><?php echo $vehicleName; ?></strong></legend>
								  <?php foreach($value1['newData'] as $value) {
								  	$VehicleName1 = 'vVehicleType_'.$_SESSION['sess_lang'];
									
									
									if($value['eFareType'] == 'Fixed'){
									$eFareType = 'Fixed';
										
									}else if($value['eFareType'] == 'Regular'){
									$eFareType = 'Per Min'; 
									}else{
									$eFareType = '';
									
									}

								  $vehicle_typeName =$value[$VehicleName1];
								  ?>
									<li>
										<b><?php echo $vehicle_typeName; ?></b>
										<div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
											<input type="checkbox" <?php if($chngamt == "Enabled"){ ?>onchange="check_box_value(this.value);" <?php } ?> id="vCarType1_<?php echo $value['iVehicleTypeId'] ?>" class="chk" name="vCarType[]" <?php if(in_array($value['iVehicleTypeId'],$vCarTyp)){?>checked<?php } ?> value="<?php echo $value['iVehicleTypeId'] ?>" />
										</div>
										<?php 
										if($chngamt == "Enabled"){
											$p001="style='display:none;'";
											if(in_array($value['iVehicleTypeId'],$vCarTyp)){
												$p001="style='display:block;'";
											}
											?>
										<div class="hatchback-search" id="amt1_<?php echo $value['iVehicleTypeId'] ?>" <?php echo $p001;?>>
											<input type="hidden" name="desc" id="desc_<?php echo $value['iVehicleTypeId']?>" value="<?php echo $value[$VehicleName1] ?>">
											<input class="form-control" type="text" name="fAmount[<?php echo $value['iVehicleTypeId']?>]" value="<?php echo $fAmount[$value['iVehicleTypeId']]?>" placeholder="Enter Amount for <?php echo $value[$VehicleName1] ?>" id="fAmount_<?php echo $value['iVehicleTypeId']?>"><label><?php echo $eFareType;?></label>
											</div>
										<?php
										}
										?>
									</li>
								  <?php } ?>
								</fieldset>
							
						<?php }?>
							</ul>
		      				<strong><input type="submit" class="save-vehicle" name="submit1" id="submit1" value="Submit" <?php if($chngamt == "Enabled"){ ?>onclick="return check_empty();"<?php } ?>> </strong>

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
    
<script>
		function check_box_value(val1)
		{
			if($('#vCarType1_'+val1).is(':checked'))
			{
				$("#amt1_"+val1).show();
				$("#fAmount_"+val1).focus();
			}else{
				$("#amt1_"+val1).hide();
				$("#fAmount_"+val1).val("");
			}	
		}
		
		 function check_empty()
		{	
			var err=0;
			$("input[type=checkbox]:checked").each ( function() {
				var tmp="fAmount_"+$(this).val();
				var tmp1="desc_"+$(this).val();
				var tmp1_val=$("#"+tmp1).val();
				
				if ( $("#"+tmp).val() == "" )
				{
					alert('Please Enter Amount for '+tmp1_val+'.');
					$("#"+tmp).focus();
					err=1;
					return false;
				}
			});
			if(err == 1)
			{
				return false;
			}else{
				document.frm1.submit();
			}	
		}
		
</script>

    <!-- End: Footer Script -->
</body>
</html>
