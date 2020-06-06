<?php
include_once('savar_check_permission.php');
if (checkPermission('VEHICLE_TYPE') == false)
    die('you dont`t have permission...');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'delete';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';

$script = 'VehicleType';
$adminArea = $_SESSION['sess_area'];
$Vehicle_type_name = ($APP_TYPE == 'Delivery') ? 'Deliver' : $APP_TYPE;

if ($Vehicle_type_name == "Ride-Delivery") {
    $vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver' or eType ='SchoolServices' )";
    if ($adminArea && $adminArea != -1) {
        $vehicle_type_sql .= " AND vSavarArea=" . $adminArea;
    }
    $data_drv = $obj->MySQLSelect($vehicle_type_sql);
} else {
    if ($APP_TYPE == 'UberX') {
        $vehicle_type_sql = "SELECT vt.*,vc.iVehicleCategoryId,vc.vCategory_EN from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' ";
        $data_drv = $obj->MySQLSelect($vehicle_type_sql);
    } else {
        $vehicle_type_sql = "SELECT * from  vehicle_type where eType='" . $Vehicle_type_name . "'";
        $data_drv = $obj->MySQLSelect($vehicle_type_sql);
    }
}

$vahicale_hdn_del_id = isset($_POST['vahicale_hdn_del_id']) ? $_POST['vahicale_hdn_del_id'] : '';
if ($action == 'delete' && $vahicale_hdn_del_id != '') {
    if ($vahicale_hdn_del_id != '') {
        if (SITE_TYPE != 'Demo') {
            $query = "DELETE FROM vehicle_type WHERE iVehicleTypeId ='" . $vahicale_hdn_del_id . "'";
            $obj->sql_query($query);
            redirect()
                ->adminUrl('vehicleTypes')
                ->setMessage('حذف نوع وسیله نقلیه با موفقیت انجام شد.');
        } else {
            redirect()
                ->adminUrl('vehicleTypes')
                ->setMessage('حذف نوع وسیله نقلیه با موفقیت انجام شد.');
        }
    }
}

$sql = "SELECT * FROM `savar_area`";
$vSavarAreaArray = $obj->MySQLSelect($sql);

$areaArray = array();
foreach ($vSavarAreaArray as $area) {
    $areaName = $area['sAreaName'] . ' ( ' . $area['sAreaNamePersian'] . ' )';
    if ($area['sActive'] == 'No') {
        $areaName = '<span class="red">' . $areaName . '</span>';
    }
    $areaArray[$area['aId']] = $areaName;
}
?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-right">نوع خودرو</h2>
                <?php if ($APP_TYPE != 'UberX') { ?>
                    <a class="btn btn-primary" href="<?php echo adminUrl('vehicleTypes', ['op' => 'form']); ?>">افزودن
                        نوع خودرو</a> <?php } ?>
            </div>
            <hr/>
            <div class="col-lg-12">
                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                    <thead>
                    <tr>
                        <th scope="col">نوع</th>
                        <th scope="col">محدوده</th>
                        <th scope="col">هزینه هر کیلومتر</th>
                        <th scope="col">هزینه هر دقیقه</th>
                        <th scope="col">کرایه پایه</th>
                        <th scope="col">کمیسیون (%)</th>
                        <th scope="col">ظرفیت</th>
                        <?php if ($Vehicle_type_name == "Ride-Delivery") echo '<th scope="col">نوع خودرو</th>'; ?>
                        <th scope="col">فعالیت</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < count($data_drv); $i++) { ?>
                        <tr class="gradeA">
                            <td><?php echo $data_drv[$i]['vVehicleType'] ?></td>
                            <td><?php echo @$areaArray[$data_drv[$i]['vSavarArea']] ?></td>
                            <?php if ($APP_TYPE != 'UberX') { ?>
                                <td><?php echo $data_drv[$i]['fPricePerKM'] ?></td>
                                <td><?php echo $data_drv[$i]['fPricePerMin'] ?></td>

                                <td><?php echo $data_drv[$i]['iBaseFare'] ?></td>
                                <td><?php echo $data_drv[$i]['fCommision'] ?></td>
                                <td><?php echo $data_drv[$i]['iPersonSize'] ?></td>
                                <?php if ($Vehicle_type_name == "Ride-Delivery") { ?>
                                    <td><?php echo $data_drv[$i]['eType']; ?></td>
                                <?php } ?>
                            <?php } ?>
                            <td>
                                <a class="text-info"
                                   href="<?php echo adminUrl('vehicleTypes',['op'=>'form','id'=>$data_drv[$i]['iVehicleTypeId']]); ?>"
                                   data-toggle="tooltip" title="Edit Vehicle Type">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="#" class="text-danger delete-vehicle-type"
                                   data-vehicle-type-id="<?php echo $data_drv[$i]['iVehicleTypeId']; ?>">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $(".delete-vehicle-type").click(function () {
            let id = $(this).attr('data-vehicle-type-id');
            var request = $.ajax({
                type: "POST",
                url: 'ajax_delete_vehicle_type.php',
                //data: 'id =' + id,
                data: {id: id},
                success: function (data) {
                    //alert(data);
                    if (data == true) {
                        //alert("Selected vehicle type is not delete because some of driver has used selected vehicle type.");
                        alert("This vehicle type can not be deleted because its in use by some vehicles. Please remove this vehicle type from those vehicles and delete after that.");
                        return false;
                    } else {
                        document.getElementById("vahicale_hdn_del_id_" + id).value = +id;
                        var strconfirm = confirm("Are you sure you want to delete?");
                        if (strconfirm == true) {
                            document.getElementById('delete_frm_' + id).submit();
                        } else {
                            return false;
                        }
                    }
                }
            });
        });
    });
</script>