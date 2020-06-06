<?php
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'vehicle_type';
$script = 'VehicleType';

$vVehicleType = isset($_POST['vVehicleType']) ? $_POST['vVehicleType'] : '';

$iVehicleCategoryId = isset($_POST['iVehicleCategoryId']) ? $_POST['iVehicleCategoryId'] : '';
$fPricePerKM = isset($_POST['fPricePerKM']) ? $_POST['fPricePerKM'] : '';
$fPricePerMin = isset($_POST['fPricePerMin']) ? $_POST['fPricePerMin'] : '';
$fWaitingPricePerMin = isset($_POST['fWaitingPricePerMin']) ? $_POST['fWaitingPricePerMin'] : '';
$iBaseFare = isset($_POST['iBaseFare']) ? $_POST['iBaseFare'] : '';
$iMinFare = isset($_POST['iMinFare']) ? $_POST['iMinFare'] : '';
$fCommision = isset($_POST['fCommision']) ? $_POST['fCommision'] : '';
$iPersonSize = isset($_POST['iPersonSize']) ? $_POST['iPersonSize'] : '';
//$fPickUpPrice = isset($_POST['fPickUpPrice']) ? $_POST['fPickUpPrice'] : '';
$fNightPrice = isset($_POST['fNightPrice']) ? $_POST['fNightPrice'] : '';
//$tPickStartTime = isset($_POST['tPickStartTime']) ? $_POST['tPickStartTime'] : '';

$tMonPickStartTime = isset($_POST['tMonPickStartTime']) ? $_POST['tMonPickStartTime'] : '';
$tMonPickEndTime = isset($_POST['tMonPickEndTime']) ? $_POST['tMonPickEndTime'] : '';
$fMonPickUpPrice = isset($_POST['fMonPickUpPrice']) ? $_POST['fMonPickUpPrice'] : '';

$tTuePickStartTime = isset($_POST['tTuePickStartTime']) ? $_POST['tTuePickStartTime'] : '';
$tTuePickEndTime = isset($_POST['tTuePickEndTime']) ? $_POST['tTuePickEndTime'] : '';
$fTuePickUpPrice = isset($_POST['fTuePickUpPrice']) ? $_POST['fTuePickUpPrice'] : '';

$tWedPickStartTime = isset($_POST['tWedPickStartTime']) ? $_POST['tWedPickStartTime'] : '';
$tWedPickEndTime = isset($_POST['tWedPickEndTime']) ? $_POST['tWedPickEndTime'] : '';
$fWedPickUpPrice = isset($_POST['fWedPickUpPrice']) ? $_POST['fWedPickUpPrice'] : '';

$tThuPickStartTime = isset($_POST['tThuPickStartTime']) ? $_POST['tThuPickStartTime'] : '';
$tThuPickEndTime = isset($_POST['tThuPickEndTime']) ? $_POST['tThuPickEndTime'] : '';
$fThuPickUpPrice = isset($_POST['fThuPickUpPrice']) ? $_POST['fThuPickUpPrice'] : '';

$tFriPickStartTime = isset($_POST['tFriPickStartTime']) ? $_POST['tFriPickStartTime'] : '';
$tFriPickEndTime = isset($_POST['tFriPickEndTime']) ? $_POST['tFriPickEndTime'] : '';
$fFriPickUpPrice = isset($_POST['fFriPickUpPrice']) ? $_POST['fFriPickUpPrice'] : '';

$tSatPickStartTime = isset($_POST['tSatPickStartTime']) ? $_POST['tSatPickStartTime'] : '';
$tSatPickEndTime = isset($_POST['tSatPickEndTime']) ? $_POST['tSatPickEndTime'] : '';
$fSatPickUpPrice = isset($_POST['fSatPickUpPrice']) ? $_POST['fSatPickUpPrice'] : '';

$tSunPickStartTime = isset($_POST['tSunPickStartTime']) ? $_POST['tSunPickStartTime'] : '';
$tSunPickEndTime = isset($_POST['tSunPickEndTime']) ? $_POST['tSunPickEndTime'] : '';
$fSunPickUpPrice = isset($_POST['fSunPickUpPrice']) ? $_POST['fSunPickUpPrice'] : '';
$eMultiPassenger = isset($_POST['eMultiPassenger']) ? $_POST['eMultiPassenger'] : '';
$eMultiPassenger = $eMultiPassenger == 'on' ? 'Yes' : 'No';


//$tPickEndTime = isset($_POST['tPickEndTime']) ? $_POST['tPickEndTime'] : '';
$tNightStartTime = isset($_POST['tNightStartTime']) ? $_POST['tNightStartTime'] : '';
$tNightEndTime = isset($_POST['tNightEndTime']) ? $_POST['tNightEndTime'] : '';
$eStatus_picktime = isset($_POST['ePickStatus']) ? $_POST['ePickStatus'] : 'off';
$ePickStatus = ($eStatus_picktime == 'on') ? 'Active' : 'Inactive';
$ePriceZone = (isset($_POST['ePriceZone']) && $_POST['ePriceZone'] == 'on') ? 'Active' : 'Inactive';
//$tPriceZoneSerialize = (isset($_POST['ePriceZone']) && $_POST['ePriceZone'] == 'on')?'Active':'Inactive';
$eStatus_nighttime = isset($_POST['eNightStatus']) ? $_POST['eNightStatus'] : 'off';
$eNightStatus = ($eStatus_nighttime == 'on') ? 'Active' : 'Inactive';
$eType = isset($_POST['eType']) ? $_POST['eType'] : '';
$eIconType = isset($_POST['eIconType']) ? $_POST['eIconType'] : '';
$vSavarArea = isset($_POST['vSavarArea']) ? $_POST['vSavarArea'] : '';

$eFareType = isset($_POST['eFareType']) ? $_POST['eFareType'] : '';
$fFixedFare = isset($_POST['fFixedFare']) ? $_POST['fFixedFare'] : '';
$eAllowQty = isset($_POST['eAllowQty']) ? $_POST['eAllowQty'] : '';
$iMaxQty = isset($_POST['iMaxQty']) ? $_POST['iMaxQty'] : '';
$fPricePerHour = isset($_POST['fPricePerHour']) ? $_POST['fPricePerHour'] : '';


// add by seyyed amir

$zoneDistance = $_POST['zoneDistance'];
$zoneSurcharge = $_POST['zoneSurcharge'];

$priceZoneArray = array();

for ($i = 0; $i < count($zoneDistance); $i++) {
    if ($zoneDistance[$i] != '' && $zoneSurcharge[$i] != ''
        && $zoneDistance[$i] != '0' && $zoneSurcharge[$i] != '0') {
        $priceZoneArray[] = array("zoneDistance" => $zoneDistance[$i], "zoneSurcharge" => $zoneSurcharge[$i]);
    }
}

function amircmp($a, $b)
{
    if ($a['zoneDistance'] == $b['zoneDistance']) {
        return 0;
    }
    return ($a['zoneDistance'] < $b['zoneDistance']) ? -1 : 1;
}

uasort($priceZoneArray, 'amircmp');

$tPriceZoneSerialize = serialize($priceZoneArray);

//////////////// MULTI PASSENGER BY SEYYED AMIT
$seatsNumber = $_POST['seatsNumber'];
$priceSurcharge = $_POST['priceSurcharge'];

$multiPassengerPriceArray = array();

for ($i = 0; $i < count($seatsNumber); $i++) {
    if ($seatsNumber[$i] != '' && $priceSurcharge[$i] != ''
        && $seatsNumber[$i] != '0' && $priceSurcharge[$i] != '0') {
        $multiPassengerPriceArray[] = array("seatsNumber" => $seatsNumber[$i], "priceSurcharge" => $priceSurcharge[$i]);
    }
}

function amircmp2($a, $b)
{
    if ($a['seatsNumber'] == $b['seatsNumber']) {
        return 0;
    }
    return ($a['seatsNumber'] < $b['seatsNumber']) ? -1 : 1;
}

uasort($multiPassengerPriceArray, 'amircmp2');

$tMultiPassengerSerialize = serialize($multiPassengerPriceArray);

///////////////////////////


$vTitle_store = array();
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$sql = "SELECT * FROM `language_master` where  1=1 ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';

    }
}


$sql = "SELECT * FROM `savar_area`";
$vSavarAreaArray = $obj->MySQLSelect($sql);


