<?php
require_once(TPATH_CLASS . 'savar/jalali_date.php');
if (checkPermission('DRIVER') == false)
    die('you dont`t have permission...');

$actionType = "";
$generalobjAdmin->check_member_login();

//$sql = "select vLabel,vValue from language_label where vCode='PS'";
//$db_lbl = $obj->MySQLSelect($sql);
//foreach ($db_lbl as $key => $value) {
//    $langage_lbl_array[$value['vLabel']] = $value['vValue'];
//}

$adminArea = $_SESSION['sess_area'];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$res_id = isset($_REQUEST['res_id']) ? $_REQUEST['res_id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$script = 'Driver';

if ($iDriverId != '' && $status != '') {
    $sql = "SELECT register_driver.iDriverId from register_driver
	LEFT JOIN company on register_driver.iCompanyId=company.iCompanyId
	LEFT JOIN driver_vehicle on driver_vehicle.iDriverId=register_driver.iDriverId
	WHERE company.eStatus='Active' AND driver_vehicle.eStatus='Active' AND register_driver.iDriverId='" . $iDriverId . "'" . $ssl;
    if ($adminArea && $adminArea != -1) {
        $sql .= " AND company.iAreaId=" . $adminArea;
    }

    $Data = $obj->MySQLSelect($sql);

    if ($status == 'active') {
        $query = "UPDATE register_driver SET eStatus = 'inactive' WHERE iDriverId = '" . $iDriverId . "'";
        $obj->sql_query($query);

        LoggerVehicle($query);
        LoggerVehicle($_SESSION);
        /*
         * ارسال ایمیل برای راننده
         */
        //TODO send email to driver
//        $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
//        $db_status = $obj->MySQLSelect($sql);
//        $maildata['EMAIL'] = $db_status[0]['vEmail'];
//        $maildata['NAME'] = $db_status[0]['vName'] . ' ' . $db_status[0]['vLastName'];
//        //$maildata['LAST_NAME'] = $db_status[0]['vName'];
//        $status = ($db_status[0]['eStatus'] == "active") ? $langage_lbl_array['LBL_ACTIVE_TEXT'] : $langage_lbl_array['LBL_INACTIVE_TEXT'];
//        $maildata['DETAIL'] = $langage_lbl_array['LBL_YOUR_ACCOUNT_TEXT'] . " " . $status . ".";
//        $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);

        redirect()->adminUrl('drivers')->setMessage('راننده با موفقیت غیرفعال شد.');
    } else if (SITE_TYPE != 'Demo' && count($Data) > 0) {
        $query = "UPDATE register_driver SET eStatus = 'active' WHERE iDriverId = '" . $iDriverId . "'";
        $obj->sql_query($query);

        LoggerVehicle($query);
        LoggerVehicle($_SESSION);

        /*
         * ارسال ایمیل برای راننده
         */
//        $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
//        $db_status = $obj->MySQLSelect($sql);
//        $maildata['EMAIL'] = $db_status[0]['vEmail'];
//        $maildata['NAME'] = $db_status[0]['vName'];
//        //$maildata['LAST_NAME'] = $db_status[0]['vName'];
//        $status = ($db_status[0]['eStatus'] == "active") ? $langage_lbl_array['LBL_ACTIVE_TEXT'] : $langage_lbl_array['LBL_INACTIVE_TEXT'];
//        $maildata['DETAIL'] = $langage_lbl_array['LBL_YOUR_ACCOUNT_TEXT'] . " " . $status . ".<p>" . $langage_lbl_array['LBL_YOU_CAN_LOGIN_TEXT'] . "</p>";
//        $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);
        redirect()->adminUrl('drivers')->setMessage('راننده با موفقیت فعال شد.');
    } else {
        redirect()->adminUrl('drivers')->setMessage('راننده هیچ شرکت یا وسیله نقلیه ثبت شده ای ندارد.', 'warning');
    }
}

if ($action == 'delete' && $hdn_del_id != '') {
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE register_driver SET eStatus = 'Deleted' WHERE iDriverId = '" . $hdn_del_id . "'";
        $obj->sql_query($query);
        $action = "view";
        redirect()->adminUrl('drivers')->setMessage('حذف راننده با موفقیت انجام شد.');
    } else {
        redirect()->adminUrl('drivers')->setMessage('حذف راننده با موفقیت انجام شد.');
    }
}

