<?php
include_once('../common.php');
require_once(TPATH_CLASS . 'savar/jalali_date.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$start = @jdate("Y");
$end = '1370';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'driver_vehicle';
/*if ($_SESSION['sess_user'] == 'driver') {
    $sql = "select iCompanyId from `register_driver` where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
}*/
$script = 'Vehicle';
/*if ($_SESSION['sess_user'] == 'company') {

    $sql = "select * from register_driver where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
    $db_drvr = $obj->MySQLSelect($sql);
}*/
$sql = "select * from driver_vehicle where iDriverVehicleId = '" . $id . "' ";
$db_mdl = $obj->MySQLSelect($sql);

$sql = "select * from driver_vehicle where iDriverVehicleId = '" . $id . "' ";
$db_driver = $obj->MySQLSelect($sql);

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
$iColor = isset($_POST['iColor']) ? $_POST['iColor'] : '';
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
//echo "<pre>";
//print_R($_REQUEST);
//exit;
//die;

$sql = "SELECT * from make WHERE eStatus='Active' ORDER By vMake ASC";
$db_make = $obj->MySQLSelect($sql);

$sql = "SELECT * from company WHERE eStatus='Active'";
$db_company = $obj->MySQLSelect($sql);

if (isset($_POST['submit'])) {
    /*
            if($vLicencePlate != '' && check_plate() == false)
            {
                $error = $langage_lbl['LBL_LICENCE_PLATE_EXIST'];
            }
            if($vLicencePlate_city == '' || $vLicencePlate_place2 == '' ||  $vLicencePlate_alphabet == '' ||  $vLicencePlate_place1 == '' )
            {
                $error = $langage_lbl['LBL_LICENCE_PLATE_EMPTY'];
            }
    */
    if (SITE_TYPE == 'Demo') {
        $error_msg = "Edit / Delete Record Feature has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.";
        header("Location:form.php?id=" . $id . "&error_msg=" . $error_msg . "&success=2");
        exit;
    }
    if (!isset($_REQUEST['vCarType'])) {
        redirect()->adminUrl('vehicles', ['op' => 'form'])->setMessage('شما باید حداقل یک نوع خودرو برای وسیله نقلیه انتخاب کنید.');
        exit;
    }

    if ($APP_TYPE == 'UberX') {
        $vLicencePlate = 'My Services';

    } else {

        $vLicencePlate = $vLicencePlate;
    }


    $q = "INSERT INTO ";
    $where = '';
    //echo "<pre>";print_R($_REQUEST);exit;

    if ($action == 'Edit') {

        $str = ' ';
    } else {
        $eStatus = 'Active';
    }

    $cartype = implode(",", $_REQUEST['vCarType']);
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iDriverVehicleId` = '" . $id . "'";
    }

    $query = $q . " `" . $tbl_name . "` SET
		`iModelId` = '" . $iModelId . "',
		`vLicencePlate` = '" . $vLicencePlate_local . "',
		`vLicencePlate_local` = '" . $vLicencePlate_local . "',
		`iYear` = '" . $iYear . "',
		`iMakeId` = '" . $iMakeId . "',
		`iColor` = '" . $iColor . "',
		`iCompanyId` = '" . $iCompanyId . "',
		`iDriverId` = '" . $iDriverId . "',
		`eStatus` = '" . $eStatus . "',
		`vCarType` = '" . $cartype . "' $str"
        . $where;
    $obj->sql_query($query);
    //echo"<pre>";print_r($query);exit;

    if ($id != "" && $db_mdl[0]['eStatus'] != $eStatus) {
        //echo $db_mdl[0]['eStatus']; die;
        //TODO send email for vehicle
//        if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes') {
//            $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName
//					FROM driver_vehicle dv, register_driver rd, make m, model md, company c
//					WHERE
//					  dv.eStatus != 'Deleted'
//					  AND dv.iDriverId = rd.iDriverId
//					  AND dv.iCompanyId = c.iCompanyId
//					  AND dv.iModelId = md.iModelId
//					  AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '" . $id . "'";
//            $data_email_drv = $obj->MySQLSelect($sql23);
//            $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
//            $maildata['NAME'] = $data_email_drv[0]['vName'];
//            //$maildata['LAST_NAME'] = $vehicles[0]['companyFirstName'];
//            $maildata['DETAIL'] = "Your Vehicle " . $data_email_drv[0]['vTitle'] . " For COMPANY " . $data_email_drv[0]['companyFirstName'] . " is temporarly " . $eStatus;
//            $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);
//        }
    }

    $id = ($id != '') ? $id : mysqli_insert_id($condbc);

    if ($action == "Add") {
        //TODO send email for vehicle
//        $sql = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
//        $db_compny = $obj->MySQLSelect($sql);
//
//        $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
//        $db_status = $obj->MySQLSelect($sql);
//
//        $maildata['EMAIL'] = $db_status[0]['vEmail'];
//        $maildata['NAME'] = $db_status[0]['vName'] . " " . $db_status[0]['vLastName'];
//        //$maildata['LAST_NAME'] = $db_compny[0]['vName'];
//        //$maildata['DETAIL']="Your Vehicle is Added For ".$db_compny[0]['vCompany']." and will process your document and activate your account ";
//        $maildata['DETAIL'] = "Thanks for adding your vehicle.<br />We will soon verify and check it's documentation and proceed ahead with activating your account.<br />We will notify you once your account become active and you can then take rides with passengers.";
//
//        $generalobj->send_email_user("VEHICLE_BOOKING", $maildata);
        //print_R($maildata);
    }
    redirect()
        ->adminUrl('vehicles')
        ->setMessage('اطلاعات وسیله نقلیه با موفقیت ثبت شد.');
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

            $plate_split = explode('|', $vLicencePlate_local);
            if (count($plate_split) > 0 && $plate_split[0] = "IRAN") {
                $vLicencePlate_place1 = $plate_split[1];
                $vLicencePlate_alphabet = $plate_split[2];
                $vLicencePlate_place2 = $plate_split[3];
                $vLicencePlate_city = $plate_split[4];

                $iYear = $value['iYear'];
                $iColor = $value['iColor'];

                // added by seyyed amir
                $mdate = gregorian_to_jalali($iYear, 9, 1);
                $iYear = $_POST['iYear'] = $mdate[0];

                $eCarX = $value['eCarX'];
                $eCarGo = $value['eCarGo'];
                $iDriverId = $value['iDriverId'];
                $vCarType = $value['vCarType'];
            }

            //$iYear = $value['iYear'];
            //$iColor = $value['iColor'];
            //$eCarX = $value['eCarX'];
            //$eCarGo = $value['eCarGo'];
            //$iDriverId = $value['iDriverId'];
            //$vCarType = $value['vCarType'];
            $iCompanyId = $value['iCompanyId'];
            $eStatus = $value['eStatus'];
        }
    }
}
print_r($vCarType);
$vCarTyp = explode(",", $vCarType);

$Vehicle_type_name = ($APP_TYPE == 'Delivery') ? 'Deliver' : $APP_TYPE;
if ($Vehicle_type_name == "Ride-Delivery") {
    $vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver' or eType ='SchoolServices' )";
    $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
} else {
    if ($Vehicle_type_name == 'UberX') {
        $vehicle_type_sql = "SELECT vt.*,vc.iVehicleCategoryId,vc.vCategory_EN from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' ";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
    } else {
        $vehicle_type_sql = "SELECT * from  vehicle_type where eType='" . $Vehicle_type_name . "'";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
    }
}
?>
<div class="card">
    <div class="card-body">
        <div class="col-12">
            <h2 class="text-right">ثبت اطلاعات وسیله نقلیه</h2>
            <a href="<?php echo adminUrl('vehicles'); ?>" class="btn btn-secondary">
                بازگشت به لیست
            </a>
        </div>
    </div>
    <hr/>
    <div class="col-12">
        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
            <?php if ($APP_TYPE != 'UberX') { ?>
                <div class="row">
                    <div class="form-group col-12 col-md-2">
                        <label for="iMakeId">خودرو<span class="red"> *</span></label>
                        <select name="iMakeId" id="iMakeId" class="form-control"
                                onChange="get_model(this.value, '')" required>
                            <option value="">انتخاب خودرو</option>
                            <?php for ($j = 0; $j < count($db_make); $j++) { ?>
                                <option value="<?php echo $db_make[$j]['iMakeId'] ?>" <?php if ($iMakeId == $db_make[$j]['iMakeId']) { ?> selected <?php } ?>><?php echo $db_make[$j]['vMake'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <label for="iModelId">مدل<span class="red"> *</span></label>
                        <div id="carmdl">
                            <select name="iModelId" id="iModelId" class="form-control" required>
                                <option value="">انتخاب مدل خودرو</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <label for="iColor">رنگ<span class="red"> *</span></label>
                        <div id="carmdl">
                            <input type="text" class="form-control" name="iColor" id="iColor"
                                   value="<?php echo $iColor ?>"/>
                        </div>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <label for="iYear">سال<span class="red"> *</span></label>
                        <select name="iYear" id="iYear" class="form-control" required>
                            <option value="">انتخاب سال</option>
                            <?php for ($j = $start; $j >= $end; $j--) { ?>
                                <option value="<?php echo $j ?>" <?php if ($iYear == $j) { ?> selected <? } ?>><?php echo $j ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class=" col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text border-left-0" style="border-radius: 0;">پلاک خودرو:</span>
                            <input type="text" class="form-control pelakinput" name="vLicencePlate_place1"
                                   id="vLicencePlate_place1" value="<?php echo $vLicencePlate_place1; ?>"
                                   placeholder="00" required>
                            <input type="text" class="form-control pelakinput" name="vLicencePlate_alphabet"
                                   id="vLicencePlate_alphabet"
                                   value="<?php echo $vLicencePlate_alphabet; ?>" placeholder="الف"
                                   required>
                            <input type="text" class="form-control pelakinput" name="vLicencePlate_place2"
                                   id="vLicencePlate_place2" value="<?php echo $vLicencePlate_place2; ?>"
                                   placeholder="000" required>

                            <input type="text" class="form-control pelakinput" name="vLicencePlate_city"
                                   id="vLicencePlate_city" value="<?php echo $vLicencePlate_city; ?>"
                                   placeholder="کد شهر" required>
                            <span id="plate_warning" class="error-text"></span>
                            </b>
                            <b><span id="plate_warning"></span></b>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <div class="form-group col-12 col-md-6">
                    <label for="iCompanyId">شرکت<span class="red"> *</span></label>
                    <select name="iCompanyId" id="iCompanyId" onChange="get_driver(this.value, '')"
                            class="form-control" required>
                        <option value="">انتخاب شرکت</option>
                        <?php for ($j = 0; $j < count($db_company); $j++) { ?>
                            <option value="<?php echo $db_company[$j]['iCompanyId'] ?>" <?php if ($iCompanyId == $db_company[$j]['iCompanyId']) { ?> selected <?php } ?>><?php echo $db_company[$j]['vCompany'] ?></option>

                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="driver"><?php echo $langage_lbl['LBL_VEHICLE_DRIVER_TXT_ADMIN']; ?> <span
                                class="red">*</span></label>
                    <select name="iDriverId" id="driver" class="form-control" required>
                        <option value=""><?php echo $langage_lbl['LBL_CHOOSE_DRIVER_ADMIN']; ?> </option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>نوع خودرو <span class="red"> *</span></label>
                <div class="alert alert-danger alert-dismissable" style="display:none;" id="car_error">
                    <button class="close" type="button" id="cartypeClosed">×</button>
                    شما باید حداقل یک نوع ماشین را انتخاب کنید
                </div>
                <div class="btn-group btn-group-toggle required" data-toggle="buttons">
                    <?php foreach ($vehicle_type_data as $key => $value) { ?>
                        <label class="btn btn-info">
                            <input type="checkbox" name="vCarType"
                                   class="vehicle-type" <?php echo((in_array($value['iVehicleTypeId'], $vCarType)) ? 'checked' : ''); ?>
                                   autocomplete="off" value="<?php echo $value['iVehicleTypeId'] ?>">
                            <?php echo(($Vehicle_type_name == 'UberX') ? $value['vCategory_PS'] . '-' . $value['vVehicleType'] : $value['vVehicleType']) ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group">
                <label>وضعیت</label>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-secondary active">
                        <input type="radio" name="eStatus" id="option1"
                               autocomplete="off" <?php echo ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>>
                        فعال
                    </label>
                    <label class="btn btn-secondary">
                        <input type="radio" name="eStatus" id="option2"
                               autocomplete="off" <?php echo ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>>
                        غیرفعال
                    </label>
                </div>
            </div>
            <input type="submit" class="btn btn-info" onClick="return chk1();" name="submit"
                   id="submit" value="ثبت اطلاعات">
        </form>
    </div>
    <?php if ($action == 'Edit') { ?>
        <script>
            window.onload = function () {
                get_model('<?php echo $db_mdl[0]['iMakeId']; ?>', '<?php echo $db_mdl[0]['iModelId']; ?>');
                //get_driver('<?php echo $db_driver[0]['iCompanyId']; ?>', '<?php echo $db_driver[0]['iDriverId']; ?>');
                get_driver('<?php echo $iCompanyId; ?>', '<?php echo $iDriverId; ?>');
            };
        </script>
    <? } ?>
    <script>

        function chk1() {
            // var a = $('div.checkbox-group.required :checkbox:checked').length;
            var a = $('.vehicle-type:checked').length;
            if (a > 0) {
                return true;
            } else {
                $("#car_error").show();
                return false;
            }
        }


        $('#cartypeClosed').click(function () {

            $("#car_error").hide();

        });

        function get_model(model, modelid) {

            $("#carmdl").html('Wait...');
            var request = $.ajax({
                type: "POST",
                url: '../ajax_find_model.php',
                data: "action=get_model&model=" + model + "&iModelId=" + modelid,
                success: function (data) {
                    $("#carmdl").html(data);
                }
            });
            request.fail(function (jqXHR, textStatus) {
                alert("Request failed: " + textStatus);
            });
        }

        function get_driver(company, companyid) {
            //alert(company);
            $("#driver").html('Wait...');
            var request = $.ajax({
                type: "POST",
                url: '../ajax_find_driver.php',
                data: "action=get_driver&company=" + company + "&iDriverId=" + companyid,
                success: function (data) {
                    $("#driver").html(data);
                }
            });

            request.fail(function (jqXHR, textStatus) {
                alert("Request failed: " + textStatus);
            });
        }

        function check_licence_plate(plate, id1 = '') {
            var request = $.ajax({
                type: "POST",
                url: '../ajax_find_plate.php',
                data: "plate=" + plate + "&id=" + id1,
                success: function (data) {
                    if ($.trim(data) == 'yes') {
                        $('input[type="submit"]').removeAttr('disabled');
                        $("#plate_warning").html("");
                    } else {
                        $("#plate_warning").html(data);
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    }
                }
            });
        }
    </script>
