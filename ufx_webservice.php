<?php
	//date_default_timezone_set('Asia/Kolkata');
	@session_start();
	$_SESSION['sess_hosttype'] = 'ufxall';
    include_once('common.php');
    include_once('include_taxi_webservices.php');
	include_once(TPATH_CLASS.'configuration.php');
	include_once('generalFunctions.php');
	$type = isset($_REQUEST['type'])?clean($_REQUEST['type']):'';
	
	if($type=="getServiceCategories"){
		global $generalobj;
		
		$parentId = isset($_REQUEST['parentId'])?clean($_REQUEST['parentId']):0;
		$userId = isset($_REQUEST['userId'])?clean($_REQUEST['userId']):'';
		if($userId != "") {
			$sql1 = "SELECT vLang FROM `register_user` WHERE iUserId='$userId'";
			$row = $obj->MySQLSelect($sql1);
			$lang = $row[0]['vLang'];
			if($lang == "") { $lang = "EN"; }
			
			//$vehicle_category = get_value('vehicle_category', 'iVehicleCategoryId, vLogo,vCategory_'.$row[0]['vLang'].' as vCategory', 'eStatus', 'Active');
			$sql2 = "SELECT iVehicleCategoryId, vLogo,vCategory_".$lang." as vCategory FROM vehicle_category WHERE eStatus='Active' AND iParentId='$parentId'";
			$Data = $obj->MySQLSelect($sql2);
			
			for($i=0;$i<count($Data);$i++){
				$Data[$i]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'].'/'.$Data[$i]['iVehicleCategoryId'].'/android/'.$Data[$i]['vLogo'];
			}
			
			if(!empty($Data)){
				$returnArr['Action']="1";
				$returnArr['message'] = $Data;
			}else{
				$returnArr['Action']="0"; 
				$returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
			}
		}else{
			$returnArr['Action']="0"; 
			$returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
		}
		echo json_encode($returnArr);
	}
	
	if($type=="getServiceCategoryTypes"){
		global $generalobj;
		
		$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId'])?clean($_REQUEST['iVehicleCategoryId']):0;
		$userId = isset($_REQUEST['userId'])?clean($_REQUEST['userId']):'';
		if($userId != "") {
			$sql1 = "SELECT vLang FROM `register_user` WHERE iUserId='$userId'";
			$row = $obj->MySQLSelect($sql1);
			$lang = $row[0]['vLang'];
			if($lang == "") { $lang = "EN"; }
			
			$sql2 = "SELECT vc.iVehicleCategoryId, vc.vCategory_".$lang." as vCategory, vt.vVehicleType_".$lang." as vVehicleType, vc.vCategoryTitle_".$lang." as vCategoryTitle, vc.tCategoryDesc_".$lang." as tCategoryDesc, vt.iVehicleTypeId, fFixedFare FROM vehicle_category as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vc.eStatus='Active' AND vt.iVehicleCategoryId='$iVehicleCategoryId'";
			$Data = $obj->MySQLSelect($sql2);
			
			if(!empty($Data)){
				$returnArr['Action']="1";
				$returnArr['message'] = $Data;
			}else{
				$returnArr['Action']="0"; 
				$returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
			}
		}else{
			$returnArr['Action']="0"; 
			$returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
		}
		echo json_encode($returnArr);
	}
	
	
	