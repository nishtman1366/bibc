<?php
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$adminArea = $_SESSION['sess_area'];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$actionSearch = isset($_REQUEST['actionSearch']) ? $_REQUEST['actionSearch'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$script = "Vehicle";

if ($_GET['iDriverVehicleId'] != '' && $_GET['status'] != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE driver_vehicle SET eStatus = '" . $_GET['status'] . "' WHERE iDriverVehicleId = '" . $_GET['iDriverVehicleId'] . "'";
        $obj->sql_query($query);
        LoggerVehicle($query);
        LoggerVehicle($_SESSION);

        //TODO send email to driver
//        if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes') {
//            $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName
//			FROM driver_vehicle dv, register_driver rd, make m, model md, company c
//			WHERE
//			dv.eStatus != 'Deleted'
//			AND dv.iDriverId = rd.iDriverId
//			AND dv.iCompanyId = c.iCompanyId
//			AND dv.iModelId = md.iModelId
//			AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '" . $iDriverVehicleId . "'";
//            $data_email_drv = $obj->MySQLSelect($sql23);
//            $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
//            $maildata['NAME'] = $data_email_drv[0]['vName'];
//            $maildata['DETAIL'] = "Your Vehicle " . $data_email_drv[0]['vTitle'] . " For COMPANY " . $data_email_drv[0]['companyFirstName'] . " is temporarly " . $status;
//            $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);
//        }
        redirect()->adminUrl('vehicles')->setMessage('تغییر وضعیت وسیله نقلیه با موفقیت انجام شد.');
    } else {
        redirect()->adminUrl('vehicles')->setMessage('تغییر وضعیت وسیله نقلیه با موفقیت انجام شد.');
    }
//    if ($APP_TYPE == 'UberX') {
//        $sql = "SELECT dv.*,rd.vName, rd.vLastName,dv.vLicencePlate, c.vCompany FROM driver_vehicle dv, register_driver rd,company c
//		WHERE  dv.eStatus != 'Deleted'  AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId
//		" . $cmp_ssql;
//        $vehicles = $obj->MySQLSelect($sql);
//    } else {
//        $sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
//		FROM driver_vehicle dv, register_driver rd, make m, model md, company c
//		WHERE
//		dv.eStatus != 'Deleted'
//		AND dv.iDriverId = rd.iDriverId
//		AND dv.iCompanyId = c.iCompanyId
//		AND dv.iModelId = md.iModelId
//		AND dv.iMakeId = m.iMakeId";
//        $vehicles = $obj->MySQLSelect($sql);
//    }
}

if ($_POST['action'] == 'delete' && $_POST['hdn_del_id'] != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE driver_vehicle SET eStatus = 'Deleted' WHERE iDriverVehicleId = '" . $_POST['hdn_del_id'] . "'";
        $obj->sql_query($query);
    } else {
        redirect()->adminUrl('vehicles')->setMessage('حذف وسیله نقلیه با موفقیت انجام شد.');
    }
    $sql = "select iDriverId from driver_vehicle WHERE iDriverVehicleId = '" . $_POST['hdn_del_id'] . "'";
    $db_member = $obj->MySQLSelect($sql);

    $sql = "select count(iDriverVehicleId) as total from driver_vehicle WHERE iDriverId = '" . $db_member[0]['iDriverId'] . "' AND eStatus='Active'";
    $db_vehicle_NO = $obj->MySQLSelect($sql);

    if ($db_vehicle_NO[0]['total'] <= 0) {
        $query = "UPDATE register_driver SET eStatus = 'inactive' WHERE iDriverId = '" . $db_member[0]['iDriverId'] . "'";
        $obj->sql_query($query);
    }
    redirect()->adminUrl('vehicles')->setMessage('حذف وسیله نقلیه با موفقیت انجام شد.');
}

$cmp_ssql = "";
//$sql = "select * from company WHERE 1=1";
//if ($adminArea && $adminArea != -1) {
//    $sql .= " AND iAreaId=" . $adminArea;
//}