//print_r($vTitle_store);exit;
if (isset($_POST['btnsubmit'])) {
    if ($eFareType == "Fixed") {
        $ePickStatus = "Inactive";
        $eNightStatus = "Inactive";
        $ePriceZone = "Inactive";
    } else {
        $ePickStatus = $ePickStatus;
        $eNightStatus = $eNightStatus;
        $ePriceZone = $ePriceZone;
    }

    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {
        $filecheck = basename($_FILES['vLogo']['name']);

        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "png") {
            $flag_error = 1;
            $var_msg = "Upload only png image" . $ext;
        }
        $data = getimagesize($_FILES['vLogo']['tmp_name']);
        $width = $data[0];
        $height = $data[1];

        if ($width != 360 && $height != 360) {

            $flag_error = 1;
            $var_msg = "Please Upload image only 360px * 360px";
        }

        if ($flag_error == 1) {


            if ($action == "Add") {
                header("Location:vehicle_type_action.php?var_msg=" . $var_msg . "&success=3");
                exit;
            } else {
                header("Location:vehicle_type_action.php?id=" . $id . "&var_msg=" . $var_msg . "&success=3");
                exit;
            }

            // $generalobj->getPostForm($_POST, $var_msg, "vehicle_type_action.php?success=0&var_msg=".$var_msg);
            // exit;
        }
    }

    if (isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != "") {
        $filecheck = basename($_FILES['vLogo1']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "png") {
            $flag_error = 1;
            $var_msg = "Upload only png image";
        }
        $data = getimagesize($_FILES['vLogo1']['tmp_name']);
        $width = $data[0];
        $height = $data[1];

        if ($width != 360 && $height != 360) {

            $flag_error = 1;
            $var_msg = "Please Upload image only 360px * 360px";
        }
        if ($flag_error == 1) {

            if ($action == "Add") {
                header("Location:vehicle_type_action.php?var_msg=" . $var_msg . "&success=3");
                //$generalobj->getPostForm($_POST,$var_msg,"banner_action.php");
                exit;
            } else {
                header("Location:vehicle_type_action.php?id=" . $id . "&var_msg=" . $var_msg . "&success=3");
                //$generalobj->getPostForm($_POST,$var_msg,"banner_action.php?id=".$id."&var_msg=".$var_msg);
                exit;
            }
            //$generalobj->getPostForm($_POST, $var_msg, "vehicle_type_action.php?success=0&var_msg=".$var_msg);
            exit;
        }
    }

    if ($ePickStatus == "Active") {

        /*  if($tPickStartTime > $tPickEndTime){
            header("Location:vehicle_type_action.php?id=".$id."&success=3");exit;
          }*/

        if ($tMonPickStartTime > $tMonPickEndTime) {

            $varmsg = "Please Select  Monday Peak زمان شروع less than Monday Peak زمان پایان.";
            header("Location:vehicle_type_action.php?id=" . $id . "&success=3&varmsg=" . $var_msg);
            exit;
        }

        if ($tTuePickStartTime > $tTuePickEndTime) {
            $varmsg = "Please Select  Tuesday Peak زمان شروع less than Tuesday Peak زمان پایان.";
            header("Location:vehicle_type_action.php?id=" . $id . "&success=3&varmsg=" . $varmsg);
            exit;
        }

        if ($tWedPickStartTime > $tWedPickEndTime) {
            $varmsg = "Please Select  Wednesday Peak زمان شروع less than Wednesday Peak زمان پایان.";
            header("Location:vehicle_type_action.php?id=" . $id . "&success=3&varmsg=" . $varmsg);
            exit;
        }

        if ($tThuPickStartTime > $tThuPickEndTime) {
            $varmsg = "Please Select  Thursday Peak زمان شروع less than Thursday Peak زمان پایان.";
            header("Location:vehicle_type_action.php?id=" . $id . "&success=3&varmsg=" . $varmsg);
            exit;
        }

        if ($tFriPickStartTime > $tFriPickEndTime) {
            $varmsg = "Please Select  Friday Peak زمان شروع less than Friday Peak زمان پایان.";
            header("Location:vehicle_type_action.php?id=" . $id . "&success=3&varmsg=" . $varmsg);
            exit;
        }

        if ($tSatPickStartTime > $tSatPickEndTime) {
            $varmsg = "Please Select  Saturday Peak زمان شروع less than Saturday Peak زمان پایان.";
            header("Location:vehicle_type_action.php?id=" . $id . "&success=3&varmsg=" . $varmsg);
            exit;
        }

        if ($tSunPickStartTime > $tSunPickEndTime) {
            $varmsg = "Please Select  Sunday Peak زمان شروع less than Sunday Peak زمان پایان.";
            header("Location:vehicle_type_action.php?id=" . $id . "&success=3&varmsg=" . $varmsg);
            exit;
        }

    }
    if ($eNightStatus == "Active") {
        if ($tNightStartTime > $tNightEndTime) {
            header("Location:vehicle_type_action.php?id=" . $id . "&success=4");
            exit;
        }
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:vehicle_type_action.php?id=" . $id . "&success=2");
        exit;
    }

    for ($i = 0; $i < count($vTitle_store); $i++) {

        $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
        // echo $_POST[$vTitle_store[$i]] ; exit;
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {

            $q = "UPDATE ";
            $where = " WHERE `iVehicleTypeid` = '" . $id . "'";
        }


        $query = $q . " `" . $tbl_name . "` SET
				`vVehicleType` = '" . $vVehicleType . "',
				`iVehicleCategoryId` = '" . $iVehicleCategoryId . "',
				`eFareType` = '" . $eFareType . "',
				`fFixedFare` = '" . $fFixedFare . "',
				`fPricePerKM` = '" . $fPricePerKM . "',
				`fPricePerMin` = '" . $fPricePerMin . "',
				`fWaitingPricePerMin` = '" . $fWaitingPricePerMin . "',
				`iBaseFare` = '" . $iBaseFare . "',
				`iMinFare` = '" . $iMinFare . "',
				`fCommision` = '" . $fCommision . "',
				`iPersonSize` = '" . $iPersonSize . "',
				`fNightPrice` = '" . $fNightPrice . "',
				`tNightStartTime` = '" . $tNightStartTime . "',
				`tNightEndTime` = '" . $tNightEndTime . "',
				`ePickStatus` = '" . $ePickStatus . "',
				`eAllowQty` = '" . $eAllowQty . "',
				`fPricePerHour` = '" . $fPricePerHour . "',
				`iMaxQty` = '" . $iMaxQty . "',
				`eType` = '" . $eType . "',
				`eIconType` = '" . $eIconType . "',
				`vSavarArea` = '" . $vSavarArea . "',
				`eNightStatus` = '" . $eNightStatus . "',
				`tMonPickStartTime` = '" . $tMonPickStartTime . "',
				`tMonPickEndTime` = '" . $tMonPickEndTime . "',
				`fMonPickUpPrice` = '" . $fMonPickUpPrice . "',
				`tTuePickStartTime` = '" . $tTuePickStartTime . "',
				`tTuePickEndTime` = '" . $tTuePickEndTime . "',
				`fTuePickUpPrice` = '" . $fTuePickUpPrice . "',
				`tWedPickStartTime` = '" . $tWedPickStartTime . "',
				`tWedPickEndTime` = '" . $tWedPickEndTime . "',
				`fWedPickUpPrice` = '" . $fWedPickUpPrice . "',
				`tThuPickStartTime` = '" . $tThuPickStartTime . "',
				`tThuPickEndTime` = '" . $tThuPickEndTime . "',
				`fThuPickUpPrice` = '" . $fThuPickUpPrice . "',
				`tFriPickStartTime` = '" . $tFriPickStartTime . "',
				`tFriPickEndTime` = '" . $tFriPickEndTime . "',
				`fFriPickUpPrice` = '" . $fFriPickUpPrice . "',
				`tSatPickStartTime` = '" . $tSatPickStartTime . "',
				`tSatPickEndTime` = '" . $tSatPickEndTime . "',
				`fSatPickUpPrice` = '" . $fSatPickUpPrice . "',
				`tSunPickStartTime` = '" . $tSunPickStartTime . "',
				`tSunPickEndTime` = '" . $tSunPickEndTime . "',
				`fSunPickUpPrice` = '" . $fSunPickUpPrice . "',
				`eMultiPassenger` = '" . $eMultiPassenger . "',
				`tMultiPassengerSerialize` = '" . $tMultiPassengerSerialize . "',
				`ePriceZone` = '" . $ePriceZone . "',
				`tPriceZoneSerialize` = '" . $tPriceZoneSerialize . "',
				`" . $vValue . "` = '" . $_POST[$vTitle_store[$i]] . "'"
            . $where;

        $obj->sql_query($query);
        $id = ($id != '') ? $id : mysql_insert_id();

    }


    // exit;
    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {

        $img_path = $tconfig["tsite_upload_images_vehicle_type_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo']['tmp_name'];
        $image_name = $_FILES['vLogo']['name'];

        $check_file_query = "select iVehicleTypeId,vLogo from vehicle_type where iVehicleTypeId=" . $id;
        $check_file = $obj->sql_query($check_file_query);

        if ($image_name != "") {


            if ($message_print_id != "") {
                $check_file['vLogo'] = $img_path . '/' . $id . '/android/' . $check_file[0]['vLogo'];
                $android_path = $img_path . '/' . $id . '/android';
                $ios_path = $img_path . '/' . $id . '/ios';

                if ($check_file['vLogo'] != '' && file_exists($check_file['vLogo'])) {
                    @unlink($android_path . '/' . $check_file[0]['vLogo']);
                    @unlink($android_path . '/mdpi_' . $check_file[0]['vLogo']);
                    @unlink($android_path . '/hdpi_' . $check_file[0]['vLogo']);
                    @unlink($android_path . '/xhdpi_' . $check_file[0]['vLogo']);
                    @unlink($android_path . '/xxhdpi_' . $check_file[0]['vLogo']);
                    @unlink($android_path . '/xxxhdpi_' . $check_file[0]['vLogo']);
                    @unlink($ios_path . '/' . $check_file[0]['vLogo']);
                    @unlink($ios_path . '/1x_' . $check_file[0]['vLogo']);
                    @unlink($ios_path . '/2x_' . $check_file[0]['vLogo']);
                    @unlink($ios_path . '/3x_' . $check_file[0]['vLogo']);
                }
            }

            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';
            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                mkdir($Photo_Gallery_folder_android, 0777);
                mkdir($Photo_Gallery_folder_ios, 0777);
            }

            $vVehicleType1 = str_replace(' ', '', $vVehicleType);
            $img = $generalobj->general_upload_image_vehicle_android($image_object, $image_name, $Photo_Gallery_folder_android, $tconfig["tsite_upload_images_vehicle_type_size1_android"], $tconfig["tsite_upload_images_vehicle_type_size2_android"], $tconfig["tsite_upload_images_vehicle_type_size3_both"], $tconfig["tsite_upload_images_vehicle_type_size4_android"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_type_size5_both"], $Photo_Gallery_folder_android, $vVehicleType1, NULL);
            $img1 = $generalobj->general_upload_image_vehicle_ios($image_object, $image_name, $Photo_Gallery_folder_ios, '', '', $tconfig["tsite_upload_images_vehicle_type_size3_both"], $tconfig["tsite_upload_images_vehicle_type_size5_both"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_type_size5_ios"], $Photo_Gallery_folder_ios, $vVehicleType1, NULL);
            $vImage = "ic_car_" . $vVehicleType1 . ".png";


            $sql = "UPDATE " . $tbl_name . " SET `vLogo` = '" . $vImage . "' WHERE `iVehicleTypeId` = '" . $id . "'";

            $obj->sql_query($sql);
        }
    }

    if (isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != "") {
        $img_path = $tconfig["tsite_upload_images_vehicle_type_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo1']['tmp_name'];
        $image_name = $_FILES['vLogo1']['name'];
        $check_file_query = "select iVehicleTypeId,vLogo1 from vehicle_type where iVehicleTypeId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        if ($image_name != "") {
            if ($message_print_id != "") {
                $check_file['vLogo1'] = $img_path . '/' . $id . '/android/' . $check_file[0]['vLogo1'];
                $android_path = $img_path . '/' . $id . '/android';
                $ios_path = $img_path . '/' . $id . '/ios';

                if ($check_file['vLogo1'] != '' && file_exists($check_file['vLogo1'])) {
                    @unlink($android_path . '/' . $check_file[0]['vLogo1']);
                    @unlink($android_path . '/mdpi_hover_' . $check_file[0][0]['vLogo1']);
                    @unlink($android_path . '/hdpi_hover_' . $check_file[0]['vLogo1']);
                    @unlink($android_path . '/xhdpi_hover_' . $check_file[0]['vLogo1']);
                    @unlink($android_path . '/xxhdpi_hover_' . $check_file[0]['vLogo1']);
                    @unlink($android_path . '/xxxhdpi_hover_' . $check_file[0]['vLogo1']);
                    @unlink($ios_path . '/' . $check_file[0]['vLogo1']);
                    @unlink($ios_path . '/1x_hover_' . $check_file[0]['vLogo1']);
                    @unlink($ios_path . '/2x_hover_' . $check_file[0]['vLogo1']);
                    @unlink($ios_path . '/3x_hover_' . $check_file[0]['vLogo1']);
                }
            }
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            $Photo_Gallery_folder_android = $Photo_Gallery_folder . '/android/';
            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . '/ios/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                mkdir($Photo_Gallery_folder_android, 0777);
                mkdir($Photo_Gallery_folder_ios, 0777);
            }
            $vVehicleType1 = str_replace(' ', '', $vVehicleType);
            $img = $generalobj->general_upload_image_vehicle_android($image_object, $image_name, $Photo_Gallery_folder_android, $tconfig["tsite_upload_images_vehicle_type_size1_android"], $tconfig["tsite_upload_images_vehicle_type_size2_android"], $tconfig["tsite_upload_images_vehicle_type_size3_both"], $tconfig["tsite_upload_images_vehicle_type_size4_android"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_type_size5_both"], $Photo_Gallery_folder_android, $vVehicleType1, "hover_");
            $img1 = $generalobj->general_upload_image_vehicle_ios($image_object, $image_name, $Photo_Gallery_folder_ios, '', '', $tconfig["tsite_upload_images_vehicle_type_size3_both"], $tconfig["tsite_upload_images_vehicle_type_size5_both"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_type_size5_ios"], $Photo_Gallery_folder_ios, $vVehicleType1, "hover_");
            $vImage1 = "ic_car_" . $vVehicleType1 . ".png";

            $sql = "UPDATE " . $tbl_name . " SET `vLogo1` = '" . $vImage1 . "' WHERE `iVehicleTypeId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }

    // $obj->sql_query($query);
    header("Location:vehicle_type_action.php?id=" . $id . '&success=1');
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleTypeid = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    //

    $vLabel = $id;
    if (count($db_data) > 0) {
        for ($i = 0; $i < count($db_master); $i++) {

            foreach ($db_data as $key => $value) {
                $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vVehicleType = $value['vVehicleType'];
                $iVehicleCategoryId = $value['iVehicleCategoryId'];
                $fPricePerKM = $value['fPricePerKM'];
                $fPricePerMin = $value['fPricePerMin'];
                $fWaitingPricePerMin = $value['fWaitingPricePerMin'];
                $iBaseFare = $value['iBaseFare'];
                $iMinFare = $value['iMinFare'];
                $fCommision = $value['fCommision'];
                $iPersonSize = $value['iPersonSize'];
                $fPricePerHour = $value['fPricePerHour'];
                //$fPickUpPrice = $value['fPickUpPrice'];
                $fNightPrice = $value['fNightPrice'];
                // $tPickStartTime = $value['tPickStartTime'];
                // $tPickEndTime = $value['tPickEndTime'];
                $tNightStartTime = $value['tNightStartTime'];
                $tNightEndTime = $value['tNightEndTime'];
                $ePickStatus = $value['ePickStatus'];
                $eNightStatus = $value['eNightStatus'];
                $tMonPickStartTime = $value['tMonPickStartTime'];
                $tMonPickEndTime = $value['tMonPickEndTime'];
                $fMonPickUpPrice = $value['fMonPickUpPrice'];
                $tTuePickStartTime = $value['tTuePickStartTime'];
                $tTuePickEndTime = $value['tTuePickEndTime'];
                $fTuePickUpPrice = $value['fTuePickUpPrice'];
                $tWedPickStartTime = $value['tWedPickStartTime'];
                $tWedPickEndTime = $value['tWedPickEndTime'];
                $fWedPickUpPrice = $value['fWedPickUpPrice'];
                $tThuPickStartTime = $value['tThuPickStartTime'];
                $tThuPickEndTime = $value['tThuPickEndTime'];
                $fThuPickUpPrice = $value['fThuPickUpPrice'];
                $tFriPickStartTime = $value['tFriPickStartTime'];
                $tFriPickEndTime = $value['tFriPickEndTime'];
                $fFriPickUpPrice = $value['fFriPickUpPrice'];
                $tSatPickStartTime = $value['tSatPickStartTime'];
                $tSatPickEndTime = $value['tSatPickEndTime'];
                $fSatPickUpPrice = $value['fSatPickUpPrice'];
                $tSunPickStartTime = $value['tSunPickStartTime'];
                $tSunPickEndTime = $value['tSunPickEndTime'];
                $fSunPickUpPrice = $value['fSunPickUpPrice'];
                $vLogo = $value['vLogo'];
                $eType = $value['eType'];
                $eIconType = $value['eIconType'];
                $vSavarArea = $value['vSavarArea'];
                $fFixedFare = $value['fFixedFare'];
                $eFareType = $value['eFareType'];
                $eAllowQty = $value['eAllowQty'];
                $iMaxQty = $value['iMaxQty'];
                $eMultiPassenger = $value['eMultiPassenger'];
                $tMultiPassengerSerialize = $value['tMultiPassengerSerialize'];
                $ePriceZone = $value['ePriceZone'];
                $tPriceZoneSerialize = $value['tPriceZoneSerialize'];


                $priceZoneArray = @unserialize($value['tPriceZoneSerialize']);
                if ($priceZoneArray === false)
                    $priceZoneArray = array();

                $multiPassengerPriceArray = @unserialize($value['tMultiPassengerSerialize']);
                if ($multiPassengerPriceArray === false)
                    $multiPassengerPriceArray = array();
            }
        }
    }
}

if ($APP_TYPE == 'UberX') {
    $sql_cat = "SELECT * FROM  vehicle_category";
    $db_data_cat = $obj->MySQLSelect($sql_cat);

}
?>
<link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-right"> نوع خودرو </h2>
                <a href="<?php echo adminUrl('vehicleTypes'); ?>" class="btn btn-primary">
                    بازگشت به لیست
                </a>
            </div>
            <hr/>
            <div class="col-12">
                <?php if ($success == 1) { ?>
                    <div class="alert alert-success alert-dismissable msgs_hide">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        نوع خودرو با موفقیت ویرایش شد
                    </div><br/>
                <?php } elseif ($success == 2) { ?>
                    <div class="alert alert-danger alert-dismissable ">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be
                        enabled
                        on the main script we will provide you.
                    </div><br/>
                <?php } elseif ($success == 3) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        <?php echo $_REQUEST['varmsg']; ?>
                    </div><br/>
                <?php } elseif ($success == 4) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        "Please Select Night زمان شروع less than Night زمان پایان."
                    </div><br/>
                <?php } ?>
                <?php if ($_REQUEST['var_msg'] != Null) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        Record Not Updated .
                    </div><br/>
                <?php } ?>
                <div id="price1"></div>
                <br/>
                <div id="price"></div>
                <br/>
                <form id="vtype" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                    <?php if ($APP_TYPE == 'UberX') { ?>
                        <div class="form-group">
                            <label for="iVehicleCategoryId">دسته بندی خودرو<span class="red"> *</span></label>
                            <select class="form-control" id="iVehicleCategoryId" name='iVehicleCategoryId' required>
                                <option value="">--select--</option>
                                <?php for ($i = 0; $i < count($db_data_cat); $i++) { ?>
                                    <option value="<?php echo $db_data_cat[$i]['iVehicleCategoryId'] ?>" <?php echo ($db_data_cat[$i]['iVehicleCategoryId'] == $iVehicleCategoryId) ? 'selected' : ''; ?>><?php echo $db_data_cat[$i]['vCategory_EN'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if ($APP_TYPE == 'Ride-Delivery') { ?>
                        <div class="row">
                            <div class="form-group col-12 col-md-4">
                                <label for="eType">دسته بندی نوع خودرو<span class="red"> *</span></label>
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-secondary active">
                                        <input type="radio" name="eType" value="Ride"
                                               autocomplete="off" <?php echo ($eType == "Ride") ? '' : 'checked'; ?>>
                                        راننده
                                    </label>
                                    <label class="btn btn-secondary">
                                        <input type="radio" name="eType" value="Deliver"
                                               autocomplete="off" <?php echo ($eType == "Deliver") ? '' : 'checked'; ?>>
                                        پیک موتوری
                                    </label>
                                    <label class="btn btn-secondary">
                                        <input type="radio" name="eType" value="SchoolServices"
                                               autocomplete="off" <?php echo ($eType == "SchoolServices") ? '' : 'checked'; ?>>
                                        سرویس مدرسه
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>آیکون نشانگر وسیله نقلیه بر روی نقشه<span class="red"> *</span></label>
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-secondary active">
                                        <input type="radio" name="eIconType" value="Car"
                                               autocomplete="off" <?php echo ($eIconType == "Car") ? '' : 'checked'; ?>>
                                        خودرو
                                    </label>
                                    <label class="btn btn-secondary">
                                        <input type="radio" name="eIconType" value="Bike"
                                               autocomplete="off" <?php echo ($eIconType == "Bike") ? '' : 'checked'; ?>>
                                        موتورسیکلت
                                    </label>
                                    <label class="btn btn-secondary">
                                        <input type="radio" name="eIconType" value="Cycle"
                                               autocomplete="off" <?php echo ($eIconType == "Cycle") ? '' : 'checked'; ?>>
                                        اتوبوس
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label for="vSavarArea">محدوده خودرو<span class="red"> *</span></label>
                                <select class="form-control" id="vSavarArea" name='vSavarArea' required>
                                    <option value="0" <?php if ($vSavarArea == 0) echo 'selected="selected"'; ?> >انتخاب
                                        کنید
                                    </option>
                                    <?php foreach ($vSavarAreaArray as $area) : ?>
                                        <option value="<?php echo $area['aId'] ?>" <?php if ($vSavarArea == $area['aId']) echo 'selected="selected"'; ?> ><?php echo $area['sAreaName'] . ' ( ' . $area['sAreaNamePersian'] . ' )'; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php } else {
                        $Vehicle_type_name = ($APP_TYPE == 'Delivery') ? 'Deliver' : $APP_TYPE;
                        ?>
                        <input type="hidden" name="eType" value="<?php echo $Vehicle_type_name; ?>"/>
                    <?php } ?>
                    <div class="row">
                        <div class="form-group col-12 col-md-4">
                            <label for="vVehicleType">نوع خودرو<span class="red"> *</span> <i class="icon-question-sign"
                                                                                              data-placement="top"
                                                                                              data-toggle="tooltip"
                                                                                              data-original-title='Type of vehicle like Small car, Luxury car, SUV, VAN for example'></i></label>
                            <input type="text" class="form-control" name="vVehicleType" id="vVehicleType"
                                   value="<?php echo $vVehicleType; ?>" required>
                        </div>
                        <?php
                        if ($count_all > 0) {
                            for ($i = 0; $i < $count_all; $i++) {
                                $vCode = $db_master[$i]['vCode'];
                                $vTitle = $db_master[$i]['vTitle'];
                                $eDefault = $db_master[$i]['eDefault'];

                                $vValue = 'vVehicleType_' . $vCode;

                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                ?>
                                <div class="form-group col-12 col-md-4">
                                    <label for="<?php echo $vValue; ?>"">نوع خودرو (<?php echo $vTitle; ?>) <span
                                            class="red"> *</span></label>
                                    <input type="text" class="form-control" name="<?php echo $vValue; ?>"
                                           id="<?php echo $vValue; ?>" value="<?php echo $$vValue; ?>"
                                           placeholder="<?php echo $vTitle; ?>Value" <?php echo $required; ?>>
                                </div>
                            <?php }
                        } ?>
                    </div>
                    <?php if ($APP_TYPE == 'UberX') { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>نوع خودرو <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='eFareType' id="eFareType" required
                                        onchange="get_faretype(this.value)">
                                    <option value="Regular"<?
                                    if ($eFareType == "Regular") {
                                        echo 'selected="selected"';
                                    }
                                    ?>>زمان و فاصله
                                    </option>
                                    <option value="Fixed"<?
                                    if ($eFareType == "Fixed") {
                                        echo 'selected="selected"';
                                    }
                                    ?>>فیکس شده
                                    </option>
                                    <option value="Hourly"<?
                                    if ($eFareType == "Hourly") {
                                        echo 'selected="selected"';
                                    }
                                    ?>>ساعتی
                                    </option>
                                </select>
                            </div>
                        </div>
                    <?php } else { ?>
                        <input type="hidden" name="eFareType" value="Regular"/>
                    <?php } ?>
                    <div class="row" id="fixed_div" style="display:none;">
                        <div class="col-lg-12">
                            <label><?php echo $langage_lbl_admin['LBL_FIXED_FARE_TXT_ADMIN']; ?><span
                                        class="red"> *</span></label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="fFixedFare" id="fFixedFare"
                                   value="<?php echo $fFixedFare; ?>">
                        </div>
                    </div>

                    <div class="row" id="Regular_div1">
                        <div class="form-group col-12 col-md-3" id="hide-km">
                            <label for="fPricePerKM"> قیمت هر کیلومتر<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="fPricePerKM" id="fPricePerKM"
                                   value="<?php echo $fPricePerKM; ?>" onchange="getpriceCheck(this.value)">
                        </div>
                        <div class="form-group col-12 col-md-3" id="hide-price">
                            <label for="fPricePerMin">قیمت هر دقیقه<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="fPricePerMin" id="fPricePerMin"
                                   value="<?php echo $fPricePerMin; ?>" onChange="getpriceCheck(this.value)">

                        </div>
                        <div class="form-group col-12 col-md-3" id="hide-price">
                            <label for="fWaitingPricePerMin">قیمت هر دقیقه انتظار<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="fWaitingPricePerMin"
                                   id="fWaitingPricePerMin"
                                   value="<?php echo $fWaitingPricePerMin; ?>"
                                   onChange="getpriceCheck(this.value)">
                        </div>
                        <div class="form-group col-12 col-md-3" id="hide-priceHour">
                            <label for="fPricePerHour">قیمت هر ساعت<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="fPricePerHour" id="fPricePerHour"
                                   value="<?php echo $fPricePerHour; ?>" onChange="getpriceCheck(this.value)">
                        </div>
                        <div class="form-group col-12 col-md-6" id="hide-minimumfare">
                            <label for="iMinFare">حداقل کرایه<span class="red"> *</span> <i class="icon-question-sign"
                                                                                            data-placement="top"
                                                                                            data-toggle="tooltip"
                                                                                            data-original-title='The minimum fare is the least amount you have to pay. For eg : if you travel a distance of 1 km  , the actual fare will be $10 (base fare $6 + $2/km + $2/min) assuming that it takes 1 min to travel but still you are liable to pay the minimum fare which is $15 for example.'></i></label>
                            <input type="text" class="form-control" name="iMinFare" id="iMinFare"
                                   value="<?php echo $iMinFare; ?>" onchange="getpriceCheck(this.value)">
                        </div>
                        <div class="form-group col-12 col-md-6" id="hide-basefare">
                            <label for="iBaseFare"> کرایه پایه<span class="red"> *</span> <i class="icon-question-sign"
                                                                                             data-placement="top"
                                                                                             data-toggle="tooltip"
                                                                                             data-original-title='Base fare is the price that the taxi meter will start at a certain point. Let say if you set base fare $3 then the meter will be set at $3 to begin, and not $0.'></i></label>
                            <input type="text" class="form-control" name="iBaseFare" id="iBaseFare"
                                   value="<?php echo $iBaseFare; ?>" onChange="getpriceCheck(this.value)">
                        </div>
                    </div>

                    <!--                    <div id="Regular_div1">-->
                    <!--                        <div class="row" id="hide-km">-->
                    <!--                            <div class="col-lg-12">-->
                    <!--                                <label> قیمت هر کیلومتر<span class="red"> *</span></label>-->
                    <!--                                <input type="text" class="form-control" name="fPricePerKM" id="fPricePerKM"-->
                    <!--                                       value="-->
                    <?php //echo $fPricePerKM; ?><!--" onchange="getpriceCheck(this.value)">-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!---->
                    <!--                        <div class="row" id="hide-price">-->
                    <!--                            <div class="col-lg-12">-->
                    <!--                                <label>قیمت هر دقیقه<span class="red"> *</span></label>-->
                    <!--                            </div>-->
                    <!--                            <div class="col-lg-6">-->
                    <!--                                <input type="text" class="form-control" name="fPricePerMin" id="fPricePerMin"-->
                    <!--                                       value="-->
                    <?php //echo $fPricePerMin; ?><!--" onChange="getpriceCheck(this.value)">-->
                    <!---->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        <div class="row" id="hide-price">-->
                    <!--                            <div class="col-lg-12">-->
                    <!--                                <label>قیمت هر دقیقه انتظار<span class="red"> *</span></label>-->
                    <!--                            </div>-->
                    <!--                            <div class="col-lg-6">-->
                    <!--                                <input type="text" class="form-control" name="fWaitingPricePerMin"-->
                    <!--                                       id="fWaitingPricePerMin"-->
                    <!--                                       value="--><?php //echo $fWaitingPricePerMin; ?><!--"-->
                    <!--                                       onChange="getpriceCheck(this.value)">-->
                    <!---->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        <div class="row" id="hide-priceHour">-->
                    <!--                            <div class="col-lg-12">-->
                    <!--                                <label>قیمت هر ساعت<span class="red"> *</span></label>-->
                    <!--                            </div>-->
                    <!--                            <div class="col-lg-6">-->
                    <!--                                <input type="text" class="form-control" name="fPricePerHour" id="fPricePerHour"-->
                    <!--                                       value="-->
                    <?php //echo $fPricePerHour; ?><!--" onChange="getpriceCheck(this.value)">-->
                    <!---->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        --><?php ////if($APP_TYPE != 'UberX'){ ?>
                    <!--                        <div class="row" id="hide-minimumfare">-->
                    <!--                            <div class="col-lg-12">-->
                    <!--                                <label>حداقل کرایه<span class="red"> *</span> <i class="icon-question-sign"-->
                    <!--                                                                                 data-placement="top"-->
                    <!--                                                                                 data-toggle="tooltip"-->
                    <!--                                                                                 data-original-title='The minimum fare is the least amount you have to pay. For eg : if you travel a distance of 1 km  , the actual fare will be $10 (base fare $6 + $2/km + $2/min) assuming that it takes 1 min to travel but still you are liable to pay the minimum fare which is $15 for example.'></i></label>-->
                    <!--                            </div>-->
                    <!--                            <div class="col-lg-6">-->
                    <!--                                <input type="text" class="form-control" name="iMinFare" id="iMinFare"-->
                    <!--                                       value="-->
                    <?php //echo $iMinFare; ?><!--" onchange="getpriceCheck(this.value)">-->
                    <!---->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        <div class="row" id="hide-basefare">-->
                    <!--                            <div class="col-lg-12">-->
                    <!--                                <label> کرایه پایه<span class="red"> *</span> <i class="icon-question-sign"-->
                    <!--                                                                                 data-placement="top"-->
                    <!--                                                                                 data-toggle="tooltip"-->
                    <!--                                                                                 data-original-title='Base fare is the price that the taxi meter will start at a certain point. Let say if you set base fare $3 then the meter will be set at $3 to begin, and not $0.'></i></label>-->
                    <!--                            </div>-->
                    <!--                            <div class="col-lg-6">-->
                    <!--                                <input type="text" class="form-control" name="iBaseFare" id="iBaseFare"-->
                    <!--                                       value="-->
                    <?php //echo $iBaseFare; ?><!--" onChange="getpriceCheck(this.value)">-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        --><?php //// } ?>
                    <!--                    </div>-->
                    <div class="row">
                        <div class="form-group col-12 col-md-4">
                            <label for="fCommision"> کمیسیون (%)<span class="red"> *</span> <i
                                        class="icon-question-sign"
                                        data-placement="top"
                                        data-toggle="tooltip"
                                        data-original-title='This is % amount that will go to site for each ride.'></i></label>
                            <input type="text" class="form-control" name="fCommision" id="fCommision"
                                   value="<?php echo $fCommision; ?>" required>

                        </div>
                    </div>
                    <div id="Regular_div2">
                        <?php //if($APP_TYPE != 'UberX'){ ?>
                        <div class="row">
                            <div class="form-group col-12 col-md-4">
                                <label for="iPersonSize"> صندلی های موجود / ظرفیت شخصی<span class="red"> *</span> <i
                                            class="icon-question-sign"
                                            data-placement="top"
                                            data-toggle="tooltip"
                                            data-original-title='Number of seats available for riders'></i></label>
                                <input type="text" class="form-control" name="iPersonSize" id="iPersonSize"
                                       value="<?php echo $iPersonSize; ?>"
                                       onChange="getpriceCheck(this.value),onlydigit(this.value)">
                            </div>
                            <div id="digit"></div>
                        </div>
                        <div class="row">
                            <div class="btn-group btn-group-toggle required" data-toggle="buttons">
                                <label class="btn btn-info <?php echo ($id != '' && $ePriceZone == 'Inactive') ? '' : 'active'; ?>">
                                    <input type="checkbox" id="ePriceZone" onChange="showHidePriceZone();"
                                           name="ePriceZone" <?php echo ($id != '' && $ePriceZone == 'Inactive') ? '' : 'checked'; ?>>
                                    منطقه قیمت<span class="red"> *</span> <i class="icon-question-sign"
                                                                             data-placement="top"
                                                                             data-toggle="tooltip"
                                                                             data-original-title='HELP'></i>
                                </label>
                            </div>
                        </div>
                        <div id="showpricezone" class="row" style="display:none;">
                            <?php
                            if (count($priceZoneArray) === 0) {
                                $priceZoneArray[] = [
                                    "zoneDistance" => '',
                                    "zoneSurcharge" => ''
                                ];
                            }
                            ?>
                            <div id="PriceZoneWrap" class="col-10">
                                <?php foreach ($priceZoneArray as $zone) { ?>
                                    <div class="input-group">
                                        <span class="input-group-text border-left-0" style="border-radius: 0;">اگر فاصله بزرگتر از:</span>
                                        <input type="text" class="form-control border-right-0 border-left-0"
                                               name="zoneDistance[]"
                                               id="zoneDistance"
                                               value="<?php echo $zone['zoneDistance']; ?>"
                                               placeholder="Distance">
                                        <span class="input-group-text border-right-0 border-left-0"
                                              style="border-radius: 0;">(km) اضافه کردن:</span>
                                        <input type="text" class="form-control border-right-0"
                                               name="zoneSurcharge[]"
                                               id="zoneSurcharge"
                                               value="<?php echo $zone['zoneSurcharge']; ?>"
                                               placeholder="Surcharge">
                                        <span class="input-group-text border-right-0"
                                              style="border-radius: 0;">(X)</span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-success" onclick="AddNewPriceZone()">
                                    <i class="fa fa-plus"></i>
                                </button>
                        </div>


                        <div class="row">
                            <div class="col-lg-12">

                                <label>حداکثر زمان اضافه کردن روشن/خاموش <span class="red"> *</span> <i
                                            class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                            data-original-title='This is a multiplier X  to the standard fares causing the fare to be higher than the standard fare during certain times the day; i.e. if X is 1.2 during some point of time then the standard fare will be multiplied by 1.2 to get the final fare.'></i></label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" id="ePickStatus" onChange="showhidepickuptime();"
                                           name="ePickStatus" <?php echo ($id != '' && $ePickStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                </div>
                            </div>
                        </div>

                        <div id="showpickuptime" style="display:none;">
                            <div class="row">
                                <div class="col-lg-12 main-table001">
                                    <div class="main-table001">

                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center"><b>دوشنبه</b></td>
                                            </tr>
                                            <tr>
                                                <td> زمان شروع</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tMonPickStartTime"
                                                           id="tMonPickStartTime"
                                                           value="<?php echo $tMonPickStartTime; ?>"
                                                           placeholder="Select Pickup زمان شروع"></td>
                                            </tr>
                                            <tr>
                                                <td> زمان پایان</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tMonPickEndTime"
                                                           id="tMonPickEndTime"
                                                           value="<?php echo $tMonPickEndTime; ?>"
                                                           placeholder="Select Pickup زمان پایان"></td>
                                            </tr>
                                            <tr>
                                                <td> هزینه</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" name="fMonPickUpPrice"
                                                           id="fMonPickUpPrice"
                                                           value="<?php echo $fMonPickUpPrice; ?>"
                                                           placeholder="Enter Price"
                                                           onchange="getpriceCheck(this.value)"></td>
                                            </tr>
                                        </table>

                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center"><b>سه شنبه</b></td>
                                            </tr>
                                            <tr>
                                                <td> زمان شروع</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tTuePickStartTime"
                                                           id="tTuePickStartTime"
                                                           value="<?php echo $tTuePickStartTime; ?>"
                                                           placeholder="Select Pickup زمان شروع"></td>
                                            </tr>
                                            <tr>
                                                <td> زمان پایان</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tTuePickEndTime"
                                                           id="tTuePickEndTime"
                                                           value="<?php echo $tTuePickEndTime; ?>"
                                                           placeholder="Select Pickup زمان پایان"></td>
                                            </tr>
                                            <tr>
                                                <td> هزینه</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" name="fTuePickUpPrice"
                                                           id="fTuePickUpPrice"
                                                           value="<?php echo $fTuePickUpPrice; ?>"
                                                           placeholder="Enter Price"
                                                           onchange="getpriceCheck(this.value)"></td>
                                            </tr>
                                        </table>

                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center"><b>چهار شنبه</b></td>
                                            </tr>
                                            <tr>
                                                <td> زمان شروع</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tWedPickStartTime"
                                                           id="tWedPickStartTime"
                                                           value="<?php echo $tWedPickStartTime; ?>"
                                                           placeholder="Select Pickup زمان شروع"></td>
                                            </tr>
                                            <tr>
                                                <td> زمان پایان</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tWedPickEndTime"
                                                           id="tWedPickEndTime"
                                                           value="<?php echo $tWedPickEndTime; ?>"
                                                           placeholder="Select Pickup زمان پایان"></td>
                                            </tr>
                                            <tr>
                                                <td> هزینه</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" name="fWedPickUpPrice"
                                                           id="fWedPickUpPrice"
                                                           value="<?php echo $fWedPickUpPrice; ?>"
                                                           placeholder="Enter Price"
                                                           onchange="getpriceCheck(this.value)"></td>
                                            </tr>
                                        </table>

                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center"><b>پنج شنبه</b></td>
                                            </tr>
                                            <tr>
                                                <td> زمان شروع</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tThuPickStartTime"
                                                           id="tThuPickStartTime"
                                                           value="<?php echo $tThuPickStartTime; ?>"
                                                           placeholder="Select Pickup زمان شروع"></td>
                                            </tr>
                                            <tr>
                                                <td> زمان پایان</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tThuPickEndTime"
                                                           id="tThuPickEndTime"
                                                           value="<?php echo $tThuPickEndTime; ?>"
                                                           placeholder="Select Pickup زمان پایان"></td>
                                            </tr>
                                            <tr>
                                                <td> هزینه</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" name="fThuPickUpPrice"
                                                           id="fThuPickUpPrice"
                                                           value="<?php echo $fThuPickUpPrice; ?>"
                                                           placeholder="Enter Price"
                                                           onchange="getpriceCheck(this.value)"></td>
                                            </tr>
                                        </table>

                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center"><b>جمعه</b></td>
                                            </tr>
                                            <tr>
                                                <td> زمان شروع</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tFriPickStartTime"
                                                           id="tFriPickStartTime"
                                                           value="<?php echo $tFriPickStartTime; ?>"
                                                           placeholder="Select Pickup زمان شروع"></td>
                                            </tr>
                                            <tr>
                                                <td> زمان پایان</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tFriPickEndTime"
                                                           id="tFriPickEndTime"
                                                           value="<?php echo $tFriPickEndTime; ?>"
                                                           placeholder="Select Pickup زمان پایان"></td>
                                            </tr>
                                            <tr>
                                                <td> هزینه</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" name="fFriPickUpPrice"
                                                           id="fFriPickUpPrice"
                                                           value="<?php echo $fFriPickUpPrice; ?>"
                                                           placeholder="Enter Price"
                                                           onchange="getpriceCheck(this.value)"></td>
                                            </tr>
                                        </table>

                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center"><b>شنبه</b></td>
                                            </tr>
                                            <tr>
                                                <td> زمان شروع</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tSatPickStartTime"
                                                           id="tSatPickStartTime"
                                                           value="<?php echo $tSatPickStartTime; ?>"
                                                           placeholder="Select Pickup زمان شروع"></td>
                                            </tr>
                                            <tr>
                                                <td> زمان پایان</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tSatPickEndTime"
                                                           id="tSatPickEndTime"
                                                           value="<?php echo $tSatPickEndTime; ?>"
                                                           placeholder="Select Pickup زمان پایان"></td>
                                            </tr>
                                            <tr>
                                                <td> هزینه</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" name="fSatPickUpPrice"
                                                           id="fSatPickUpPrice"
                                                           value="<?php echo $fSatPickUpPrice; ?>"
                                                           placeholder="Enter Price"
                                                           onchange="getpriceCheck(this.value)"></td>
                                            </tr>
                                        </table>
                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center"><b>یک شنبه</b></td>
                                            </tr>
                                            <tr>
                                                <td> زمان شروع</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tSunPickStartTime"
                                                           id="tSunPickStartTime"
                                                           value="<?php echo $tSunPickStartTime; ?>"
                                                           placeholder="Select Pickup زمان شروع"></td>
                                            </tr>
                                            <tr>
                                                <td> زمان پایان</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" readonly class=" form-control"
                                                           name="tSunPickEndTime"
                                                           id="tSunPickEndTime"
                                                           value="<?php echo $tSunPickEndTime; ?>"
                                                           placeholder="Select Pickup زمان پایان"></td>
                                            </tr>
                                            <tr>
                                                <td> هزینه</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" name="fSunPickUpPrice"
                                                           id="fSunPickUpPrice"
                                                           value="<?php echo $fSunPickUpPrice; ?>"
                                                           placeholder="Enter Price"
                                                           onchange="getpriceCheck(this.value)"></td>
                                            </tr>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label> شارژ شب روشن/خاموش <span class="red"> *</span> <i class="icon-question-sign"
                                                                                          data-placement="top"
                                                                                          data-toggle="tooltip"
                                                                                          data-original-title='This is a multiplier X  to the standard fares causing the fare to be higher than the standard fare during night time; i.e. if X is 1.2 during some point of time then the standard fare will be multiplied by 1.2 to get the final fare.'></i></label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" id="eNightStatus" onChange="showhidenighttime();"
                                           name="eNightStatus" <?php echo ($id != '' && $eNightStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                </div>
                            </div>
                        </div>

                        <div id="shownighttime" style="display:none;">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label> زمان شروع شارژ شب<span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" readonly class=" form-control" name="tNightStartTime"
                                           id="tNightStartTime" value="<?php echo $tNightStartTime; ?>"
                                           placeholder="Select Night زمان شروع">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label> زمان پایان شارژ شب<span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" readonly class=" form-control" name="tNightEndTime"
                                           id="tNightEndTime"
                                           value="<?php echo $tNightEndTime; ?>"
                                           placeholder="Select Night زمان پایان">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label> اضافه کردن زمان شب (X) <span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="fNightPrice" id="fNightPrice"
                                           value="<?php echo $fNightPrice; ?>" placeholder="Enter Price"
                                           onchange="getpriceCheck(this.value)">

                                </div>
                            </div>
                        </div>
                        <?php //} ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <label> چند مسافره روشن/خاموش <span class="red"> *</span> <i
                                            class="icon-question-sign"
                                            data-placement="top"
                                            data-toggle="tooltip"
                                            data-original-title='MultiPassenger is savarpool.'></i></label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" id="eMultiPassenger" name="eMultiPassenger"
                                           onChange="showhideMPP();" <?php echo ($id != '' && $eMultiPassenger == 'Yes') ? 'checked' : ''; ?>/>
                                </div>
                            </div>
                        </div>


                        <div id="multipassenger" style="display:none;">
                            <div class="row">
                                <div class="col-lg-12 main-table001">
                                    <div class="main-table001" id="MPPWrap">

                                        <?php
                                        if (count($multiPassengerPriceArray) === 0)
                                            $multiPassengerPriceArray[] = array(
                                                "seatsNumber" => '',
                                                "priceSurcharge" => ''
                                            );

                                        foreach ($multiPassengerPriceArray as $mpp) :
                                            ?>

                                            <table class="col-lg-2" id="MPPTable">
                                                <tr>
                                                    <td align="center"><b>اگر صندلی</b></td>
                                                </tr>
                                                <tr>
                                                    <td>تعداد صندلی</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" name="seatsNumber[]"
                                                               id="seatsNumber"
                                                               value="<?php echo $mpp['seatsNumber']; ?>"
                                                               placeholder="Seats Number">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>هزینه (X)</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                               name="priceSurcharge[]"
                                                               id="priceSurcharge"
                                                               value="<?php echo $mpp['priceSurcharge']; ?>"
                                                               placeholder="Surcharge">
                                                    </td>
                                                </tr>
                                            </table>
                                        <?php endforeach; ?>

                                    </div>

                                    <div class="main-table001">

                                        <table class="col-lg-2">
                                            <tr>
                                                <td align="center" valign="middle"
                                                    style="width:100%;height:130px;background-color:#eee;color:#fff;font-size:25pt;cursor:pointer"
                                                    onclick="AddNewMultiPassengerPrice()">+
                                                </td>
                                            </tr>

                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>


                    <?php if ($APP_TYPE != 'UberX') { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>تصویر نوع خودرو (تصویر خاکستری) <i class="icon-question-sign"
                                                                          data-placement="top"
                                                                          data-toggle="tooltip"
                                                                          data-original-title='This is used to represent the vehicle type as a icon in application.'></i></label>
                            </div>
                            <div class="col-lg-6">
                                <?php if ($vLogo != '') { ?>
                                    <img src="<?php echo $tconfig['tsite_upload_images_vehicle_type'] . "/" . $id . "/ios/3x_" . $vLogo; ?>"
                                         style="width:100px;height:100px;">
                                <? } ?>
                                <input type="file" class="form-control" name="vLogo" <?php echo $required_rule; ?>
                                       id="vLogo"
                                       placeholder="" style="padding-bottom: 39px;">
                                <br/>
                                [Note: Upload only png image size of 360px*360px.]
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>تصویر نوع خودرو (تصویر نارنجی) <i class="icon-question-sign"
                                                                         data-placement="top"
                                                                         data-toggle="tooltip"
                                                                         data-original-title='This is used to represent the vehicle type as a icon in application. Oragen icon is used to represent the vehicle type as a selected.'></i></label>
                            </div>
                            <div class="col-lg-6">
                                <?php if ($vLogo != '') { ?>
                                    <img src="<?php echo $tconfig['tsite_upload_images_vehicle_type'] . "/" . $id . "/ios/3x_hover_" . $vLogo; ?>"
                                         style="width:100px;height:100px;">
                                <? } ?>
                                <input type="file" class="form-control" name="vLogo1" <?php echo $required_rule; ?>
                                       id="vLogo1"
                                       placeholder="" style="padding-bottom: 39px;">
                                <br/>
                                [Note: Upload only png image size of 360px*360px.]
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($APP_TYPE == 'UberX') { ?>
                        <div id="show-in-fixed">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>مقدار مجاز <span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name='eAllowQty' id="AllowQty"
                                            onchange="get_AllowQty(this.value)">
                                        <option value="Yes"<?
                                        if ($eAllowQty == "Yes") {
                                            echo 'selected="selected"';
                                        }
                                        ?>>بله
                                        </option>
                                        <option value="No"<?
                                        if ($eAllowQty == "No") {
                                            echo 'selected="selected"';
                                        }
                                        ?>>خیر
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row" id="iMaxQty">
                                <div class="col-lg-12">
                                    <label>حداکثر تعداد<span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="iMaxQty" id="iMaxQty"
                                           value="<?php echo $iMaxQty; ?>" onchange="getpriceCheck(this.value)">
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit"
                                   value="ویرایش">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen"
      href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
<!--For Faretype-->
<script>
    $('[data-toggle="tooltip"]').tooltip();
    window.onload = function () {

        var vid = $("#vid").val();
        var eFareType = $("#eFareType").val();
        var AllowQty = $("#AllowQty").val();
        if (vid == '') {
            get_faretype('Regular');
        } else {
            get_faretype(eFareType);
        }

        if (AllowQty == 'Yes') {
            $("#iMaxQty").show();
            $("#iMaxQty").attr('required', 'required');
        } else {
            $("#iMaxQty").hide();
            $("#iMaxQty").removeAttr('required');

        }

        var appTYpe = '<?php echo $APP_TYPE;?>';

        if (appTYpe == 'UberX' && eFareType == 'Regular') {
            $("#Regular_div2").show();
            $("#Regular_div1").show();

        } else if (appTYpe == 'Ride' || appTYpe == 'Delivery' || appTYpe == 'Ride-Delivery') {
            $("#Regular_div2").show();
            $("#Regular_div1").show();

        } else {
            $("#Regular_div2").hide();
            $("#Regular_div1").show();

        }
    };
    var successMSG1 = '<?php echo $success;?>';

    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }

    function get_faretype(val) {
        console.log(val);
        var appTYpe = '<?php echo $APP_TYPE;?>';
        if (appTYpe == 'UberX') {

            if (val == "Fixed") {
                $("#fixed_div").show();
                $("#Regular_div1").hide();
                $("#Regular_div2").hide();
                $("#hide-priceHour").hide();
                $("#hide-basefare").hide();
                $("#hide-minimumfare").hide();
                $("#hide-price").hide();
                $("#hide-km").hide();
                $("#show-in-fixed").show();

                $("#fFixedFare").attr('required', 'required');
                $("#iMaxQty").attr('required', 'required');
                $("#fPricePerKM").removeAttr('required');
                $("#fPricePerMin").removeAttr('required');
                $("#fWaitingPricePerMin").removeAttr('required');
                $("#iBaseFare").removeAttr('required');
                $("#iPersonSize").removeAttr('required');
                $("#fPickUpPrice").removeAttr('required');
                $("#tPickStartTime").removeAttr('required');
                $("#tPickEndTime").removeAttr('required');
                $("#tNightStartTime").removeAttr('required');
                $("#tNightEndTime").removeAttr('required');
                $("#fPricePerHour").removeAttr('required');
                $("#iMinFare").removeAttr('required');
            } else if (val == "Regular") {

                $("#fixed_div").hide();
                $("#Regular_div2").show();
                $("#Regular_div1").show();
                $("#show-in-fixed").hide();
                $("#hide-priceHour").show();
                $("#hide-km").show();
                $("#hide-basefare").show();
                $("#hide-minimumfare").show();
                $("#hide-price").show();
                $("#fPricePerHour").removeAttr('required');
                $("#iMaxQty").removeAttr('required');
                $("#fFixedFare").removeAttr('required');
                $("#fPricePerKM").attr('required', 'required');
                $("#iMinFare").attr('required', 'required');
                $("#fPricePerMin").attr('required', 'required');
                $("#fWaitingPricePerMin").attr('required', 'required');
                $("#iBaseFare").attr('required', 'required');
                $("#iPersonSize").attr('required', 'required');
                $("#fPickUpPrice").attr('required', 'required');
                $("#tPickStartTime").attr('required', 'required');
                $("#tPickEndTime").attr('required', 'required');
                $("#tNightStartTime").attr('required', 'required');
                $("#tNightEndTime").attr('required', 'required');


            } else {

                $("#fixed_div").hide();
                $("#Regular_div1").show();
                $("#Regular_div2").hide();
                $("#hide-basefare").hide();
                $("#hide-minimumfare").hide();
                $("#hide-price").hide();
                $("#hide-km").hide();
                $("#hide-priceHour").show();
                $("#show-in-fixed").hide();
                $("#fFixedFare").removeAttr('required');
                $("#iMaxQty").removeAttr('required');
                $("#iMinFare").removeAttr('required');
                $("#fPricePerHour").attr('required', 'required');


                /* $("#fPricePerKM").attr('required','required');
                $("#fPricePerMin").attr('required','required');
                $("#iBaseFare").attr('required','required');
                $("#iPersonSize").attr('required','required');
                $("#fPickUpPrice").attr('required','required');
                $("#tPickStartTime").attr('required','required');
                $("#tPickEndTime").attr('required','required');
                $("#tNightStartTime").attr('required','required');
                $("#tNightEndTime").attr('required','required'); */

                $("#iBaseFare").removeAttr('required');
                $("#fPricePerKM").removeAttr('required');
                $("#fPricePerMin").removeAttr('required');
                $("#fWaitingPricePerMin").removeAttr('required');
                $("#iPersonSize").removeAttr('required');
                $("#fPickUpPrice").removeAttr('required');
                $("#tPickStartTime").removeAttr('required');
                $("#tPickEndTime").removeAttr('required');
                $("#tNightStartTime").removeAttr('required');
                $("#tNightEndTime").removeAttr('required');


            }
        } else {
            $("#Regular_div1").show();
            $("#Regular_div2").show();
            $("#fFixedFare").hide();
            $("#show-in-fixed").show();
            $("#hide-priceHour").show();
            $("#fFixedFare").removeAttr('required');
            $("#iMaxQty").removeAttr('required');
            $("#fPricePerHour").removeAttr('required');
            $("#fPricePerKM").attr('required', 'required');
            $("#iMinFare").attr('required', 'required');
            $("#fPricePerMin").attr('required', 'required');
            $("#fWaitingPricePerMin").attr('required', 'required');
            $("#iBaseFare").attr('required', 'required');
            $("#iPersonSize").attr('required', 'required');
            $("#fPickUpPrice").attr('required', 'required');
            $("#tPickStartTime").attr('required', 'required');
            $("#tPickEndTime").attr('required', 'required');
            $("#tNightStartTime").attr('required', 'required');
            $("#tNightEndTime").attr('required', 'required');

        }
    }

    function get_AllowQty(val) {
        if (val == "Yes") {
            $("#iMaxQty").show();
            $("#iMaxQty").attr('required', 'required');

        } else {

            $("#iMaxQty").hide();
            $("#iMaxQty").removeAttr('required');
        }


    }
</script>
<!--For Faretype End-->
<script>
    function changeCode(id) {
        var request = $.ajax({
            type: "POST",
            url: 'change_code.php',
            data: 'id=' + id,
            success: function (data) {
                document.getElementById("code").value = data;
                //window.location = 'profile.php';
            }
        });
    }

    function validate_email(id) {

        var request = $.ajax({
            type: "POST",
            url: 'validate_email.php',
            data: 'id=' + id,
            success: function (data) {
                if (data == 0) {
                    $('#emailCheck').html('<i class="icon icon-remove alert-danger alert">Already Exist,Select Another</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');
                } else if (data == 1) {
                    var eml = /^[-.0-9a-zA-Z]+@[a-zA-z]+\.[a-zA-z]{2,3}$/;
                    result = eml.test(id);
                    if (result == true) {
                        $('#emailCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                        $('input[type="submit"]').removeAttr('disabled');
                    } else {
                        $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Email</i>');
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    }
                }
            }
        });
    }

    function getpriceCheck(id) {
        /*var km_rs=document.getElementById('fPricePerKM').value;
        var min_rs=document.getElementById('fPricePerMin').value;
        var base_rs=document.getElementById('iBaseFare').value;
        var com_rs=document.getElementById('fCommision').value;
        if(km_rs != 0 && min_rs !=0 && base_rs != 0 && com_rs != 0)
        {
        }*/

        if (id > 0) {
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('#price').html('<i class="alert-danger alert"> You can not EnterAny price Zero or Letter</i>');
            $('input[type="submit"]').attr('disabled', 'disabled');
        }
    }

    function onlydigit(id) {
        var digi = /^[1-9]{1}$/;
        result = digi.test(id);
        if (result == true) {
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('#digit').html('<i class="alert-danger alert">Only Decimal Number less Than 10</i>');
            $('input[type="submit"]').attr('disabled', 'disabled');
        }

    }

    /*function checkDates() {
        if (tPickStartTime.val() != '' && tPickEndTime.val() != '') {
            if (Date.parse(tPickStartTime.val()) > Date.parse(tPickEndTime.val())) {
                alert('End date should be before start date');
                endDate.val(tPickStartTime.val());
            }
        }
    }*/


    /*$(function () {
                newDate = new Date('Y-M-D');
          $('#tPickStartTime').datetimepicker({
                    format: 'HH:mm:ss',
                    //minDate: moment().format('l'),
                    ignoreReadonly: true,
                    //sideBySide: true,
                });
    });

    $(function () {
                newDate = new Date('Y-M-D');
          $('#tPickEndTime').datetimepicker({
                    format: 'HH:mm:ss',
                    //minDate: moment().format('l'),
                    ignoreReadonly: true,
                    //sideBySide: true,
                })
    }); */
    $(function () {
        newDate = new Date('Y-M-D');
        $('#tMonPickStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tMonPickEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tTuePickStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tTuePickEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tWedPickStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tWedPickEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });


    $(function () {
        newDate = new Date('Y-M-D');
        $('#tThuPickStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tThuPickEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });


    $(function () {
        newDate = new Date('Y-M-D');
        $('#tFriPickStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tFriPickEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tSatPickStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tSatPickEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tSunPickStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tSunPickEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });


    $(function () {
        newDate = new Date('Y-M-D');
        $('#tNightStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
    });

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tNightEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        })
    });

    /*
    $(function () {
        $('#startTime, #endTime').datetimepicker({
            format: 'hh:mm',
            pickDate: false,
            pickSeconds: false,
            pick12HourFormat: false
        });
    });
    */
    /* $(document).ready(function() {
        $.validator.addMethod("tPickEndTime", function(value, element) {
            var startDate = $('#tPickStartTime').val();
            return Date.parse(startDate) <= Date.parse(value) || value == "";
        }, "* End date must be after start date");
        $('#vtype').validate();
    });*/

    function showhidepickuptime() {
        if ($('input[name=ePickStatus]').is(':checked')) {
            //alert('Checked');
            $("#showpickuptime").show();
        } else {
            //alert('Not checked');
            $("#showpickuptime").hide();
        }
    }

    function showHidePriceZone() {
        if ($('input[name=ePriceZone]').is(':checked')) {
            $("#showpricezone").show();
        } else {
            $("#showpricezone").hide();
        }
    }

    function showhideMPP() {
        if ($('input[name=eMultiPassenger]').is(':checked')) {
            //alert('Checked');
            $("#multipassenger").show();
        } else {
            //alert('Not checked');
            $("#multipassenger").hide();
        }
    }

    function showhidenighttime() {
        if ($('input[name=eNightStatus]').is(':checked')) {
            //alert('Checked');
            $("#shownighttime").show();
        } else {
            //alert('Not checked');
            $("#shownighttime").hide();
        }
    }

    showhidepickuptime();
    showHidePriceZone();
    showhidenighttime();


    function AddNewPriceZone() {
        $("#PriceZoneWrap div.input-group:eq(0)").clone().appendTo("#PriceZoneWrap").find("input").val('');
        return false;
    }

    function AddNewMultiPassengerPrice() {
        $("#MPPWrap table:eq(0)").clone().appendTo("#MPPWrap").find("input").val('');
    }
</script>