if ($action == 'reset' && $res_id != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE register_driver SET iTripId='0',vTripStatus='NONE' WHERE iDriverId = '" . $res_id . "'";
        $obj->sql_query($query);
        $action = "view";
        redirect()->adminUrl('drivers')->setMessage('اطلاعات راننده با موفقیت بازنشانی شد.');
    } else {
        redirect()->adminUrl('drivers')->setMessage('اطلاعات راننده با موفقیت بازنشانی شد.');
    }
}

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLname = isset($_POST['vLname']) ? $_POST['vLname'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '1';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = $generalobj->encrypt($vPassword);
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$iCompanyid = isset($_REQUEST['iCompanyid']) ? $_REQUEST['iCompanyid'] : '';

$cmp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
}
$ssqlcmp = '';
if ($iCompanyid != '') {
    $ssqlcmp = " AND rd.iCompanyId ='$iCompanyid'";

}
if ($action == 'view') {

    $title = "Pending ";
    if ($actionType != "" && $actionType == "approve") {
        $title = "Approved ";
        $ssl = " AND rd.eStatus = 'active'";
    }
    $sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE 1=1 " . $ssl . $cmp_ssql . $ssqlcmp;
    if ($adminArea && $adminArea != -1) {
        $sql .= " AND c.iAreaId=" . $adminArea;
    }
    if ($iCompanyid == '')
        $sql .= " AND rd.eStatus != 'Deleted'";
    $data_drv = $obj->MySQLSelect($sql);
}

function LoggerVehicle($data)
{
    $text = '';
    if (is_array($data) || is_object($data))
        $text = print_r($data, true);
    else
        $text = $data;

    file_put_contents("LoggerVehicle.txt", $text . "\r\n................." . date('m/d/Y h:i:s a', time()) . "..............\r\n", FILE_APPEND);
}

