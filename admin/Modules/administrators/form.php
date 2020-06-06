<?php
include_once('../common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'administrators';
$script = 'Admin';

$sql1 = "SELECT * FROM admin_groups WHERE 1";
$db_group = $obj->MySQLSelect($sql1);

$vFirstName = isset($_POST['vFirstName']) ? $_POST['vFirstName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vContactNo = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$iGroupId = isset($_POST['iGroupId']) ? $_POST['iGroupId'] : '';
$area = isset($_POST['area']) ? $_POST['area'] : '';
$vPass = $generalobj->encrypt($vPassword);
$vAccessOptions = implode(",", $_POST['access']);

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        redirect()
            ->adminUrl('administrators')
            ->setMessage('ثبت اطلاعات مدیر با موفقیت انجام شد.');
    }
    if ($id != "") {
        $msg = $generalobj->checkDuplicateAdmin('iAdminId', 'administrators', array('vEmail'), $tconfig["tsite_url"] . "/admin/admin_action.php?success=3&var_msg=Email already Exists", "Email already Exists", $id, "");
    } else {
        $msg = $generalobj->checkDuplicateAdmin('vEmail', 'administrators', array('vEmail'), $tconfig["tsite_url"] . "/admin/admin_action.php?success=3&var_msg=Email already Exists", "Email already Exists", "", "");
    }
    if ($msg == 1) {
        if ($id == "") {
            redirect()
                ->adminUrl('administrators', ['op' => 'form'])
                ->setMessage('آدرس ایمیل وارد شده تکراری است', 'danger');
        } else {
            redirect()
                ->adminUrl('administrators', ['op' => 'form', 'id' => $id])
                ->setMessage('آدرس ایمیل وارد شده تکراری است', 'danger');
        }
    }

    $q = "INSERT INTO ";
    $where = '';
    if ($action == 'Edit') {
        $str = ", eStatus = 'Inactive' ";
    } else {
        $str = '';
    }
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iAdminId` = '" . $id . "'";
    }


    $query = $q . " `" . $tbl_name . "` SET
  `vFirstName` = '" . $vFirstName . "',
  `vLastName` = '" . $vLastName . "',
  `vEmail` = '" . $vEmail . "',
  `vPassword` = '" . $vPass . "',
  `iGroupId` = '" . $iGroupId . "',
  `vAccessOptions` = '" . $vAccessOptions . "',
  `area` = '" . $area . "',
  `vContactNo` = '" . $vContactNo . "'
  " . $where;
    $obj->sql_query($query);

    $id = ($id != '') ? $id : mysqli_insert_id($condbc);
    redirect()
        ->adminUrl('administrators')
        ->setMessage('ثبت اطلاعات مدیر با موفقیت انجام شد.');
}

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iAdminId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_R($db_data);echo "</pre>";
    $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vFirstName = $value['vFirstName'];
            $vAccessOptions = $value['vAccessOptions'];
            $vLastName = $value['vLastName'];
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $adminArea = $value['area'];
            $vPassword = $value['vPassword'];
            $vContactNo = $value['vContactNo'];
            $iGroupId = $value['iGroupId'];
        }
    }
}
?>
<div class="row">
    <div class="col-12">
        <h2 class="text-right"><?php echo $action; ?> Admin <?php echo $vFirstName; ?></h2>
        <a href="<?php echo adminUrl('administrators'); ?>" class="btn btn-primary">بازگشت به لیست</a>
    </div>
</div>
<hr/>
<div class="card">
    <div class="card-body">
        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
            <?php if ($id) { ?>

            <?php } ?>
            <div class="form-group">
                <label for="vName">نام<span class="red"> *</span></label>
                <input type="text" pattern="[\D]+" class="form-control" name="vFirstName" id="vName"
                       value="<?php echo $vFirstName; ?>" placeholder="First Name" required>
            </div>
            <div class="form-group">
                <label for="vLastName">نام خانوادگی<span class="red"> *</span></label>
                <input type="text" pattern="[\D]+" class="form-control" name="vLastName" id="vLastName"
                       value="<?php echo $vLastName; ?>" placeholder="Last Name" required>
            </div>

            <div class="form-group">
                <label for="vEmail">آدرس ایمیل<span class="red"> *</span></label>
                <!-- <input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" class="form-control" name="vEmail" id="vEmail" value="<?php echo $vEmail; ?>" placeholder="Email" required <?php if (!empty($_REQUEST['id'])) { ?> readonly="readonly" <?php } ?>> -->
                <input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                       class="form-control" name="vEmail" id="vEmail" value="<?php echo $vEmail; ?>"
                       placeholder="Email" required>
                <div id="emailCheck"></div>
            </div>

            <div class="form-group">
                <label for="vPassword">کلمه عبور<span class="red"> *</span></label>
                <input type="password" pattern=".{6,}" class="form-control" name="vPassword"
                       id="vPassword" value="<?php echo $vPass ?>" placeholder="Password Label"
                       title="Six or more characters" required>
            </div>
            <div class="form-group">
                <label for="vContactNo">تلفن تماس<span class="red"> *</span></label>
                <input type="text" pattern="[0-9]{1,}" class="form-control" name="vPhone"
                       id="vContactNo" value="<?php echo $vContactNo; ?>" placeholder="Phone" required>
            </div>
            <?php if ($_SESSION['sess_iGroupId'] == 1) { ?>
                <div class="form-group">
                    <label for="iGroupId">گروه کاربری<span class="red"> *</span><i class="icon-question-sign"
                                                                                   data-placement="top"
                                                                                   data-toggle="tooltip"
                                                                                   data-original-title='Admin Group has 3 types. 1) Super Administrator - He can manage whole admin panel. 2) Dispatcher Administrator - He can manage Manual Taxi Dispatch. 3) Billing Administrator - He can see rides and details of each ride.'></i></label>
                    <select class="form-control" name='iGroupId' id="iGroupId" required>
                        <option value="">انتخاب کنید:</option>
                        <?php for ($i = 0; $i < count($db_group); $i++) { ?>
                            <option value="<?php echo $db_group[$i]['iGroupId'] ?>" <?php echo ($db_group[$i]['iGroupId'] == $iGroupId) ? 'selected' : ''; ?> > <?php echo $db_group[$i]['vGroup'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="form-group">
                <label for="area">Area<span class="red"> *</span></label>
                <select class="form-control" name="area" id="area" required>
                    <option value="-1">All</option>
                    <?php
                    $sql = "select * from savar_area WHERE sActive = 'Yes'";
                    $db_area = $obj->MySQLSelect($sql);
                    for ($i = 0; $i < count($db_area); $i++) {
                        echo '<option ' . (($db_area[$i]['aId'] == $adminArea) ? 'selected ' : '') . 'value ="' . $db_area[$i]['aId'] . '">' . $db_area[$i]['sAreaNamePersian'] . " - " . $db_area[$i]['sAreaName'] . " </option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Accesses <?php echo $vAccessOptions; ?> <span class="red"> </span></label>
                <!--                    <div class="btn-group btn-group-toggle" data-toggle="buttons">-->
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="a0" <?php echo((strpos($vAccessOptions, 'a0') !== false) ? 'checked' : ''); ?>>داشبورد
                </label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="a1" <?php echo((strpos($vAccessOptions, 'a1') !== false) ? 'checked' : ''); ?>>ادمین
                    ها</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="2a" <?php echo (strpos($vAccessOptions, '2a') !== false && $vAccessOptions[strpos($vAccessOptions, '2a') - 1] != '1') ? 'checked' : ''; ?>>شرکت
                    ها</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="3a" <?php echo (strpos($vAccessOptions, '3a') !== false && $vAccessOptions[strpos($vAccessOptions, '3a') - 1] != '1') ? 'checked' : ''; ?>>ناحیه</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="4a" <?php echo (strpos($vAccessOptions, '4a') !== false && $vAccessOptions[strpos($vAccessOptions, '4a') - 1] != '1') ? 'checked' : ''; ?>>رانندگان</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="5a" <?php echo (strpos($vAccessOptions, '5a') !== false && $vAccessOptions[strpos($vAccessOptions, '5a') - 1] != '1') ? 'checked' : ''; ?>>حداکثر
                    بدهی</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="6a" <?php echo (strpos($vAccessOptions, '6a') !== false && $vAccessOptions[strpos($vAccessOptions, '6a') - 1] != '1') ? 'checked' : ''; ?>>ماشین</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="7a" <?php echo (strpos($vAccessOptions, '7a') !== false && $vAccessOptions[strpos($vAccessOptions, '7a') - 1] != '1') ? 'checked' : ''; ?>>نوع
                    ماشین</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="a2" <?php echo (strpos($vAccessOptions, 'a2') !== false && $vAccessOptions[strpos($vAccessOptions, 'a2') - 1] != '1') ? 'checked' : ''; ?>>تنظیمات
                    نرخ کلی</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="8a" <?php echo (strpos($vAccessOptions, '8a') !== false && $vAccessOptions[strpos($vAccessOptions, '8a') - 1] != '1') ? 'checked' : ''; ?>>نوع
                    بسته</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="9a" <?php echo (strpos($vAccessOptions, '9a') !== false && $vAccessOptions[strpos($vAccessOptions, '9a') - 1] != '1') ? 'checked' : ''; ?>>مسافران</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="10a" <?php echo (strpos($vAccessOptions, '10a') !== false) ? 'checked' : ''; ?>>رزرو</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="11a" <?php echo (strpos($vAccessOptions, '11a') !== false) ? 'checked' : ''; ?>>سفرها</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="12a" <?php echo (strpos($vAccessOptions, '12a') !== false) ? 'checked' : ''; ?>>رزروها</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="13a" <?php echo (strpos($vAccessOptions, '13a') !== false) ? 'checked' : ''; ?>>کد
                    تخفیف</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="14a" <?php echo (strpos($vAccessOptions, '14a') !== false) ? 'checked' : ''; ?>>معرفی
                    دوستان</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="15a" <?php echo (strpos($vAccessOptions, '15a') !== false) ? 'checked' : ''; ?>>گادز
                    ویو</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="16a" <?php echo (strpos($vAccessOptions, '16a') !== false) ? 'checked' : ''; ?>>نقشه
                    پراکندگی</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="17a" <?php echo (strpos($vAccessOptions, '17a') !== false) ? 'checked' : ''; ?>>نظرات</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="18a" <?php echo (strpos($vAccessOptions, '18a') !== false) ? 'checked' : ''; ?>>ناتیفیکیشن</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="19a" <?php echo (strpos($vAccessOptions, '19a') !== false) ? 'checked' : ''; ?>>گزارشات</label>
                <label class="btn btn-outline-warning">
                    <input type="checkbox" name="access[]"
                           value="20a" <?php echo (strpos($vAccessOptions, '20a') !== false) ? 'checked' : ''; ?>>تنظیمات</label>
                <!--                    </div>-->
            </div>
            <input type="submit" class="btn btn-info" name="submit" id="submit"
                   value="ذخیره اطلاعات">
        </form>
    </div>
</div>

<script>
    $('[data-toggle="tooltip"]').tooltip();

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
                    let eml = /^[-.0-9a-zA-Z]+@[a-zA-z]+\.[a-zA-z]{2,3}$/;
                    let result = eml.test(id);
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
</script>
