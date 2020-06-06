<?php
include_once('savar_check_permission.php');
if (checkPermission('COMPANY') == false)
    die('you dont`t have permission...');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$script = "Company";

if ($_GET['iCompanyId'] != '' && $_GET['status'] != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE company SET eStatus = '" . $_GET['status'] . "' WHERE iCompanyId = '" . $_GET['iCompanyId'] . "'";
        $obj->sql_query($query);
        redirect()
            ->adminUrl('companies')
            ->setMessage('تغییر وضعیت شرکت با موفقیت انجام شد.');
    } else {
        redirect()
            ->adminUrl('companies')
            ->setMessage('تغییر وضعیت شرکت با موفقیت انجام شد.');
    }
    //TODO send email to company about changed status
//    $sql = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "' ORDER BY BINARY NAME ASC";
//    $db_status = $obj->MySQLSelect($sql);
//    $maildata['EMAIL'] = $db_status[0]['vEmail'];
//    $maildata['NAME'] = $db_status[0]['vCompany'];
//
//    //$maildata['DETAIL']="Your Account is ".$db_status[0]['eStatus'];
//    $status = ($db_status[0]['eStatus'] == "Active") ? $langage_lbl_array['LBL_ACTIVE_TEXT'] : $langage_lbl_array['LBL_INACTIVE_TEXT'];
//    $maildata['DETAIL'] = $langage_lbl_array['LBL_YOUR_ACCOUNT_TEXT'] . " " . $status . ".";
//
//    $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);
}
if ($_POST['action'] == 'delete' && $_POST['hdn_del_id'] != '') {
    $query = "UPDATE company SET eStatus = 'Deleted' WHERE iCompanyId = '" . $_POST['hdn_del_id'] . "'";
    $obj->sql_query($query);
    redirect()
        ->adminUrl('companies')
        ->setMessage('حذف شرکت با موفقیت انجام شد.');
}

$sql = "SELECT * FROM company WHERE eStatus != 'Deleted' order by tRegistrationDate desc";
$data_drv = $obj->MySQLSelect($sql);
?>
<div class="card">
    <div class="card-body">
        <div class="col-lg-12">
            <h2 class="text-right">شرکت ها</h2>
            <a href="<?php echo adminUrl('companies', ['op' => 'form ']); ?>" class="btn btn-primary">افزودن شرکت</a>
        </div>
        <hr/>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                <thead>
                <tr>
                    <th scope="col">نام شرکت</th>
                    <th scope="col">راننده</th>
                    <th scope="col">محدوده</th>
                    <th scope="col">موبایل</th>
                    <th scope="col">تاریخ ثبت نام</th>
                    <th scope="col">وضعیت</th>
                    <th scope="col">ویرایش اسناد</th>
                    <th scope="col">عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($data_drv); $i++) {
                    $sql = "SELECT count(iDriverId) as count from register_driver where iCompanyId = '" . $data_drv[$i]['iCompanyId'] . "'";
                    $db_cnt = $obj->MySQLSELECT($sql);
                    $data_drv[$i]['count'] = $db_cnt[0]['count'];
                    ?>
                    <tr>
                        <td><?php echo $data_drv[$i]['vCompany']; ?></td>
                        <td><a href="<?php echo adminUrl('drivers', ['iCompanyid' => $data_drv[$i]['iCompanyId']]); ?>"
                               target="_blank"><?php echo $data_drv[$i]['count']; ?></a></td>
                        <!--                        <td class="center">-->
                        <?php //echo $vehicles[$i]['vServiceLoc']; ?><!--</td>-->
                        <?php
                        $sql = "SELECT * from savar_area where aId = '" . $data_drv[$i]['iAreaId'] . "'";
                        $db_cnt23 = $obj->MySQLSELECT($sql);
                        ?>
                        <td><?php echo $db_cnt23[0]['sAreaNamePersian']; ?></td>
                        <td><?php echo $generalobjAdmin->clearPhone($data_drv[$i]['vPhone']); ?></td>
                        <td><?php //echo jdate('d-F-Y', strtotime($vehicles[$i]['tRegistrationDate'])); ?>
                            <!-- --->
                            <?php if ($data_drv[$i]['iCompanyId'] == 1) { ?>
                        <td>-----</td>

                    <?php } else { ?>
                        <td>
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
                            }
                            ?>
                        </td>
                    <?php } ?>
                        <td>
                            <?php if ($data_drv[$i]['iCompanyId'] == 1) { ?>
                            <b>-----</b>
                        </td>
                    <?php } else { ?>
                        <a href="<?php echo adminUrl('companies', ['op' => 'documents', 'id' => $data_drv[$i]['iCompanyId'], 'action' => 'edit']); ?>">
                            <i class="fa fa-file"></i>
                        </a>
                    <?php } ?>
                        </td>
                        <td class="center" width="12%" align="center" style="text-align:center;">
                            <a href="<?php echo adminUrl('companies', ['op' => 'form', 'id' => $data_drv[$i]['iCompanyId']]); ?>"
                               data-toggle="tooltip" title="Edit">
                                <i class="fa fa-pencil text-info"></i>
                            </a>
                            <a href="<?php echo adminUrl('companies', ['iCompanyId' => $data_drv[$i]['iCompanyId'], 'status' => 'Active']); ?>"
                               data-toggle="tooltip" title="فعال سازی">
                                <?php if ($data_drv[$i]['iCompanyId'] != 1) { ?>
                                    <i class="fa fa-check text-primary"></i>
                                <?php } ?>
                            </a>
                            <a href="<?php echo adminUrl('companies', ['iCompanyId' => $data_drv[$i]['iCompanyId'], 'status' => 'Inactive']); ?>"
                               data-toggle="tooltip" title="غیرفعال سازی">
                                <?php if ($data_drv[$i]['iCompanyId'] != 1) { ?>
                                    <i class="fa fa-ban text-secondary"></i>
                                <?php } ?>
                            </a>

                            <a href="#" onclick="$('#delete_form_<?php echo $data_drv[$i]['iCompanyId'] ?>').submit()"
                               data-toggle="tooltip" title="حذف">
                                <i class="fa fa-trash text-danger"></i>
                            </a>
                            <form name="delete_form" id="delete_form_<?php echo $data_drv[$i]['iCompanyId'] ?>"
                                  method="post" action=""
                                  onsubmit="return confirm_delete()" class="margin0">
                                <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                       value="<?php echo $data_drv[$i]['iCompanyId']; ?>">
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
<script>
    function confirm_delete() {
        return confirm("آیا از حذف این شرکت مطمين هستید؟");
    }
</script>
