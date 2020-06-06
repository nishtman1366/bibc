<?php
include_once('../common.php');

include_once('savar_check_permission.php');
if (checkPermission('ADMIN') == false)
    die('you dont`t have permission...');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script = 'Admin';
if ($_POST['action'] == 'delete' && $_POST['hdn_del_id'] != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE administrators SET eStatus = 'Deleted' WHERE iAdminId = '" . $_POST['hdn_del_id'] . "'";
        $obj->sql_query($query);
        setMessage('حذف مدیر با موفقیت انجام شد.');
        redirect()
            ->adminUrl('administrators')
            ->setMessage('حذف مدیر با موفقیت انجام شد.');
    } else {
        redirect()
            ->adminUrl('administrators')
            ->setMessage('حذف مدیر با موفقیت انجام شد.');
        exit;
    }
}
if ($_GET['iAdminId'] != '' && $_GET['status'] != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE administrators SET eStatus = '" . $_GET['status'] . "' WHERE iAdminId = '" . $_GET['iAdminId'] . "'";
        $obj->sql_query($query);
        redirect()
            ->adminUrl('administrators')
            ->setMessage('تغییر وضعیت مدیر با موفقیت انجام شد.');
    } else {
        redirect()
            ->adminUrl('administrators')
            ->setMessage('تغییر وضعیت مدیر با موفقیت انجام شد.');
        exit;
    }
}

$sql = "SELECT ad.*,ag.vGroup FROM administrators ad left join admin_groups ag on
			ad.iGroupId=ag.iGroupId
			where ad.eStatus != 'Delete' ORDER BY BINARY ad.vFirstName ASC";
$data_drv = $obj->MySQLSelect($sql);
?>
<div class="card">
    <div class="card-body">
        <div class="col-12">
            <h2 class="text-right">مدیران سیستم</h2>
            <a class="btn btn-primary" href="<?php echo adminUrl('administrators', ['op' => 'form']); ?>">افزودن
                ادمین</a>
        </div>
        <hr/>
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover"
                       id="dataTables-example">
                    <thead>
                    <tr>
                        <th scope="col">نام ادمین</th>
                        <th scope="col">ایمیل</th>
                        <!--<th>SERVICE LOCATION</th>-->
                        <th scope="col">نوع ادمین</th>
                        <th scope="col">موبایل</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < count($data_drv); $i++) { ?>
                        <tr class="gradeA">
                        <td><?php echo $data_drv[$i]['vFirstName'] . ' ' . $data_drv[$i]['vLastName']; ?></td>
                        <td><?php echo $generalobjAdmin->clearEmail($data_drv[$i]['vEmail']); ?></td>
                        <td><?php echo $data_drv[$i]['vGroup']; ?></td>
                        <td><?php echo $generalobjAdmin->clearPhone($data_drv[$i]['vContactNo']); ?></td>
                        <td>
                            <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                <?php if ($data_drv[$i]['eStatus'] == 'Active') {
                                    ?>
                                    <i class="fa fa-check text-success"></i>
                                    <?php
                                } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                    ?>
                                    <i class="fa fa-ban text-secondary"></i>
                                    <?php
                                } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
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
                        <!--    <td class="center" width="10%">
																<a href="admin_action.php?id=<? //=$vehicles[$i]['iAdminId'];?>">
																	<!--<button class="btn btn-primary">
																		<i class="icon-pencil icon-white"></i> Edit
																	</button>
																	<img src="img/edit-icon.png" alt="image">
																</a>
															</td>-->
                        <td>
                        <a class="text-primary"
                           href="<?php echo adminUrl('administrators', ['op' => 'form', 'id' => $data_drv[$i]['iAdminId']]); ?>"
                           data-toggle="tooltip" title="ویرایش">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                            <a class="text-success"
                               href="<?php echo adminUrl('administrators', ['iAdminId' => $data_drv[$i]['iAdminId'], 'status' => 'Active']); ?>"
                               data-toggle="tooltip" title="فعال سازی">
                                <i class="fa fa-check"></i>
                            </a>
                            <a class="text-secondary"
                               href="<?php echo adminUrl('administrators', ['iAdminId' => $data_drv[$i]['iAdminId'], 'status' => 'Inactive']); ?>"
                               data-toggle="tooltip" title="غیرفعال سازی">
                                <i class="fa fa-ban"></i>
                            </a>
                            <a class="text-danger" href="#"
                               onclick="$('#delete_form_<?php echo $data_drv[$i]['iAdminId']; ?>').submit()"
                               data-toggle="tooltip" title="حذف">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form name="delete_form" id="delete_form_<?php echo $data_drv[$i]['iAdminId']; ?>"
                                  method="post"
                                  action="<?php echo adminUrl('administrators'); ?>"
                                  onsubmit="return confirm_delete()">
                                <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                       value="<?php echo $data_drv[$i]['iAdminId']; ?>">
                                <input type="hidden" name="action" id="action" value="delete">
                            </form>
                            </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    function confirm_delete() {
        return confirm("Are You sure You want to Delete Driver?");
    }
</script>
