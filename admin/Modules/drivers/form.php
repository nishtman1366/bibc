<?php
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id = $id;
$action = ($id != '') ? 'Edit' : 'Add';

$script = 'Driver';

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vUserName = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vImage = isset($_POST['vImage']) ? $_POST['vImage'] : '';
$vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
$vBankAccountHolderName = isset($_POST['vBankAccountHolderName']) ? $_POST['vBankAccountHolderName'] : '';
$vAccountNumber = isset($_POST['vAccountNumber']) ? $_POST['vAccountNumber'] : '';
$vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
$vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
$vBIC_SWIFT_Code = isset($_POST['vBIC_SWIFT_Code']) ? $_POST['vBIC_SWIFT_Code'] : '';
$tProfileDescription = isset($_POST['tProfileDescription']) ? $_POST['tProfileDescription'] : '';
$vCurrencyDriver = isset($_POST['vCurrencyDriver']) ? $_POST['vCurrencyDriver'] : '';
$vPass = $generalobj->encrypt($vPassword);

if (isset($_POST['submitbtn'])) {
    //Start :: Upload Image Script
    if (!empty($id)) {
        if (SITE_TYPE == 'Demo') {
            redirect()
                ->adminUrl('drivers')
                ->setMessage('ثبت اطلاعات راننده با موفقیت انجام شد.');
        }
        if (isset($_FILES['vImage'])) {
            $id = $_GET['id'];
            $img_path = $tconfig["tsite_upload_images_driver_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            $check_file_query = "select iDriverId,vImage from register_driver where iDriverId=" . $id;
            $check_file = $obj->sql_query($check_file_query);
            if ($image_name != "") {
                /*
                 * بررسی خصوصیات فایل ارسال شده
                 */
                $check_file['vImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vImage'];
                /*
                 * ۱. بررسی فرمت فایل ارسال شده
                 */
                $filecheck = basename($_FILES['vImage']['name']);
                $fileextarr = explode(".", $filecheck);
                $ext = strtolower($fileextarr[count($fileextarr) - 1]);
                $uploadedImageMessage = null;
                if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                    $uploadedImageMessage = "فرمت فایل ارسالی باید .jpg, .jpeg, .gif, .png باشد.";
                }
                /*
                 * بررسی اندازه فایل ارسال شده
                 */
                if ($_FILES['vImage']['size'] > 1048576) {
                    $uploadedImageMessage = "حجم فایل ارسالی نباید بیشتر از ۱ مگابایت باشد.";
                }
                if (!is_null($uploadedImageMessage)) {
                    /*
                     * در صورت وجود اشکال در فایل ارسالی
                     * کاربر به فرم ثبت راننده منتقل می شود.
                     */
                    redirect()
                        ->adminUrl('drivers', ['op' => 'form', 'id', $_REQUEST['id']])
                        ->setMessage($uploadedImageMessage, 'danger');
                } else {
                    if ($check_file['vImage'] != '' && file_exists($check_file['vImage'])) {
                        unlink($img_path . '/' . $id . '/' . $check_file[0]['vImage']);
                        unlink($img_path . '/' . $id . '/1_' . $check_file[0]['vImage']);
                        unlink($img_path . '/' . $id . '/2_' . $check_file[0]['vImage']);
                        unlink($img_path . '/' . $id . '/3_' . $check_file[0]['vImage']);
                    }

                    $Photo_Gallery_folder = $img_path . '/' . $id . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);

                    if ($img1 != '') {
                        if (is_file($Photo_Gallery_folder . $img1)) {
                            include_once(TPATH_CLASS . "/SimpleImage.class.php");
                            $img = new SimpleImage();
                            list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $img1);
                            if ($width < $height) {
                                $final_width = $width;
                            } else {
                                $final_width = $height;
                            }
                            $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);
                            $img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                        }
                    }
                    $vImage = $img1;
                }
            } else {
                $vImage = $check_file[0]['vImage'];
            }
        }
    }
    /*
     * بررسی تکراری نبودن آدرس ایمیل
     */
    $SQL1 = "SELECT 'vName' FROM `register_driver` WHERE vEmail = '$vEmail' AND iDriverId != '$id'";
    $email_exist = $obj->MySQLSelect($SQL1);
    $array = [];
    if (!empty($id)) {
        $array = ['id' => $_POST['id']];
    }
    if (count($email_exist) > 0) {
        redirect()->adminUrl('drivers', $array)->setMessage('آدرس ایمیل وارد شده تکراری است', 'warning');
    }

    $q = "INSERT INTO ";
    $where = '';
    if ($action == 'Edit') {
        $str = " ";
    } else {
        $str = " , eStatus = 'active' ";
    }

    if (SITE_TYPE == 'Demo') {
        $str = " , eStatus = 'active' ";
    }

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iDriverId` = '" . $id . "'";
        $obj->sql_query("UPDATE `driver_vehicle` SET `iCompanyId`=$iCompanyId WHERE `iDriverId` = $id");
    }


    $query = $q . " `register_driver` SET
		`vName` = '" . $vName . "',
		`vLastName` = '" . $vLastName . "',
		`vCountry` = '" . $vCountry . "',
		`vCode` = '" . $vCode . "',
		`vEmail` = '" . $vEmail . "',
		`vLoginId` = '" . $vEmail . "',
		`vPassword` = '" . $vPass . "',
		`iCompanyId` = '" . $iCompanyId . "',
		`vPhone` = '" . $vPhone . "',
		`vCity` = '" . $vCity . "',
    `vImage` = '" . $vImage . "',
    `vPaymentEmail` = '" . $vPaymentEmail . "',
    `vBankAccountHolderName` = '" . $vBankAccountHolderName . "',
    `vBankLocation` = '" . $vBankLocation . "',
    `vBankName` = '" . $vBankName . "',
    `vAccountNumber` = '" . $vAccountNumber . "',
    `vBIC_SWIFT_Code` = '" . $vBIC_SWIFT_Code . "',
		`tProfileDescription` = '" . $tProfileDescription . "',
    `vCurrencyDriver`='" . $vCurrencyDriver . "',
		`vLang` = '" . $vLang . "' $str" . $where;
    //echo '<pre>'; print_r($query); exit;
    $obj->sql_query($query);

    $id = mysqli_insert_id($condbc);
    if ($id != '') {
        if (isset($_FILES['vImage'])) {
            $img_path = $tconfig["tsite_upload_images_driver_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            $check_file_query = "select iDriverId,vImage from register_driver where iDriverId=" . $id;
            $check_file = $obj->sql_query($check_file_query);
            if ($image_name != "") {
                /*
                * بررسی خصوصیات فایل ارسال شده
                */
                $check_file['vImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vImage'];
                /*
                 * ۱. بررسی فرمت فایل ارسال شده
                 */
                $filecheck = basename($_FILES['vImage']['name']);
                $fileextarr = explode(".", $filecheck);
                $ext = strtolower($fileextarr[count($fileextarr) - 1]);
                $uploadedImageMessage = null;
                if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                    $uploadedImageMessage = "فرمت فایل ارسالی باید .jpg, .jpeg, .gif, .png باشد.";
                }
                /*
                 * بررسی اندازه فایل ارسال شده
                 */
                if ($_FILES['vImage']['size'] > 1048576) {
                    $uploadedImageMessage = "حجم فایل ارسالی نباید بیشتر از ۱ مگابایت باشد.";
                }
                if (!is_null($uploadedImageMessage)) {
                    /*
                     * در صورت وجود اشکال در فایل ارسالی
                     * کاربر به فرم ثبت راننده منتقل می شود.
                     */
                    redirect()
                        ->adminUrl('drivers', ['op' => 'form', 'id', $_REQUEST['id']])
                        ->setMessage($uploadedImageMessage, 'danger');
                } else {
                    if ($check_file['vImage'] != '' && file_exists($check_file['vImage'])) {
                        unlink($img_path . '/' . $id . '/' . $check_file[0]['vImage']);
                        unlink($img_path . '/' . $id . '/1_' . $check_file[0]['vImage']);
                        unlink($img_path . '/' . $id . '/2_' . $check_file[0]['vImage']);
                        unlink($img_path . '/' . $id . '/3_' . $check_file[0]['vImage']);
                    }
                    $Photo_Gallery_folder = $img_path . '/' . $id . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);

                    if ($img1 != '') {
                        if (is_file($Photo_Gallery_folder . $img1)) {
                            include_once(TPATH_CLASS . "/SimpleImage.class.php");
                            $img = new SimpleImage();
                            list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $img1);
                            if ($width < $height) {
                                $final_width = $width;
                            } else {
                                $final_width = $height;
                            }
                            $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);
                            $img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                        }
                    }
                    $vImage = $img1;

                    $sql = "UPDATE `register_driver` SET `vImage` = '" . $vImage . "' WHERE `iDriverId` = '" . $id . "'";
                    $obj->sql_query($sql);
                }
            }
        }
    }

//    if ($action == 'Add') {
//        $maildata['EMAIL'] = $vEmail;
//        $maildata['NAME'] = $vName . ' ' . $vLastName;
//        $maildata['PASSWORD'] = $vPassword;
    //TODO send email to driver
//        $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
//    }
    redirect()
        ->adminUrl('drivers')
        ->setMessage('ثبت اطلاعات راننده با موفقیت انجام شد.');
}

if ($action == 'Edit') {
    $sql = "SELECT * FROM `register_driver` WHERE iDriverId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
    if ($db_data[0]['eStatus'] == "active") {
        $actionType = "approve";
    } else {
        $actionType = "pending";
    }
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vName = $value['vName'];
            $iCompanyId = $value['iCompanyId'];
            $vLastName = $value['vLastName'];
            $vCountry = $value['vCountry'];
            $vCode = $value['vCode'];
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $vUserName = $value['vLoginId'];
            $vPassword = $value['vPassword'];
            $vPhone = $generalobjAdmin->clearPhone($value['vPhone']);
            $vCity = $value['vCity'];
            $vLang = $value['vLang'];
            $vImage = $value['vImage'];
            $vCurrencyDriver = $value['vCurrencyDriver'];
            $vPaymentEmail = $value['vPaymentEmail'];
            $vBankAccountHolderName = $value['vBankAccountHolderName'];
            $vAccountNumber = $value['vAccountNumber'];
            $vBankLocation = $value['vBankLocation'];
            $vBankName = $value['vBankName'];
            $vBIC_SWIFT_Code = $value['vBIC_SWIFT_Code'];
            $tProfileDescription = $value['tProfileDescription'];
        }
    } else {
        redirect()->adminUrl('drivers')->setMessage('اطلاعات راننده یافت نشده.', 'warning');
    }
}
?>
<div class="row">
    <div class="col-lg-12">
        <h2 class="text-right">ثبت اطلاعات راننده</h2>
        <a href="<?php echo adminUrl('drivers', ['type' => $actionType]); ?>" class="btn btn-primary">بازگشت به لیست</a>
    </div>
</div>
<hr/>
<div class="card">
    <div class="card-body">
        <div class="form-group">
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" id="u_id" name="id" value="<?php echo $id; ?>"/>
                <input type="hidden" id="usertype" name="usertype" value="driver"/>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <?php if ($id) { ?>
                            <?php if ($vImage == 'NONE' || $vImage == '') { ?>
                                <img class="m-auto" src="../assets/img/profile-user-img.png" alt="">
                            <?php } else { ?>
                                <img class="w-100 rounded border border-dark"
                                     src="<?php echo $tconfig["tsite_upload_images_driver"] . '/' . $id . '/3_' . $vImage ?>"/>
                            <?php } ?>
                        <?php } ?>
                        <div class="form-group">
                            <label for="vImage">تصویر پروفایل</label>
                            <input type="file" class="form-control" name="vImage" id="vImage"
                                   placeholder="Name Label" style="padding-bottom: 39px;">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="vName">نام<span class="red"> *</span></label>
                            <input type="text" pattern="[\D]+" title="Only Alpha characters allowed in name."
                                   class="form-control" name="vName" id="vName" value="<?php echo $vName; ?>"
                                   placeholder="First Name" required oninvalid="window.scrollTo(0,0);">
                        </div>
                        <div class="form-group">
                            <label for="vEmail">ایمیل<span class="red"> *</span></label>
                            <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$"
                                   class="form-control" name="vEmail" onchange="validate_email(this.value)"
                                   id="vEmail" value="<?php echo $vEmail; ?>" placeholder="Email" required>
                            <div id="emailCheck"></div>
                        </div>
                        <div class="form-group">
                            <label for="vCountry">کشور<span class="red"> *</span></label>
                            <select class="form-control" name='vCountry' id='vCountry'
                                    onChange="changeCode(this.value);"
                                    required>
                                <option value="">انتخاب کنید:</option>
                                <?php foreach (countries() as $country) { ?>
                                    <option value="<?php echo $country['vCountryCode'] ?>"
                                            <?php if ($vCountry == $country['vCountryCode']){ ?>selected<?php } ?>><?php echo $country['vCountry'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="iCompanyId">شرکت<span class="red"> *</span></label>
                            <select class="form-control" name='iCompanyId' id='iCompanyId' required>
                                <option value="">انتخاب کنید:</option>
                                <?php foreach (companies() as $company) { ?>
                                    <option value="<?php echo $company['iCompanyId'] ?>" <?php echo ($company['iCompanyId'] == $iCompanyId) ? 'selected' : ''; ?>>
                                        <?php echo $company['vName'] . " " . $company['vLastName'] . " (" . $company['vCompany'] . ")"; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vLang">زبان<span class="red"> *</span></label>
                            <select class="form-control" name='vLang' id="vLang" required>
                                <option value="">انتخاب کنید:</option>
                                <?php foreach (languages() as $language) { ?>
                                    <option value="<?php echo $language['vCode'] ?>" <?php echo ($language['vCode'] == $vLang) ? 'selected' : ''; ?>>
                                        <?php echo $language['vTitle'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="vLastName">نام خانوادگی<span class="red"> *</span></label>
                            <input type="text" pattern="[\D]+" title="Only Alpha characters allowed in name."
                                   class="form-control" name="vLastName" id="vLastName"
                                   value="<?php echo $vLastName; ?>" placeholder="Last Name" required
                                   oninvalid="window.scrollTo(0,0);">
                        </div>
                        <div class="form-group">
                            <label for="vPassword">رمز عبور <span class="red"> *</span></label>
                            <input type="text" pattern=".{6,}" title="Six or more characters" class="form-control"
                                   name="vPassword" id="vPassword" value="<?php echo $vPass ?>"
                                   placeholder="Password Label" required>
                        </div>
                        <div class="form-group">
                            <label for="vCity">شهر<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="vCity" id="vCity"
                                   value="<?php echo $vCity; ?>" placeholder="City" required
                                   oninvalid="window.scrollTo(0,0);">
                        </div>
                        <input type="hidden" class="form-select-2" id="code" name="vCode"
                               value="<?php echo $vCode ?>" required readonly "/>
                        <div class="form-group">
                            <label for="vPhone">شماره موبایل<span class="red"> *</span></label>
                            <div class="input-group">
                                <input type="text" pattern="[0-9]{1,}" title="Please enter proper mobile number."
                                       class="form-control border-left-0" name="vPhone"
                                       id="vPhone" value="<?php echo $vPhone; ?>" placeholder="Phone" required
                                       style="direction: ltr">
                                <div class="input-group-addon border border-right-0 bg-secondary"
                                     style="font-size: 11px;direction: ltr">
                                    <?php echo $vCode ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="vCurrencyDriver">واحد پول <span class="red"> *</span></label>
                            <select class="form-control" name='vCurrencyDriver' id="vCurrencyDriver" required>
                                <option value="">انتخاب کنید:</option>
                                <?php foreach (currencies() as $currency) { ?>
                                    <option value="<?php echo $currency['vName'] ?>"
                                            <?php if ($vCurrencyDriver == $currency['vName']){ ?>selected<? } else if ($currency['eDefault'] == "Yes" && $vCurrencyDriver == ''){ ?>selected<? } ?>><?php echo $currency['vName'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="vPaymentEmail">ایمیل پرداخت</label>
                            <input type="email" class="form-control" name="vPaymentEmail" id="vPaymentEmail"
                                   value="<?php echo $vPaymentEmail ?>" placeholder="Payment Email">
                        </div>
                        <div class="form-group">
                            <label for="vBankAccountHolderName">نام دارنده حساب</label>
                            <input type="text" class="form-control" name="vBankAccountHolderName"
                                   id="vBankAccountHolderName" value="<?php echo $vBankAccountHolderName ?>"
                                   placeholder="Account Holder Name">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="vAccountNumber">شماره حساب</label>
                            <input type="text" class="form-control" name="vAccountNumber" id="vAccountNumber"
                                   value="<?php echo $vAccountNumber ?>" placeholder="Account Number">
                        </div>
                        <div class="form-group">
                            <label for="vBankName">نام بانک</label>
                            <input type="text" class="form-control" name="vBankName" id="vBankName"
                                   value="<?php echo $vBankName ?>" placeholder="Name of Bank">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="vBIC_SWIFT_Code">BIC/SWIFT Code</label>
                            <input type="text" class="form-control" name="vBIC_SWIFT_Code" id="vBIC_SWIFT_Code"
                                   value="<?php echo $vBIC_SWIFT_Code ?>" placeholder="BIC/SWIFT Code">
                        </div>
                        <div class="form-group">
                            <label for="vBankLocation">موقعیت بانک</label>
                            <input type="text" class="form-control" name="vBankLocation" id="vBankLocation"
                                   value="<?php echo $vBankLocation ?>" placeholder="Bank Location">
                        </div>
                        <?php if ($APP_TYPE == 'UberX') { ?>
                            <div style="clear: both;"></div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>توضیحات پروفایل :</label>
                                </div>
                                <div class="col-lg-6">
                                            <textarea name="tProfileDescription" rows="3" cols="40" class="form-control"
                                                      id="tProfileDescription"
                                                      placeholder="Profile Description"><?php echo $tProfileDescription; ?>
                                            </textarea>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <input type="submit" class="btn btn-primary" name="submitbtn" id="submit"
                       value="اضافه کردن راننده" style="float: left">
            </form>
        </div>
    </div>
</div>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    var successMSG1 = '<?php echo $success;?>';

    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }

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
        var uid = $("#u_id").val();
        var usertype = $("#usertype").val();
        var request = $.ajax({
            type: "POST",
            url: 'validate_email_new.php',
            data: 'id=' + id + "&uid=" + uid + "&utype=" + usertype,
            success: function (data) {
                if (data == 0) {
                    $('#emailCheck').html('<i class="icon icon-remove alert-danger alert">Already Exist,Select Another</i>');
                    $('input[type="submit"]').attr('disabled', 'disabled');
                } else if (data == 1) {
                    var eml = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    result = eml.test(id);
                    if (result == true) {
                        $('#emailCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                        $('input[type="submit"]').removeAttr('disabled');
                    } else {
                        $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Email</i>');
                        window.scrollTo(0, 0);
                        $('#vEmail').focus();
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    }
                }
                /*else if(data==2)
                {
                 $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> This Account is deleted</i>');
                 $('input[type="submit"]').attr('disabled','disabled');
                }*/
            }
        });
    }
</script>
