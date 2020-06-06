<?php
$script = "MaxOwe";

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$max_owe = $generalobj->getConfigurations('configurations', 'driver_max_owe');
$new_max_owe = isset($_REQUEST['max_owe']) ? $_REQUEST['max_owe'] : '10000';
?>
<div class="row">
    <div class="col-lg-12">
        <h2 class="text-right">
            حداکثر بدهی راننده
        </h2>
    </div>
</div>
<hr/>
<div class="card">
    <div class="card-body">
        <p class="alert alert-danger text-right">
            <?php
            //TODO change the url for settings page
            ?>
            حداکثر بدهی راننده از این صفحه به بخش
            <a href="<?php echo adminUrl('settings', ['category' => 'general']); ?>">
                تنظیمات عمومی</a> منتقل شده است.
        </p>
        <div class="form-group"><span style="color: red;font-size: small;" id="coupon_status"></span>
            <form method="post" action="<?php echo adminUrl('driver_max_owe'); ?>">
                <div class="form-group">
                    <label for="max_owe">مبلغ: (<?php echo $langage_lbl_admin['LBL_MAX_OWE_CURR']; ?>)<span
                                class="red"> *</span></label>
                    <input type="text" class="form-control" name="max_owe" id="max_owe"
                           value="<?php echo $max_owe; ?>"
                           placeholder="value" required disabled>
                </div>
            </form>
        </div>
        <div class="clear"></div>
    </div>
</div>