$ssql = '';
if ($actionSearch != '') {
    $iCompanyId = $_REQUEST['iCompanyId'];
    $iDriverId = $_REQUEST['iDriverId'];
    if ($iCompanyId != '') {
        if ($iCompanyId != '' && $iDriverId != '') {
            $ssql .= " AND dv.iDriverId = '$iDriverId' AND dv.iCompanyId = '$iCompanyId'";
        } else {
            $ssql .= " AND dv.iCompanyId = '$iCompanyId'";
        }
    }
}
$vehicles = [];
if ($APP_TYPE == 'UberX') {

    $sql = "SELECT dv.*,rd.vName, rd.vLastName,dv.vLicencePlate, c.vCompany FROM driver_vehicle dv, register_driver rd,company c
		WHERE  dv.eStatus != 'Deleted'  AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId
		" . $cmp_ssql;
    if ($adminArea && $adminArea != -1) {
        $sql .= " AND c.iAreaId=" . $adminArea;
    }
    $vehicles = $obj->MySQLSelect($sql);

} else {
    if ($adminArea && $adminArea != -1) {
        $cmp_ssql = " AND c.iAreaId=" . $adminArea;
    }
    $sql = "SELECT dv.*, m.vMake, md.vTitle, rd.vName, rd.vLastName, c.vCompany
		FROM driver_vehicle dv, register_driver rd, make m, model md, company c
		WHERE
		dv.eStatus != 'Deleted'
		AND dv.iDriverId = rd.iDriverId
		AND dv.iCompanyId = c.iCompanyId
		AND dv.iModelId = md.iModelId
		AND dv.iMakeId = m.iMakeId" . $cmp_ssql . $ssql . "
		ORDER BY dv.iDriverVehicleId DESC";
    $vehicles = $obj->MySQLSelect($sql);
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

?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-right">وسیله نقلیه</h2>
                <a href="<?php echo adminUrl('vehicles', ['op' => 'form']); ?>" class="btn btn-primary">افزودن وسیله
                    نقلیه</a>
                <hr/>
                <div class="col-lg-12">
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="POST">
                        <input type="hidden" name="actionSearch" id="actionSearch" value="search"/>
                        <div class="panel-heading">
                            <select name="iCompanyId" id="iCompanyId" class="form-control input-sm"
                                    style="width:18%;display:table-row-group;" onchange="driverList(this.value);">
                                <option value="">جست و جو بر اساس نام شرکت</option>
                                <?php
                                foreach (companies($adminArea) as $company) {
                                    ?>
                                    <option value="<?php echo $company['iCompanyId']; ?>"
                                            <?php if ($iCompanyId == $company['iCompanyId']){ ?>selected <? } ?>><?php echo $company['vCompany']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <select name="iDriverId" id="iDriverId" class="form-control input-sm"
                                    style="width:18%;display:table-row-group;">
                            </select>
                            <button class="btn btn-default" onClick="search_filters();">جست و جو</button>
                        </div>
                    </form>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                <tr>
                                    <th scope="col" class="d-none"></th>
                                    <th scope="col">وسیله نقلیه</th>
                                    <th scope="col">شرکت</th>
                                    <th scope="col">راننده</th>
                                    <th scope="col">وضعیت</th>
                                    <?php if ($APP_TYPE != 'UberX') { ?>
                                        <th>ویرایش اسناد</th>
                                    <?php } ?>
                                    <th scope="col">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($vehicles as $vehicle) { ?>
                                    <tr class="gradeA">
                                        <?php
                                        if ($APP_TYPE == 'UberX') {
                                            $vname = $vehicle['vLicencePlate'];
                                        } else {
                                            $vname = $vehicle['vTitle'];
                                        }
                                        ?>
                                        <td data-order="<?php echo $vehicle['iDriverVehicleId']; ?>"
                                            class="d-none"></td>
                                        <td><?php echo $vehicle['vMake'] . ' ' . $vname; ?></td>
                                        <td><?php echo $vehicle['vCompany']; ?></td>
                                        <td><?php echo $vehicle['vName'] . ' ' . $vehicle['vLastName']; ?></td>
                                        <td>
                                            <?php if ($vehicle['eStatus'] == 'Active') {
                                                ?>
                                                <i class="fa fa-check text-success"></i>
                                                <?php
                                            } else if ($vehicle['eStatus'] == 'Inactive') {
                                                ?>
                                                <i class="fa fa-ban text-secondary"></i>
                                                <?php
                                            } else if ($vehicle['eStatus'] == 'Deleted') {
                                                ?>
                                                <i class="fa fa-trash text-danger"></i>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                        <?php if ($APP_TYPE != 'UberX') { ?>
                                            <td>
                                                <a href="<?php echo adminUrl('vehicle_document_action', [
                                                    'id' => $vehicle['iDriverVehicleId'],
                                                    'vehicle' => $vehicle['vMake']
                                                ]); ?>" data-toggle="tooltip" title="ویرایش اسناد">
                                                    <i class="fa fa-file"></i>
                                                </a>
                                            </td>
                                        <?php } ?>
                                        <td>
                                            <a class="text-primary" href="<?php echo adminUrl('vehicles', [
                                                'op' => 'form',
                                                'id' => $vehicle['iDriverVehicleId'],
                                                'vehicle' => $vehicle['vMake'],
                                            ]); ?>" data-toggle="tooltip" title="ویرایش وسیله نقلیه">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a class="text-success" href="<?php echo adminUrl('vehicles', [
                                                'iDriverVehicleId' => $vehicle['iDriverVehicleId'],
                                                'status' => 'Active'
                                            ]); ?>" data-toggle="tooltip" title="فعال سازی وسیله نقلیه">
                                                <i class="fa fa-check"></i>
                                            </a>
                                            <a class="text-secondary" href="<?php echo adminUrl('vehicles', [
                                                'iDriverVehicleId' => $vehicle['iDriverVehicleId'],
                                                'status' => 'Inactive'
                                            ]); ?>" data-toggle="tooltip" title="غیرفعال سازی وسیله نقلیه">
                                                <i class="fa fa-ban"></i>
                                            </a>
                                            <a class="text-danger" href="#" onclick="$('#delete_form').submit();"
                                               data-toggle="tooltip"
                                               title="حذف وسیله نقلیه">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <form name="delete_form" id="delete_form" method="post"
                                                  action="<?php echo adminUrl('vehicles'); ?>"
                                                  onSubmit="return confirm_delete()" class="margin0">
                                                <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                                       value="<?php echo $vehicle['iDriverVehicleId']; ?>">
                                                <input type="hidden" name="action" id="action" value="delete">
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!--TABLE-END-->
        </div>
    </div>
</div>
<script>
    function confirm_delete() {
        var confirm_ans = confirm("Are You sure You want to Delete this Vehicle?");
        return confirm_ans;
        document.getElementById('delete_form').submit();
    }

    function changeCode(id) {
        var request = $.ajax({
            type: "POST",
            url: 'change_code.php',
            data: 'id=' + id,
            success: function (data) {
                document.getElementById("code").value = data;
            }
        });
    }

    $('#iDriverId').hide();

    function driverList(companyId, iDriverId) {
        var request = $.ajax({
            type: "POST",
            url: 'ajax_driver_list.php',
            data: {id: companyId, iDriverId: iDriverId},
            success: function (data) {
                if (data != '') {
                    $('#iDriverId').show();
                    $('#iDriverId').html(data);
                } else {
                    $('#iDriverId').hide();
                }
            }
        });
    }

    function search_filters() {
        document.frmsearch.action = "";
        document.frmsearch.submit();
    }
</script>
<?php
if ($actionSearch != '') {
    ?>
    <script> $('#iDriverId').show();
        var iDriverId = '<?php echo $iDriverId?>';
        var iCompanyId = '<?php echo $iCompanyId?>';
        driverList(iCompanyId, iDriverId);
    </script>
<?php }
?>
