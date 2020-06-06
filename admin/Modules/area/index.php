<?php
$generalobjAdmin->check_member_login();

include_once('savar_check_permission.php');
if (checkPermission('Area') == false)
    die('you dont`t have permission...');

$script = 'Area';

if ($_GET['id'] != '' && $_GET['Status'] != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE savar_area SET sActive = '" . $_GET['Status'] . "' WHERE aId = '" . $_GET['id'] . "'";
        $obj->sql_query($query);
        redirect()
            ->adminUrl('area')
            ->setMessage('تغییر وضعیت ناحیه با موفقیت انجام شد.');
    } else {
        redirect()
            ->adminUrl('area')
            ->setMessage('تغییر وضعیت ناحیه با موفقیت انجام شد.');
    }
}

if ($_POST['action'] == 'delete' && $_POST['hdn_del_id'] != '') {
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
    if (SITE_TYPE != 'Demo') {
        $query = "DELETE FROM `savar_area` WHERE  aId= '" . $_POST['hdn_del_id'] . "'";
        $obj->sql_query($query);
        redirect()
            ->adminUrl('area')
            ->setMessage('حذف اطلاعات ناحیه با موفقیت انجام شد.');
    } else {
        redirect()
            ->adminUrl('area')
            ->setMessage('حذف اطلاعات ناحیه با موفقیت انجام شد.');
    }
}

$sql = "SELECT * FROM `savar_area`";
$data_area = $obj->MySQLSelect($sql);
?>
<div id="card">
    <div class="card-body">
        <div class="col-lg-12">
            <h2 class="text-right">نواحی</h2>
            <a class="btn btn-primary"
               href="<?php echo adminUrl('area', ['op' => 'form']); ?>">ثبت ناحیه جدید</a>
        </div>
        <hr/>
        <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th>نام ناحیه</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data_area as $area) { ?>
                    <tr class="gradeA">
                        <td><?php echo $area['sAreaName'] . '(' . $area['sAreaNamePersian'] . ')'; ?>
                        </td>
                        <td><?php echo(($area['sActive'] == 'Yes') ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-ban text-secondary"></i>'); ?></td>
                        <td>
                            <a href="<?php echo adminUrl('area', ['op' => 'form', 'action' => 'edit', 'id' => $area['aId']]); ?>">
                                <i class="fa fa-pencil text-info"></i>
                            </a>
                            <a href="<?php echo adminUrl('area', ['id' => $area['aId'], 'Status' => 'Yes']); ?>">
                                <i class="fa fa-check text-success"></i>
                            </a>
                            <a href="<?php echo adminUrl('area', ['id' => $area['aId'], 'Status' => 'No']); ?>">
                                <i class="fa fa-ban text-secondary"></i>
                            </a>
                            <a href="#" onclick="$('#delete_form').submit();">
                                <i class="fa fa-trash text-danger"></i>
                            </a>
                            <form name="delete_form" id="delete_form" method="post" action=""
                                  onSubmit="return confirm('Are you sure you want to delete record?')"
                                  class="margin0">
                                <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                       value="<?php echo $area['aId']; ?>">
                                <input type="hidden" name="action" id="action" value="delete">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>