if ($_GET['action'] && $_GET['action'] == 'export' && $_GET['exportType'] == 'excel') {
    $db_name = "k68ir_DB";
    $conn2 = new mysqli('localhost', 'k68ir_DB', 'Kamelia.irir*****', $db_name);
    $conn2->set_charset("utf8");

    if ($_GET['iCompanyid'] != "") {
        $setSql = "SELECT `iDriverId`, `vName`, `vLastName`,`iCompanyId`, `vEmail`, `tRegistrationDate`, `vPhone`, `vCity` FROM `register_driver` WHERE iCompanyId = '" . $_GET['iCompanyid'] . "'";
    } else {
        $setSql = "SELECT `iDriverId`, `vName`, `vLastName`,`iCompanyId`, `vEmail`, `tRegistrationDate`, `vPhone`, `vCity` FROM `register_driver` WHERE 1";
    }
    $setRec = mysqli_query($conn2, $setSql);

    $columnHeader = '';
    $columnHeader = "Sr NO" . "\t" . "نام راننده" . "\t" . "فامیلی راننده" . "\t" . "نمایندگی" . "\t" . "ایمیل" . "\t" . "ناریخ ثبت نام" . "\t" . "شماره موبایل" . "\t" . "شهر" . "\t";

    $setData = '';
    $ccc = 0;
    $ccc2 = 0;
    while ($rec = mysqli_fetch_row($setRec)) {
        $rowData = '';
        foreach ($rec as $value) {
            $ccc++;
            $ccc2++;
            if ($ccc == 1) {
                $ccc2 = $value;
                $value = '"' . $value . '"' . "\t";
                $rowData .= $value;
            } else {
                if ($ccc == 4) {
                    $sql = "SELECT vCompany FROM company where iCompanyId = '" . $value . "'";

                    //die($sql);
                    $data_drv2 = $obj->MySQLSelect($sql);
                    $value = '"' . $data_drv2[0]['vCompany'] . '"' . "\t";
                    $rowData .= $value;
                } else {
                    $value = '"' . $value . '"' . "\t";
                    $rowData .= $value;
                }
            }

        }
        $ccc = 0;
        $ccc2 = 0;
        $setData .= trim($rowData) . "\n";
    }
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=driver.xls");
    header('Content-Transfer-Encoding: binary');
    header("Pragma: no-cache");
    header("Expires: 0");
    echo chr(255) . chr(254) . iconv("UTF-8", "UTF-16LE//IGNORE", $columnHeader . "\n" . $setData . "\n");
    exit();
}
?>
<link rel="stylesheet" href="<?php assets('css/admin/amir.autocomplete.css'); ?>">
<div class="card">
    <div class="card-body">
        <div class="col-lg-12">
            <h2 class="text-right">راننده / حامل </h2>
            <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
            <a class="btn btn-primary" href="<?php echo adminUrl('drivers', ['op' => 'form']); ?>">افزودن راننده</a>
            <a class="btn btn-secondary"
               href="<?php echo adminUrl('drivers', ['action' => 'export', 'exportType' => 'excel', 'iCompanyid' => $_GET['iCompanyid']]); ?>">خروجی
                اکسل</a>
        </div>
    </div>
    <hr/>
    <div class="col-5">
        <div class="input-group">
            <span class="input-group-text border-left-0" style="border-radius: 0;">جستجوی راننده:</span>
            <input class="form-control border-right-0 border-left-0" type="text" id="searchDriver"
                   name="searchDriver"
                   placeholder="راننده"
                   autocomplete="off" style="border-radius: 0;" value="<?php echo $searchDriver ?>">
            <input type="hidden" id="iDriverId" name="iDriverId" value="<?php echo $iDriverId ?>">
            <button class="btn btn-primary border-right-0" id="btnShowDriver"
                    style="border-top-right-radius: 0;border-bottom-right-radius: 0;">نمایش
            </button>
        </div>
    </div>
    <div class="col-lg-12">
        <div>
            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="">Select Option</span> <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a href="#" class="small" data-value="Active" tabIndex="-1"><input type="checkbox"
                                                                                       id="checkbox"
                                                                                       checked="checked"/>&nbsp;Active</a>
                </li>
                <li><a href="#" class="small" data-value="Inactive" tabIndex="-1"><input type="checkbox"
                                                                                         id="checkbox"/>&nbsp;Inactive</a>
                </li>
            </ul>
        </div>
        <table class="table table-striped table-bordered table-hover admin-td-button">
            <thead>
            <tr>
                <th>نام راننده</th>
                <th>نام شرکت</th>
                <th>ایمیل</th>
                <th>تاریخ ثبت نام</th>
                <!--<th>SERVICE LOCATION</th>-->
                <th>موبایل</th>
                <!--<th>LANGUAGE</th>-->
                <th>شهر</th>
                <th>وضعیت</th>
                <th>ویرایش مدارک</th>
                <th>فعالیت</th>

            </tr>
            </thead>
            <tbody>
            <?php foreach ($data_drv as $driver) { ?>
                <tr class="gradeA">
                    <td><?php echo $driver['vName'] . ' ' . $driver['vLastName']; ?></td>
                    <td><?php echo $driver['companyFirstName']; ?></td>
                    <td><?php echo $generalobjAdmin->clearEmail($driver['vEmail']); ?></td>
                    <td data-order="<?php echo $driver['iDriverId']; ?>"><?php echo jdate('d-F-Y', strtotime($driver['tRegistrationDate'])); ?></td>
                    <!--<td class="center"><?php echo $driver['vServiceLoc']; ?></td>-->
                    <td><?php echo $generalobjAdmin->clearPhone($driver['vPhone']); ?></td>
                    <!--<td><?php echo $driver['vLang']; ?></td>-->
                    <td><?php echo $driver['vCity']; ?></td>
                    <td>
                        <?php if ($driver['eDefault'] != 'Yes') { ?>
                            <?php if ($driver['eStatus'] == 'active') {
                                ?>
                                <i class="fa fa-check text-success"></i>
                                <?php
                            } else if ($driver['eStatus'] == 'inactive') {
                                ?>
                                <i class="fa fa-ban text-secondary"></i>
                                <?php
                            } else if ($driver['eStatus'] == 'Deleted') {
                                ?>
                                <i class="fa fa-trash text-danger"></i>
                                <?php
                            } ?>
                            <?php
                        } else {
                            ?>
                            <i class="fa fa-check text-success"></i>
                            <?php
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($driver['eStatus'] != "Deleted") { ?>
                            <a href="<?php echo adminUrl('drivers', ['op' => 'documents', 'action' => 'edit', 'id' => $driver['iDriverId']]); ?>"
                               data-toggle="tooltip" title="مدارک راننده">
                                <i class="fa fa-file"></i>
                            </a>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($driver['eStatus'] != "Deleted") { ?>
                            <a href="<?php echo adminUrl('drivers', ['op' => 'form', 'id' => $driver['iDriverId']]) ?>"
                               data-toggle="tooltip" title="ویرایش راننده">
                                <i class="fa fa-pencil text-primary"></i>
                            </a>
                        <?php } ?>
                        <a href="<?php echo adminUrl('drivers', ['iDriverId' => $driver['iDriverId'], 'status' => 'inactive']); ?>"
                           data-toggle="tooltip" title="Active Driver">
                            <i class="fa fa-check text-success"></i>
                        </a>
                        <a href="<?php echo adminUrl('drivers', ['iDriverId' => $driver['iDriverId'], 'status' => 'active']); ?>"
                           data-toggle="tooltip" title="غیرفعال سازی راننده">
                            <i class="fa fa-ban text-secondary"></i>
                        </a>

                        <?php if ($driver['eStatus'] != "Deleted") { ?>
                            <a href="#" onclick="$('#delete_form').submit();" data-toggle="tooltip"
                               title="حذف راننده">
                                <i class="fa fa-trash text-danger"></i>
                            </a>
                            <form name="delete_form" id="delete_form" method="post"
                                  action="<?php echo adminUrl('drivers'); ?>"
                                  onSubmit="return confirm('Are you sure you want to delete <?php echo $driver['vName']; ?> <?php echo $driver['vLastName']; ?> record?')"
                                  class="margin0">
                                <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                       value="<?php echo $driver['iDriverId']; ?>">
                                <input type="hidden" name="action" id="action" value="delete">
                            </form>
                        <?php } ?>
                        <?php if ($driver['eStatus'] != "Deleted") { ?>
                            <a href="#" onclick="$('#reset_form').submit();" data-toggle="tooltip"
                               title="بازنشانی راننده">
                                <i class="fa fa-refresh text-warning"></i>
                            </a>
                            <form name="reset_form" id="reset_form" method="post" action=""
                                  onSubmit="return confirm('Are you sure ? You want to reset <?php echo $driver['vName']; ?> <?php echo $driver['vLastName']; ?> account?')"
                                  class="margin0">
                                <input type="hidden" name="action" id="action" value="reset">
                                <input type="hidden" name="res_id" id="res_id"
                                       value="<?php echo $driver['iDriverId']; ?>">
                            </form>
                        <?php } else { ?>
                            <label></label>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script src="<?php assets('js/admin/amir.autocomplete.js'); ?>"></script>
<script type="text/javascript">
    var options = ["Active"];
    $('.dropdown-menu a').on('click', function (event) {
        var $target = $(event.currentTarget),
            val = $target.attr('data-value'),
            $inp = $target.find('input'),
            idx;

        if ((idx = options.indexOf(val)) > -1) {
            options.splice(idx, 1);
            setTimeout(function () {
                $inp.prop('checked', false)
            }, 0);
        } else {
            options.push(val);
            setTimeout(function () {
                $inp.prop('checked', true)
            }, 0);
        }
        $(event.target).blur();
        var request = $.ajax({
            type: "POST",
            url: 'change_driver_list.php',
            data: {result: JSON.stringify(options)},
            success: function (data) {
                $("#data_drv001").html('');
                $("#data_drv001").html(data);
                //document.getElementById("code").value = data;
                //window.location = 'profile.php';
            }
        });
        return false;
    });
</script>
<script>
    $(document).ready(function () {
        $("#btnShowDriver").click(function () {
            if ($("#iDriverId").val() != '') {
                let url = '<?php echo adminUrl('drivers', ['op' => 'form', 'id' => 'driverId']); ?>';
                let url2 = url.replace('driverId', $("#iDriverId").val());
                window.open(url2);
                return false;
            }
        });
    });
</script>