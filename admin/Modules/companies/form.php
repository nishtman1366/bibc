<?php
$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'company';
$script = "Company";

$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);
$iParentId = isset($_POST['iParentId']) ? $_POST['iParentId'] : '';
$iAreaId = isset($_POST['iAreaId']) ? $_POST['iAreaId'] : '';
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$iCompanyCode = isset($_POST['iCompanyCode']) ? $_POST['iCompanyCode'] : '0';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vManagerPassword = isset($_POST['vManagerPassword']) ? $_POST['vManagerPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCaddress = isset($_POST['vCaddress']) ? $_POST['vCaddress'] : '';
$vCadress2 = isset($_POST['vCadress2']) ? $_POST['vCadress2'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
$vPass = $generalobj->encrypt($vPassword);
$vManagerPassword = $generalobj->encrypt($vManagerPassword);

$vVatNum = isset($_POST['vVatNum']) ? $_POST['vVatNum'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$iPercentageShare = isset($_POST['iPercentageShare']) ? $_POST['iPercentageShare'] : '';

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo' and $action == 'Edit') {
        redirect()
            ->adminUrl('companies');
    }
    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iCompanyId` = '" . $id . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET
	`vName` = '" . $vName . "',
	`iCompanyCode` = '" . $iCompanyCode . "',
	`iParentId` = '" . $iParentId . "',
	`iAreaId` = '" . $iAreaId . "',
	`vLastName` = '" . $vLastName . "',
	`vEmail` = '" . $vEmail . "',
	`vCaddress` = '" . $vCaddress . "',
	`vCadress2` = '" . $vCadress2 . "',
	`vPassword` = '" . $vPass . "',
	`vManagerPassword` = '" . $vManagerPassword . "',
	`vPhone` = '" . $vPhone . "',
	`vCity` = '" . $vCity . "',
	`vCompany` = '" . $vCompany . "',
	`iPercentageShare` = '" . $iPercentageShare . "',
	`vInviteCode` = '" . $vInviteCode . "',
	`vVat` = '" . $vVatNum . "',
	`vCountry` = '" . $vCountry . "'"
        . $where;
    //echo"<pre>";print_r($query);exit;
//TODO email for company abount status change
    //echo $query;
//    $obj->sql_query($query);
//    $id = ($id != '') ? $id : mysqli_insert_id();
//    //echo"<pre>";print_r($action);exit;
//    if ($action == 'Add') {
//        $maildata['EMAIL'] = $vEmail;
//        $maildata['NAME'] = $vName;
//        $maildata['PASSWORD'] = $vPassword;
//        // $generalobj->send_email_user("MEMBER_REGISTRATION_USER",$maildata);
//        $generalobj->send_email_user("COMPANY_REGISTRATION_USER", $maildata);
//        //header("Location:company_action.php?id=".$id.'&success=1');
//    }
    redirect()
        ->adminUrl('companies', ['op' => 'form', 'id' => $id])
        ->setMessage('ثبت اطلاعات شرکت با موفقیت انجام شد.');
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCompanyId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_R($db_data);echo "</pre>";
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iParentId = $value['iParentId'];
            $iAreaId = $value['iAreaId'];
            $vName = $value['vName'];
            $iCompanyCode = $value['iCompanyCode'];
            $vLastName = $value['vLastName'];
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $vCompany = $value['vCompany'];
            $vCaddress = $value['vCaddress'];
            $vCadress2 = $value['vCadress2'];
            $vPassword = $value['vPassword'];
            $vPhone = $value['vPhone'];
            $vCity = $value['vCity'];
            $vInviteCode = $value['vInviteCode'];
            $vVatNum = $value['vVat'];
            $vCountry = $value['vCountry'];
            $iPercentageShare = $value['iPercentageShare'];
            $vManagerPassword = $value['vManagerPassword'];

            $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
            $vManagerPassword = $generalobj->decrypt($db_data[0]['vManagerPassword']);
        }
    }
}
?>
<!-- On OFF switch -->
<!--<link href="../assets/css/jquery-ui.css" rel="stylesheet"/>-->
<!--<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>-->
<div class="row">
    <div class="col-lg-12">
        <h2 class="text-right">اضافه کردن شرکت <?php echo $vCompany; ?></h2>
        <a href="<?php echo adminUrl('companies'); ?>" class="btn btn-primary">
            بازگشت به لیست
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="form-group">
            <?php if ($success == 1) { ?>
                <div class="alert alert-success alert-dismissable msgs_hide">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?php
                    //echo $action;exit;
                    if ($action == "Add") {
                        ?>
                        شرکت با موفقیت افزوده شد
                    <?php } else {
                        ?>
                        شرکت با موفقیت ویرایش شد
                    <?php }
                    ?>
                </div><br/>
            <?php } ?>
            <?php if ($success == 2) { ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be
                    enabled on the main script we will provide you.
                </div><br/>
            <?php } ?>
            <form method="post" action="">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <div class="form-group">
                            <label for="vCompany">نام شرکت<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="vCompany" id="vCompany"
                                   value="<?php echo $vCompany; ?>" placeholder="Company Name" required>
                        </div>

                        <div class="form-group">
                            <label for="iCompanyCode">کد شرکت<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="iCompanyCode" id="iCompanyCode"
                                   value="<?php echo $iCompanyCode; ?>" placeholder="Company Code" required>
                        </div>

                        <div class="form-group">
                            <label for="iParentId">والدین <span class="red"> *</span></label>
                            <select class="form-control" name="iParentId" id="iParentId" onChange="" required>
                                <option value="0">--select--</option>
                                <?php
                                foreach (companies() as $company) { ?>
                                    <option value="<?php echo $company['iCompanyId'] ?>"
                                            <?php if ($iParentId == $company['iCompanyId']){ ?>selected<?php } ?>><?php echo $company['vCompany'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="iAreaId">محدوده <span class="red"> *</span></label>
                            <select class="form-control" name="iAreaId" id="iAreaId" onChange="" required>
                                <option value="0">--select--</option>
                                <?php foreach (areas() as $area) { ?>
                                    <option value="<?php echo $area['aId'] ?>"
                                            <?php if ($iAreaId == $area['aId']){ ?>selected<?php } ?>><?php echo $area['sAreaName'] ?>
                                        ( <?php echo $area['sAreaNamePersian'] ?> )
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <!--	<div class="row">
							<div class="col-lg-12">
							<label>First Name<span class="red"> *</span></label>
						</div>
						<div class="col-lg-6">
						<input type="text" class="form-control" name="vName"  id="vName" value="<?php echo $vName; ?>" placeholder="First Name" required>
					</div>
				</div>
				<div class="row">
				<div class="col-lg-12">
				<label>Last Name<span class="red"> *</span></label>
			</div>
			<div class="col-lg-6">
			<input type="text" class="form-control" name="vLastName"  id="vLastName" value="<?php echo $vLastName; ?>" placeholder="Last Name" required>
		</div>
	</div>-->

                        <div class="form-group">
                            <label for="vEmail">ایمیل<span class="red"> *</span></label>
                            <input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" class="form-control"
                                   name="vEmail" id="vEmail" value="<?php echo $vEmail; ?>" placeholder="Email" required
                                   onChange="validate_email(this.value,'<?php echo $id; ?>')"/>
                            <div id="emailCheck"></div>
                        </div>
                        <div class="form-group">
                            <label for="vPassword">رمز عبور<span class="red"> *</span></label>
                            <input type="text" pattern=".{6,}" title="Six or more characters" class="form-control"
                                   name="vPassword" id="vPassword" value="<?php echo $vPass ?>" placeholder="Password"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="vManagerPassword">رمز عبور مدیر<span class="red"> *</span></label>
                            <input type="text" pattern=".{6,}" title="Six or more characters" class="form-control"
                                   name="vManagerPassword" id="vManagerPassword" value="<?php echo $vManagerPassword ?>"
                                   placeholder="Manager Password" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="vPhone">موبایل<span class="red"> *</span></label>
                            <input type="text" pattern="[0-9]{1,}" class="form-control" name="vPhone" id="vPhone"
                                   value="<?php echo $vPhone; ?>" placeholder="Phone"
                                   title="Please enter proper mobile number." required>
                        </div>
                        <div class="form-group">
                            <label for="iPercentageShare">سهم درصد<span class="red"> *</span></label>
                            <input type="text" pattern="[0-9]{1,}" class="form-control" name="iPercentageShare"
                                   id="iPercentageShare"
                                   value="<?php echo $iPercentageShare; ?>" placeholder="Percentage Share"
                                   title="Please enter company Percentage Share." required>
                        </div>
                        <div class="form-group">
                            <label for="vCountry">کشور <span class="red"> *</span></label>
                            <select class="form-control" name='vCountry' id="vCountry"
                                    onChange="changeCode(this.value);"
                                    required>
                                <option value="">--select--</option>
                                <?php
                                foreach (countries() as $country) { ?>
                                    <option value="<?php echo $country['vCountryCode'] ?>"
                                            <?php if ($vCountry == $country['vCountryCode']){ ?>selected<?php } ?>><?php echo $country['vCountry'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vCaddress">آدرس اول<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="vCaddress" id="vCaddress"
                                   value="<?php echo $vCaddress; ?>" placeholder="Address Line 1" required>
                        </div>
                        <div class="form-group">
                            <label for="vCadress2">آدرس دوم</label>
                            <input type="text" class="form-control" name="vCadress2" id="vCadress2"
                                   value="<?php echo $vCadress2; ?>" placeholder="Address Line 2">
                        </div>
                        <div class="form-group">
                            <label for="vCity">شهر<span class="red"> *</span></label>
                            <input type="text" class="form-control" name="vCity" id="vCity"
                                   value="<?php echo $vCity; ?>"
                                   placeholder="City" required>
                        </div>
                        <div class="form-group">
                            <label for="vVatNum">شماره وات</label>
                            <input type="text" class="form-control" name="vVatNum" id="vVatNum"
                                   value="<?php echo $vVatNum; ?>" placeholder="VAT Number">
                        </div>
                        <!-- <div class="row">
	<div class="col-lg-12">
	<label>Invite Code</label>
</div>
<div class="col-lg-6">
<input type="text" class="form-control" name="vInviteCode"  id="vInviteCode" value="<?php echo $vInviteCode; ?>" placeholder="Invite Code">
</div>
</div> -->


                        <!--<div class="row">
<div class="col-lg-12">
<label>Status</label>
</div>
<div class="col-lg-6">
<div class="make-switch" data-on="success" data-off="warning">
<input type="checkbox" name="eStatus" <?php echo ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
</div>
</div>
</div>-->
                    </div>
                </div>
                <input type="submit" class="btn btn-info col-12" name="submit" id="submit"
                       value="ذخیره اطلاعات">
            </form>
        </div>
    </div>
</div>
<div style="clear:both;"></div>

<!--<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>-->
<!--<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>-->
<script>
    var successMSG1 = '<?php echo $success;?>';
    if (successMSG1 != '') {
