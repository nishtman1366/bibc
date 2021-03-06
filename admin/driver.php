<?php
include_once('../common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
include_once('savar_check_permission.php');
if(checkPermission('DRIVER') == false)
die('you dont`t have permission...');


if (!isset($generalobjAdmin)) {
	require_once(TPATH_CLASS . "class.general_admin.php");
	$generalobjAdmin = new General_admin();
}

$actionType = "";
$generalobjAdmin->check_member_login();

$sql="select vLabel,vValue from language_label where vCode='PS'";
$db_lbl=$obj->MySQLSelect($sql);
foreach ($db_lbl as $key => $value) {
	$langage_lbl_array[$value['vLabel']] = $value['vValue'];
}

$adminArea = $_SESSION['sess_area'];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$res_id = isset($_REQUEST['res_id']) ? $_REQUEST['res_id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess=isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$script = 'Driver';

$sql = "select * from country";
$db_country = $obj->MySQLSelect($sql);

$sql = "select * from company WHERE eStatus != 'Deleted'";
$db_company = $obj->MySQLSelect($sql);

$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);

if ($iDriverId != '' && $status != '') {

	$sql="SELECT register_driver.iDriverId from register_driver
	LEFT JOIN company on register_driver.iCompanyId=company.iCompanyId
	LEFT JOIN driver_vehicle on driver_vehicle.iDriverId=register_driver.iDriverId
	WHERE company.eStatus='Active' AND driver_vehicle.eStatus='Active' AND register_driver.iDriverId='".$iDriverId."'".$ssl;
	if ($adminArea && $adminArea != -1) {
		$sql .= " AND company.iAreaId=".$adminArea;
	}

	$Data=$obj->MySQLSelect($sql);
	if($status == 'active') {
		$query = "UPDATE register_driver SET eStatus = 'inactive' WHERE iDriverId = '" . $iDriverId . "'";
		$obj->sql_query($query);

		LoggerVehicle($query);
		LoggerVehicle($_SESSION);

		$sql="SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
		$db_status = $obj->MySQLSelect($sql);
		$maildata['EMAIL'] =$db_status[0]['vEmail'];
		$maildata['NAME'] = $db_status[0]['vName'].' '.$db_status[0]['vLastName'];
		//$maildata['LAST_NAME'] = $db_status[0]['vName'];
		$status = ($db_status[0]['eStatus'] == "active") ? $langage_lbl_array['LBL_ACTIVE_TEXT'] : $langage_lbl_array['LBL_INACTIVE_TEXT'];
		$maildata['DETAIL']=$langage_lbl_array['LBL_YOUR_ACCOUNT_TEXT']." ".$status.".";
		$generalobj->send_email_user("ACCOUNT_STATUS",$maildata);

		$msg=' Driver Inactive Successfully';
		header("Location:driver.php?success=1&msg=".$msg);exit;

	}
	else if(SITE_TYPE !='Demo' && count($Data)>0)
	{
		$query = "UPDATE register_driver SET eStatus = 'active' WHERE iDriverId = '" . $iDriverId . "'";
		$obj->sql_query($query);

		LoggerVehicle($query);
		LoggerVehicle($_SESSION);

		$sql="SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
		$db_status = $obj->MySQLSelect($sql);
		$maildata['EMAIL'] =$db_status[0]['vEmail'];
		$maildata['NAME'] = $db_status[0]['vName'];
		//$maildata['LAST_NAME'] = $db_status[0]['vName'];
		$status = ($db_status[0]['eStatus'] == "active") ? $langage_lbl_array['LBL_ACTIVE_TEXT'] : $langage_lbl_array['LBL_INACTIVE_TEXT'];
		$maildata['DETAIL']=$langage_lbl_array['LBL_YOUR_ACCOUNT_TEXT']." ".$status.".<p>".$langage_lbl_array['LBL_YOU_CAN_LOGIN_TEXT']."</p>";
		$generalobj->send_email_user("ACCOUNT_STATUS",$maildata);

		$msg=' Driver Active Successfully';
		header("Location:driver.php?type=approve&success=1&msg=".$msg);exit;
	}
	else {
		$msg='Driver Have not Any Active Company Or Vehicle';
		header("Location:driver.php?success=2&msg=".$msg."&type=".$actionType);exit;
	}
}


if ($action == 'delete' && $hdn_del_id != '') {
	$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
	//$query    = "DELETE FROM `" . $tbl_name . "` WHERE iDriverId = '" . $id . "'";
	if(SITE_TYPE !='Demo'){
		$query = "UPDATE register_driver SET eStatus = 'Deleted' WHERE iDriverId = '" . $hdn_del_id . "'";
		$obj->sql_query($query);
		$action = "view";
		$success = "1";
		$msg="Driver Deleted Successfully.";
	}
	else{
		header("Location:driver.php?success=2&type=".$actionType);exit;
	}
}

if ($action == 'reset' && $res_id != '') {

	if(SITE_TYPE !='Demo'){
		$query = "UPDATE register_driver SET iTripId='0',vTripStatus='NONE' WHERE iDriverId = '" . $res_id . "'";
		$obj->sql_query($query);
		$action = "view";
		$success = "1";
		$msg="Driver Status Reseted Successfully.";
	}
	else{
		header("Location:driver.php?success=2&type=".$actionType);exit;
	}
}

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLname = isset($_POST['vLname']) ? $_POST['vLname'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '1';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = $generalobj->encrypt($vPassword);
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$iCompanyid = isset($_REQUEST['iCompanyid']) ? $_REQUEST['iCompanyid'] : '';
$tbl_name = "register_driver";

if (isset($_POST['submit'])) {

	$q = "INSERT INTO ";
	$where = '';

	if ($action == 'Edit') {
		$eStatus = ", eStatus = 'Inactive' ";
	} else {
		$eStatus = '';
	}

	if ($id != '') {
		$q = "UPDATE ";
		$where = " WHERE `iDriverId` = '" . $id . "'";
	}

	$query = $q . " `" . $tbl_name . "` SET
	`vName` = '" . $vName . "',
	`vLastName` = '" . $vLname . "',
	`vCountry` = '" . $vCountry . "',
	`vCode` = '" . $vCode . "',
	`vEmail` = '" . $vEmail . "',
	`vLoginId` = '" . $vEmail . "',
	`vPassword` = '" . $vPass . "',
	`vPhone` = '" . $vPhone . "',
	`vLang` = '" . $vLang . "',
	`eStatus` = '" . $eStatus . "',
	`iCompanyId` = '" . $iCompanyId . "'" . $where;
	$obj->sql_query($query);
	$id = ($id != '') ? $id : mysql_insert_id();
	if($action=="Add")
	{
		$ksuccess="1";
	}
	else if ($action=="delete")
	{
		$ksuccess="3";
	}
	else
	{
		$ksuccess="2";
	}
	header("Location:driver.php?id=" . $id . '&success=1&success='.$ksuccess."&type=".$actionType);
}
$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
	$cmp_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$ssqlcmp ='';
if($iCompanyid !=''){
	$ssqlcmp =" AND rd.iCompanyId ='$iCompanyid'";

}
if ($action == 'view') {

	$title = "Pending ";
	if($actionType != "" && $actionType == "approve") {
		$title = "Approved ";
		$ssl = " AND rd.eStatus = 'active'";
	}
	$sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE 1=1 ".$ssl.$cmp_ssql.$ssqlcmp;
	if ($adminArea && $adminArea != -1) {
		$sql .= " AND c.iAreaId=".$adminArea;
	}
	if($iCompanyid == '')
	$sql .= " AND rd.eStatus != 'Deleted'";
	$data_drv = $obj->MySQLSelect($sql);
}

function LoggerVehicle($data)
{
	$text = '';
	if(is_array($data) || is_object($data))
	$text = print_r($data, true);
	else
	$text = $data;

	file_put_contents("LoggerVehicle.txt", $text . "\r\n.................".date('m/d/Y h:i:s a', time())."..............\r\n",FILE_APPEND);
}

if(isset($_REQUEST["Excel"]))
{
	$db_name = "k68ir_DB";
	$conn2 = new mysqli('localhost', 'k68ir_DB', 'Kamelia.irir*****',$db_name);
	$conn2->set_charset("utf8");

	if($_GET['iCompanyid'] != "")
	{
		$setSql = "SELECT `iDriverId`, `vName`, `vLastName`,`iCompanyId`, `vEmail`, `tRegistrationDate`, `vPhone`, `vCity` FROM `register_driver` WHERE iCompanyId = '" . $_GET['iCompanyid'] . "'";
	}
	else {
		$setSql = "SELECT `iDriverId`, `vName`, `vLastName`,`iCompanyId`, `vEmail`, `tRegistrationDate`, `vPhone`, `vCity` FROM `register_driver` WHERE 1";
	}
	$setRec = mysqli_query($conn2, $setSql);

	$columnHeader = '';
	$columnHeader = "Sr NO" . "\t" . "نام راننده" . "\t" . "فامیلی راننده" . "\t" . "نمایندگی" . "\t" . "ایمیل" . "\t" . "ناریخ ثبت نام" . "\t" . "شماره موبایل" . "\t" . "شهر" . "\t";

	$setData = '';
	$ccc = 0;$ccc2 = 0;
	while ($rec = mysqli_fetch_row($setRec)) {
		$rowData = '';
		foreach ($rec as $value) {
			$ccc++;$ccc2++;
			if($ccc == 1)
			{
				$ccc2 = $value;
				$value = '"' . $value . '"' . "\t";
				$rowData .= $value;
			}else {
				if($ccc == 4)
				{
					$sql = "SELECT vCompany FROM company where iCompanyId = '" . $value . "'";

					//die($sql);
					$data_drv2 = $obj->MySQLSelect($sql);
					$value = '"' . $data_drv2[0]['vCompany'] . '"' . "\t";
					$rowData .= $value;
				}else {
					$value = '"' . $value . '"' . "\t";
					$rowData .= $value;
				}}

			}
			$ccc = 0;$ccc2 = 0;
			$setData .= trim($rowData) . "\n";
		}
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=driver.xls");
		header('Content-Transfer-Encoding: binary');
		header("Pragma: no-cache");
		header("Expires: 0");
		echo chr(255).chr(254).iconv("UTF-8", "UTF-16LE//IGNORE", $columnHeader . "\n" . $setData . "\n");
		exit();
	}
	?>
	<!DOCTYPE html>
	<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
	<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
	<!--[if !IE]><!--><html lang="en"> <!--<![endif]-->
	<!-- BEGIN HEAD-->
	<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		
		<title>ادمین | <?php echo $langage_lbl['LBL_DRIVER_TXT_ADMIN'];?> </title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php include_once('global_files.php');?>
		<script>
		$(document).ready(function () {
			$("#show-add-form").click(function () {
				$("#show-add-form").hide(1000);
				$("#add-hide-div").show(1000);
				$("#cancel-add-form").show(1000);
			});

		});
		</script>
		<script>
		$(document).ready(function () {
			$("#cancel-add-form").click(function () {
				$("#cancel-add-form").hide(1000);
				$("#show-add-form").show(1000);
				$("#add-hide-div").hide(1000);
			});

		});

		</script>


		<!--    jquery-autocomplete-master-->
		<link rel="stylesheet" href="../assets/css/amir.autocomplete.css" />


	</head>
	<!-- END  HEAD-->
	<!-- BEGIN BODY-->
	<body class="padTop53 " >

		<!-- MAIN WRAPPER -->
		<div id="wrap">
			<?php include_once('header2.php'); ?>
			<?php include_once('left_menu.php'); ?>

			<!--PAGE CONTENT -->
			<div id="content">
				<div class="inner">
					<div id="add-hide-show-div">
						<div class="row">
							<div class="col-lg-12">
								<h2>راننده / حامل </h2>
								<!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
								<a class="add-btn" href="driver_action.php" style="text-align: center;">افزودن راننده</a>
								<?php	if($_GET['iCompanyid'] != "")
								{
									echo '<a class="add-btn" href="?Excel&iCompanyid=' . $_GET['iCompanyid'] . '" style="text-align: center;">خروجی اکسل</a>';
								}
								else
								{
									echo '<a class="add-btn" href="?Excel&iCompanyid=' . $_GET['iCompanyid'] . '" style="text-align: center;">خروجی اکسل</a>';
								}
								?>
								<input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn">
							</div>
						</div>
						<hr />
					</div>
					<?php if($success == 1) { ?>
						<div class="alert alert-success alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>

							<?php if($ksuccess == "1")
							{?>
								راننده با موفقیت افزوده شد
							<?php }
							else if ($ksuccess=="2")
							{?>
								مشخصات راننده با موفقیت ویرایش شد
							<?php }
							else if($ksuccess=="3")
							{?>
								رانندهبا موفقیت حذف شد
							<?php } ?>
							<?echo $msg;?>

						</div><br/>
					<?php }elseif ($success == 2 & $msg == '') { ?>
						<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
							"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
						</div><br/>
					<?php } elseif ($success == 2 & $msg != '') { ?>
						<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
							<?echo $msg;?>
						</div><br/>
					<?php } ?>
					<div id="add-hide-div">
						<form name = "myForm" method="post" action="">
							<div class="page-form">
								<h2>افزودن راننده</h2>
								<br><br>
								<ul>
									<li>
										نام<br>
										<input type="text" name="vName" class="form-control" placeholder="First" required>
									</li>
									<li>
										نام خانوادگی<br>
										<input type="text" name="vLname" class="form-control" placeholder="Last" required>
									</li>
									<li>
										ایمیل<br>
										<input type="email" name="vEmail" class="form-control" placeholder="" required>
									</li>
									<li>
										شرکت<br>
										<select class="form-control" name = 'iCompanyId' id = 'iCompanyId' required>
											<option value="">--select--</option>
											<?php for ($i = 0; $i < count($db_company); $i++) { ?>
												<option value ="<?php echo  $db_company[$i]['iCompanyId'] ?>"><?php echo  $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ")"; ?></option>
											<?php } ?>
										</select>
										<!--<input type="text" name="vEmail" class="form-control" placeholder="" >-->
									</li>
									<li>
										کشور<br>
										<select class="contry-select" name = 'vCountry' onChange="changeCode(this.value);" required>
											<option value="">--select--</option>
											<?php for ($i = 0; $i < count($db_country); $i++) { ?>
												<option value = "<?php echo  $db_country[$i]['vCountryCode'] ?>"><?php echo  $db_country[$i]['vCountry'] ?></option>
											<?php } ?>
										</select>
										<!--<input type="text" name="vEmail" class="form-control" placeholder="" >-->
									</li>
									<li>
										زبان<br>
										<select name = 'vLang' class="language-select" required>
											<option value="">--select--</option>
											<?	for ($i = 0; $i < count($db_lang); $i++) { ?>
												<option value = "<?php echo  $db_lang[$i]['vCode'] ?>"><?php echo  $db_lang[$i]['vTitle'] ?></option>
											<?php } ?>
										</select>
										<!--<input type="text" name="vEmail" class="form-control" placeholder="" >-->
									</li>
									<li>
										موبایل<br>
										<input type="text" class="form-select-2" id="code" name="vCode">
										<input type="text" name="vPhone" class="mobile-text" placeholder="" required pattern=".{10}"/>
									</li>

									<li>
										PASSWORD<br>
										<input type="password" class="form-control" placeholder="" name="vPassword" required>
									</li>

									<li>
										<input type="submit" name="submit" class="submit-btn" value="SUBMIT" >
									</li>
								</ul>
							</div>
						</form>
					</div>

					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading driver-neww1">
										<b>جست و جو راننده</b>
									</div>
									<div class="panel-body">
										<input class="form-control" type="text" id="searchDriver" name="searchDriver"  style="width:18%;display:table-row-group;" placeholder="راننده" autocomplete="off" value="<?php echo  $searchDriver ?>">

										<input type="hidden" id="iDriverId" name="iDriverId" value="<?php echo  $iDriverId ?>">
										<button type="button" id="btnShowDriver" class="btn btn-default btn-sm">نمایش</button>

									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading driver-neww1">
										<b><?php echo $langage_lbl['LBL_DRIVER_TXT_ADMIN'];?> </b>
										<div class="button-group driver-neww">
											<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="">Select Option</span> <span class="caret"></span></button>
											<ul class="dropdown-menu">
												<li><a href="#" class="small" data-value="Active" tabIndex="-1"><input type="checkbox" id="checkbox" checked="checked"/>&nbsp;Active</a></li>
												<li><a href="#" class="small" data-value="Inactive" tabIndex="-1"><input type="checkbox" id="checkbox" />&nbsp;Inactive</a></li>


											</ul>
										</div>
									</div>
									<div class="panel-body">
										<div class="table-responsive" id="data_drv001">
											<table class="table table-striped table-bordered table-hover admin-td-button" id="dataTables-example">
												<thead>
													<tr>
														<th>نام راننده</th>
														<th>نام شرکت</th>
														<th>ایمیل</th>
														<th>تاریخ ثبت نام</th>
														<!--<th>SERVICE LOCATION</th>-->
														<th>موبایل</th>
														<!--<th>LANGUAGE</th>-->
														<th>شهر</th>
														<th>وضعیت</th>
														<th>ویرایش مدارک</th>
														<th style="text-align:center;" align="center">فعالیت</th>

													</tr>
												</thead>
												<tbody>
													<?php for ($i = 0; $i < count($data_drv); $i++) { ?>
														<tr class="gradeA" >
															<td width="10%"><?php echo  $data_drv[$i]['vName'] . ' ' . $data_drv[$i]['vLastName']; ?></td>
															<td width="10%"><?php echo  $data_drv[$i]['companyFirstName']; ?></td>
															<td width="10%"><?php echo  $generalobjAdmin->clearEmail($data_drv[$i]['vEmail']);?></td>
															<td width="15%" data-order="<?php echo $data_drv[$i]['iDriverId']; ?>"><?php echo jdate('d-F-Y',strtotime($data_drv[$i]['tRegistrationDate'])); ?></td>
															<!--<td class="center"><?php echo  $data_drv[$i]['vServiceLoc']; ?></td>-->
															<td width="8%"><?php echo  $generalobjAdmin->clearPhone($data_drv[$i]['vPhone']);?></td>
															<!--<td><?php echo  $data_drv[$i]['vLang']; ?></td>-->
															<td width="8%"><?php echo  $data_drv[$i]['vCity'];?></td>
															<td width="8%" align="center">
																<?php if($data_drv[$i]['eDefault']!='Yes'){?>

																	<?php if($data_drv[$i]['eStatus'] == 'active') {
																		$dis_img = "img/active-icon.png";
																	}else if($data_drv[$i]['eStatus'] == 'inactive'){
																		$dis_img = "img/inactive-icon.png";
																	}else if($data_drv[$i]['eStatus'] == 'Deleted'){
																		$dis_img = "img/delete-icon.png";
																	}?>
																	<img src="<?php echo $dis_img;?>" alt="image">
																	<?php
																}
																else
																{
																	?><img src="img/active-icon.png" alt="image"><?
																}
																?>
															</td>
															<td width="10%" align="center">
																<?php if($data_drv[$i]['eStatus']=="Deleted"){
																	$newUrl2 = "javascript:void(0);";
																}else {
																	$newUrl2 = "driver_document_action.php?id=".$data_drv[$i]['iDriverId']."&action=edit";
																}
																?>
																<?php if($data_drv[$i]['eStatus']!="Deleted"){?>
																	<a href="<?php echo  $newUrl2; ?>" data-toggle="tooltip" title="Edit Driver Document">
																		<img src="img/edit-doc.png" alt="Edit Document" >
																	</a>
																<?php }?>
															</td>

															<td width="20%" align="center">
																<?php if($data_drv[$i]['eStatus']=="Deleted"){
																	$newUrl = "javascript:void(0);";
																}else {
																	$newUrl = "driver_action.php?id=".$data_drv[$i]['iDriverId'];
																}
																?>
																<?php if($data_drv[$i]['eStatus']!="Deleted"){?>
																	<a href="<?php echo  $newUrl; ?>" data-toggle="tooltip" title="Edit Driver">
																		<img src="img/edit-icon.png" alt="Edit">
																	</a>
																<?php }?>

																<a href="driver.php?iDriverId=<?php echo  $data_drv[$i]['iDriverId']; ?>&status=inactive" data-toggle="tooltip" title="Active Driver">
																	<img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
																</a>
																<a href="driver.php?iDriverId=<?php echo  $data_drv[$i]['iDriverId']; ?>&status=active" data-toggle="tooltip" title="Inactive Driver">
																	<img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
																</a>

																<?php if($data_drv[$i]['eStatus']!="Deleted"){?>
																	<form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm('Are you sure you want to delete <?php echo  $data_drv[$i]['vName']; ?> <?php echo  $data_drv[$i]['vLastName']; ?> record?')" class="margin0">
																		<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo  $data_drv[$i]['iDriverId']; ?>">
																		<input type="hidden" name="action" id="action" value="delete">
																		<button class="remove_btn001" data-toggle="tooltip" title="Delete Driver">
																			<img src="img/delete-icon.png" alt="Delete">
																		</button>
																	</form>
																<?php }else{?>
																	<label></label>
																<?php } ?>

																<?php if($data_drv[$i]['eStatus']!="Deleted"){?>
																	<form name="reset_form" id="reset_form" method="post" action="" onSubmit="return confirm('Are you sure ? You want to reset <?php echo  $data_drv[$i]['vName']; ?> <?php echo  $data_drv[$i]['vLastName']; ?> account?')" class="margin0">
																		<input type="hidden" name="action" id="action" value="reset">
																		<input type="hidden" name="res_id" id="res_id" value="<?php echo  $data_drv[$i]['iDriverId']; ?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Reset Rider">
																			<img src="img/reset-icon.png" alt="Reset">
																		</button>
																	</form>
																<?php }else{?>
																	<label></label>
																<?php } ?>
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
					<div style="clear:both;"></div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->


		<?php include_once('footer.php');?>
		<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>

		<script src="../assets/js/amir.autocomplete.js"></script>


		<script type="text/javascript">
		var options = ["Active"];

		$( '.dropdown-menu a' ).on( 'click', function( event ) {
			//alert(options);
			var $target = $( event.currentTarget ),
			val = $target.attr( 'data-value' ),
			$inp = $target.find( 'input' ),
			idx;

			if ( ( idx = options.indexOf( val ) ) > -1 ) {
				options.splice( idx, 1 );
				setTimeout( function() { $inp.prop( 'checked', false ) }, 0);
			} else {
				options.push( val );
				setTimeout( function() { $inp.prop( 'checked', true ) }, 0);
			}
			//alert(options);
			$( event.target ).blur();

			//console.log( options );
			//alert(options);
			var request = $.ajax({
				type: "POST",
				url: 'change_driver_list.php',
				data: {result:JSON.stringify(options)},
				success: function (data)
				{
					$("#data_drv001").html('');
					$("#data_drv001").html(data);
					//document.getElementById("code").value = data;
					//window.location = 'profile.php';
				}
			});
			return false;
		});
		</script>

		<script>
		$(document).ready(function () {
			$('#dataTables-example').dataTable({
				"order": [[ 3, "desc" ]]
			});


			$("#btnShowDriver").click(function(){
				if($("#iDriverId").val() != '')
				{
					window.open('driver_action.php?id='+$("#iDriverId").val());

					return false;
				}

			});
		});


		</script>
	</body>
	<!-- END BODY-->
	</html>
