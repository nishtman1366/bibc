<?php
include_once('../common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");

include_once('savar_check_permission.php');
if(checkPermission('REFERRAL') == false)
die('you dont`t have permission...');

$thumb = new thumbnail();
$script = "Referral";
if (!isset($generalobjAdmin)) {
	require_once(TPATH_CLASS . "class.general_admin.php");
	$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$rId = isset($_REQUEST['rId']) ? $_REQUEST['rId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($rId != '') ? 'Edit' : 'Add';

$sql = "SELECT * FROM `savar_area`";
$vSavarAreaArray = $obj->MySQLSelect($sql);

$sql = "SELECT iVehicleTypeId,vVehicleType,vVehicleType_PS FROM `vehicle_type`";
$sVehicleType = $obj->MySQLSelect($sql);



$referral['rId'] = '';
$referral['sRefName'] = '';
$referral['sAreaId'] = '';
$referral['sVehicleTypeId'] = '';
$referral['sForUserType'] = '';
$referral['sFollowingAmount'] = '';
$referral['sFollowerAmount'] = '';
$referral['sTripCount'] = '';
$referral['sLimitDay'] = '';
$referral['sStartDate'] = '';
$referral['sExpireDate'] = '';
$referral['sActive'] = '';
$error = '';

if(count($_POST) > 5)
{
	$referral['rId'] = GetPost('rId');
	$referral['sRefName'] = GetPost('sRefName');
	$referral['sAreaId'] = GetPost('sAreaId');
	$referral['sVehicleTypeId'] = GetPost('sVehicleTypeId');
	$referral['sForUserType'] = GetPost('sForUserType');
	$referral['sFollowingAmount'] = GetPost('sFollowingAmount');
	$referral['sFollowerAmount'] = GetPost('sFollowerAmount');
	$referral['sTripCount'] = GetPost('sTripCount');
	$referral['sLimitDay'] = GetPost('sLimitDay');
	$referral['sStartDate'] = GetPost('sStartDate');
	$referral['sExpireDate'] = GetPost('sExpireDate');
	$referral['sActive'] = GetPost('sActive');

	if($referral['sRefName'] == '')
	$error .= 'Referral Name is invalid.<br>';
	if($referral['sExpireDate'] == '')
	$error .= 'invalid expire date.<br>';
	if($referral['sStartDate'] == '')
	$error .= 'invalid start date.<br>';

	foreach($referral as $key => $val)
	{
		$$key = $val;
	}

	if($error == '')
	{
		if($referral['rId'] != '')
		{
			$sql = "UPDATE `savar_referrals` SET
			`sRefName`='$sRefName',`sAreaId`=$sAreaId, `sVehicleTypeId`=$sVehicleTypeId,`sForUserType`='$sForUserType',`sFollowingAmount`=$sFollowingAmount ,`sFollowerAmount`=$sFollowerAmount ,`sTripCount`=$sTripCount,`sLimitDay`=$sLimitDay,`sStartDate`='$sStartDate',`sExpireDate`='$sExpireDate',`sActive`='$sActive' WHERE rId = $rId";
			$res = $obj->sql_query($sql);

			if($res == false)
			{
				$error .= 'خطایی در ویرایش اطلاعات به وجود آمد<br>';
			}
			else
			{
				header("Location: " . $_SERVER['SCRIPT_NAME'] ."?msg=edit&rId=" . $rId);
			}
		}
		else
		{

			$sql = "INSERT INTO `savar_referrals` (`sRefName`, `sAreaId`, `sVehicleTypeId`, `sForUserType`, `sFollowingAmount`, `sFollowerAmount`, `sTripCount`, `sLimitDay`, `sStartDate`, `sExpireDate`, `sActive`)
			VALUES ('$sRefName', '$sAreaId', '$sVehicleTypeId', '$sForUserType', '$sFollowingAmount', '$sFollowerAmount', '$sTripCount', '$sLimitDay', '$sStartDate', '$sExpireDate', '$sActive')";

			$res = $obj->sql_query($sql);



			if($res == false)
			{
				$error .= 'خطایی در ثبت اطلاعات به وجود آمد<br>';
			}
			else
			{

				$res = $obj->MySQLSelect("SELECT rId FROM `savar_referrals` WHERE  `sRefName` = '$sRefName'");
				if(count($res) > 0 )
				{
					$rId = $res[0]['rId'];
					header("Location: " . $_SERVER['SCRIPT_NAME'] ."?msg=add&rId=" . $rId);
				}
			}
		}
	}
}
else if ($action == 'Edit') {
	$sql = "SELECT * FROM `savar_referrals` WHERE rId = '" . $rId . "'";
	$db_data = $obj->MySQLSelect($sql);
	#echo "<pre>";print_R($db_data);echo "</pre>";

	if (count($db_data) > 0) {
		$referral = $db_data[0];
	}
}

function GetPost($key)
{
	return isset($_POST[$key]) ? $_POST[$key] : '';
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
	<meta charset="UTF-8" />
	<title>Admin | Referrals
		<?php echo  $action; ?>
	</title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
	<?
	include_once('global_files.php');
	?>
	<!-- On OFF switch -->
	<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
	<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
	<!-- MAIN WRAPPER -->
	<div id="wrap">
		<?
		include_once('header.php');
		include_once('left_menu.php');
		?>
		<!--PAGE CONTENT -->
		<div id="content">
			<div class="inner">
				<div class="row">
					<div class="col-lg-12">
						<h2>
							<?php echo  $action; ?>
							ارجاع
							<?php echo  $vName; ?>
						</h2>
						<a href="referral.php">
							<input type="button" value="بازگشت" class="add-btn">
						</a> </div>
					</div>
					<hr />
					<div class="body-div">
						<div class="form-group"> <span style="color: red;font-size: small;" id="coupon_status"></span>
							<?php if (isset($_GET['msg']) && $_GET['msg'] == 'edit') {?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
									ارجاع با موفقیت ویرایش شد </div>
									<br/>
									<?} ?>
									<?php if (isset($_GET['msg']) && $_GET['msg'] == 'add') {?>
										<div class="alert alert-success alert-dismissable">
											<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
											ارجاع با موفقیت افزوده شد </div>
											<br/>
											<?} ?>

											<?php if ($error != '') {?>
												<div class="alert alert-danger alert-dismissable">
													<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
													<?php echo  $error ?> </div>
													<br/>
													<?} ?>
													<form method="post" action="referral_action.php" enctype="multipart/form-data" class="">
														<input type="hidden" name="rId" value="<?php echo  $referral['rId']; ?>">

														<div class="row coupon-action-n1">
															<div class="col-lg-12">
																<label>نام ارجاع :<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="text" class="form-control" name="sRefName" id="sRefName" value="<?php echo  $referral['sRefName']; ?>" placeholder="Referral Name" required>
															</div>

														</div>

														<div class="row">
															<div class="col-lg-12">
																<label>محدوده<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<select  class="form-control" name = 'sAreaId' required>
																	<option value="0" <?php if($referral['sAreaId'] == 0) echo 'selected="selected"'; ?> >انتخاب کنید</option>

																	<?php foreach($vSavarAreaArray as $area) : ?>
																		<option value="<?php echo  $area['aId'] ?>" <?php if($referral['sAreaId'] == $area['aId']) echo 'selected="selected"'; ?> ><?php echo   $area['sAreaName'] .' ( ' .$area['sAreaNamePersian']. ' )'; ?></option>
																	<?php endforeach; ?>
																</select>
															</div>
														</div>

														<div class="row">
															<div class="col-lg-12">
																<label>نوع خودرو<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<select  class="form-control" name = 'sVehicleTypeId' required>
																	<option value="0" <?php if($referral['sVehicleTypeId'] == 0) echo 'selected="selected"'; ?> >انتخاب کنید</option>
																	<?php foreach($sVehicleType as $vehiclet) : ?>
																		<option value="<?php echo  $vehiclet['iVehicleTypeId'] ?>" <?php if($referral['sVehicleTypeId'] == $vehiclet['iVehicleTypeId']) echo 'selected="selected"'; ?> ><?php echo   $vehiclet['vVehicleType'] .' ( ' .$vehiclet['vVehicleType_PS']. ' )'; ?></option>
																	<?php endforeach; ?>
																</select>
															</div>
														</div>

														<div class="row coupon-action-n3">
															<div class="col-lg-12">
																<label>ارجاع برای نوع کاربر<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<select id="sForUserType" name="sForUserType" class="form-control ">
																	<option value="Rider" <?php if($referral['sForUserType'] == "Rider"){ ?>selected <?php } ?> >سوار</option>
																	<option value="Driver" <?php if($referral['sForUserType'] == "Driver"){?>selected <?php } ?> >راننده</option>
																</select>
															</div>
														</div>

														<div class="row coupon-action-n2">
															<div class="col-lg-12">
																<label>مقدار برای دنبال کننده :<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="number" class="form-control" name="sFollowerAmount" min="0"  id="sFollowerAmount" value="<?php echo  $referral['sFollowerAmount']; ?>" placeholder="Amount For Referral" required>

															</div>
														</div>


														<div class="row coupon-action-n2">
															<div class="col-lg-12">
																<label>مقدار برای دنبال کننده :<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="number" class="form-control" name="sFollowingAmount" min="0"  id="sFollowingAmount" value="<?php echo  $referral['sFollowingAmount']; ?>" placeholder="Amount For Referred" required>

															</div>
														</div>

														<div class="row coupon-action-n2">
															<div class="col-lg-12">
																<label>تعداد سفر :<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="number" class="form-control" name="sTripCount" min="0"  id="sTripCount " value="<?php echo  $referral['sTripCount']; ?>" placeholder="Trip Count" required>

															</div>
														</div>

														<div class="row coupon-action-n2">
															<div class="col-lg-12">
																<label>محدود کردن روز ها :<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="number" class="form-control" name="sLimitDay" min="0"  id="sLimitDay " value="<?php echo  $referral['sLimitDay']; ?>" placeholder="Limit Days" required>

															</div>
														</div>




														<div class="row" id="date2" style="">
															<div class="col-lg-12">
																<label>تاریخ شروع:</label>
															</div>
															<div class="col-lg-6">
																<input type="text" style="float: left;margin-right: 10px; width:45% " class="form-control" name="sStartDate" value="<?php echo  $referral['sStartDate']; ?>"  id="sStartDate" placeholder="Start Date">
															</div>
														</div>
														<div class="row" id="date2" style="">
															<div class="col-lg-12">
																<label>تاریخ انقضا:</label>
															</div>
															<div class="col-lg-6">
																<input type="text" style="float: left;margin-right: 10px; width:45% " class="form-control" name="sExpireDate" value="<?php echo  $referral['sExpireDate']; ?>"  id="sExpireDate" placeholder="Expiry Date">
															</div>
														</div>

														<div class="row coupon-action-n3">
															<div class="col-lg-12">
																<label>وضعیت<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<select id="sActive" name="sActive" class="form-control ">
																	<option value="Yes" <?php if($referral['sActive'] == "Yes"){ ?>selected <?php } ?> >فعال</option>
																	<option value="No" <?php if($referral['sActive'] == "No"){?>selected <?php } ?> >غیر فعال</option>
																</select>
															</div>
														</div>
														<div class="row coupon-action-n4">
															<div class="col-lg-12">
																<input type="submit" class="save btn-info" name="submit" id="submit" value="<?php echo  $action; ?> Referral"  >
															</div>
														</div>
													</form>
												</div>
												<div class="clear"></div>
											</div>
										</div>
									</div>
									<!--END PAGE CONTENT -->
								</div>
								<!--END MAIN WRAPPER -->
								<?
								include_once('footer.php');
								?>
								<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
								<script>
								function coupon(dis){
									var bla = $('#fDiscount').val();
									if(dis == 'percentage')
									{
										if(bla > 100)
										{
											alert("Please Enter 1 to 100 Discount");
										}
									}
								}
								</script>
								<!--link rel="stylesheet" media="all" type="text/css" href="../assets/js/dtp/jquery-ui.css" />
								<link rel="stylesheet" media="all" type="text/css" href="../assets/js/dtp/jquery-ui-timepicker-addon.css" />

								<script type="text/javascript" src="../assets/js/dtp/jquery-ui.min.js"></script>
								<script type="text/javascript" src="../assets/js/dtp/jquery-ui-timepicker-addon.js"></script-->
								<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js" type="text/javascript"></script>
								<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"
								type="text/javascript"></script>
								<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css"
								rel="Stylesheet"type="text/css"/>
								<?php if ($action == 'Edit') { ?>
									<script>
									window.onload = function () {
										showhidedate('<?php echo $eValidityType; ?>');
									};
									</script>
									<?}else{
										?>
										<script>

										window.onload = function () {

											$('input:radio[name=eValidityType][value=Permanent]').attr('checked', true);
										};

									</script>
								<?php } ?>
								<script type="text/javascript">

								/*$(function() {
								$("#dActiveDate").datepicker({
								minDate: 0,
								dateFormat: "yy-mm-dd",
								showOn: "button",
								buttonImage: "http://192.168.1.131/uber-app/web-new/assets/img/cal-icon.gif",
								buttonImageOnly: true
							});
							$("#dExpiryDate").datepicker({
							minDate: 0,
							dateFormat: "yy-mm-dd",
							showOn: "button",
							buttonImage: "http://192.168.1.131/uber-app/web-new/assets/img/cal-icon.gif",
							buttonImageOnly: true
						});

						$("#dActiveDate").on("dp.change", function (e) {
						$('#dExpiryDate').data("DateTimePicker").minDate(e.date);
					});
					$("#dActiveDate").on("dp.change", function (e) {
					$('#dExpiryDate').data("DateTimePicker").maxDate(e.date);
				});
			});*/

			$(function () {

				$("#sExpireDate").datepicker({
					numberOfMonths: 2,
					dateFormat: "yy-mm-dd",
					onSelect: function (selected) {
						var dt = new Date(selected);
						dt.setDate(dt.getDate());
					}
				});

				$("#sStartDate").datepicker({
					numberOfMonths: 2,
					dateFormat: "yy-mm-dd",
					onSelect: function (selected) {
						var dt = new Date(selected);
						dt.setDate(dt.getDate());
					}
				});


			});


			function showhidedate(val){
				if(val == "Defined"){
					document.getElementById("date1").style.display='';
					document.getElementById("date2").style.display='';
					document.getElementById("dActiveDate").lang='*';
					document.getElementById("dExpiryDate").lang='*';
				}
				else
				{
					document.getElementById("date1").style.display='none';
					document.getElementById("date2").style.display='none';
					document.getElementById("dActiveDate").lang='';
					document.getElementById("dExpiryDate").lang='';

				}
			}

			function randomStringToInput(clicked_element)
			{
				var self = $(clicked_element);
				var random_string = generateRandomString(6);
				$('input[name=vCouponCode]').val(random_string);

			}
			function generateRandomString(string_length)
			{
				var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				var string = '';
				for(var i = 0; i <= string_length; i++)
				{
					var rand = Math.round(Math.random() * (characters.length - 1));
					var character = characters.substr(rand, 1);
					string = string + character;
				}
				return string;
			}
		</script>
	</script>
</body>
</html>
