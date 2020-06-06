<?php
	include_once('../common.php');
	if (!isset($generalobjAdmin)) {
		require_once(TPATH_CLASS . "class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	require_once(TPATH_CLASS . "/Imagecrop.class.php");
	$thumb = new thumbnail();

	$sql = "select * from country";
	$db_country = $obj->MySQLSelect($sql);

	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$success = isset($_REQUEST['success']) ? $_REQUEST['success'] :'';
	$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? 'Edit' : 'Add';
	$script = 'Vehicle';
	$sql = "select * from language_master where eStatus = 'Active'";
	$db_lang = $obj->MySQLSelect($sql);

	$sql = "select * from driver_vehicle where iDriverVehicleId = '" . $_REQUEST['id'] . "'";
	$db_user = $obj->MySQLSelect($sql);
	//print_r($db_user);

	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : 0;
	$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';

	if ($action == 'noc') {

		if(SITE_TYPE=='Demo')
		{
				$var_msg='"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.';
				header("location:vehicle_document_action.php?success=2&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
				exit;
		}


		if (isset($_POST['doc_path'])) {
			$doc_path = $_POST['doc_path'];
		}
		$temp_gallery = $doc_path . '/';
		$image_object = $_FILES['noc']['tmp_name'];
		$image_name = $_FILES['noc']['name'];

		if($image_name=="")
		{
			$var_msg="Please Upload valid file format for Document. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png and Document Should not empty";
			header("location:vehicle_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			//$generalobjAdmin->getPostForm($_POST, $var_msg, "company_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);

			exit;
		}

		if ($image_name != "") {
			$check_file_query = "select * from driver_vehicle where iDriverVehicleId=" . $_REQUEST['id'];
			$check_file = $obj->sql_query($check_file_query);
			$check_file['vInsurance'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vInsurance'];
			//echo $doc_path;
			$filecheck = basename($_FILES['noc']['name']);
			$fileextarr = explode(".", $filecheck);
			$ext = strtolower($fileextarr[count($fileextarr) - 1]);
			$flag_error = 0;
			if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
				$flag_error = 1;
				$var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
			}

			if ($flag_error == 1) {
				$generalobj->getPostForm($_POST, $var_msg, "vehicle_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
				exit;
				} else {
				$Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
				if (!is_dir($Photo_Gallery_folder)) {
					mkdir($Photo_Gallery_folder, 0777);
				}
				//$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
				$vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
				$vImage = $vFile[0];
				$var_msg = "INSURANCE File uploaded successfully";
				$tbl = 'driver_vehicle';
				$sql = "SELECT * FROM " . $tbl . " WHERE iDriverVehicleId = '" .  $_REQUEST['id'] . "'";
				$db_data = $obj->MySQLSelect($sql);
				$q = "INSERT INTO ";
				$where = '';

				if (count($db_data) > 0) {
					$q = "UPDATE ";
					$where = " WHERE `iDriverVehicleId` = '" . $_REQUEST['id'] . "'";
				}
				$query = $q . " `" . $tbl . "` SET `vInsurance` = '" . $vImage . "'" . $where ;
				$obj->sql_query($query);

				//Start :: Log Data Save
				if(empty($check_file[0]['vInsurance'])){ $vNocPath = $vImage ; }else{ $vNocPath = $check_file[0]['vInsurance']; }
				$generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'driver_vehicle','certi',$vNocPath);
				//End :: Log Data Save

				// Start :: Status in edit a Document upload time
				//$set_value = "`eStatus` ='inactive'";
				//$generalobj->estatus_change('driver_vehicle','iDriverVehicleId',$_REQUEST['id'],$set_value);
				// End :: Status in edit a Document upload time

				header("location:vehicle_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			}
			}
	}


	if ($action == 'certi') {

		if(SITE_TYPE=='Demo')
		{
				$var_msg='"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.';
				header("location:vehicle_document_action.php?success=2&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
				exit;
		}

		if (isset($_POST['doc_path'])) {
			$doc_path = $_POST['doc_path'];
		}
		$temp_gallery = $doc_path . '/';
		$image_object = $_FILES['certi']['tmp_name'];
		$image_name = $_FILES['certi']['name'];

		if($image_name=="")
		{
			$var_msg="Please Upload valid file format for Document. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png and Document Should not empty";
			header("location:vehicle_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			//$generalobjAdmin->getPostForm($_POST, $var_msg, "company_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);

			exit;
		}

		if ($image_name != "") {
			$check_file_query = "select * from driver_vehicle where iDriverVehicleId=" . $_REQUEST['id'];
			$check_file = $obj->sql_query($check_file_query);
			//$check_file['vPermit'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vPermit'];
			$check_file['vPermit'] = $doc_path . '/' . $check_file[0]['vPermit'];
			//echo $doc_path;
			$filecheck = basename($_FILES['certi']['name']);
			$fileextarr = explode(".", $filecheck);
			$ext = strtolower($fileextarr[count($fileextarr) - 1]);
			$flag_error = 0;
			if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
				$flag_error = 1;
				$var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
			}

			if ($flag_error == 1) {
				$generalobj->getPostForm($_POST, $var_msg, "vehicle_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
				exit;
				} else {
				$Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
				//$Photo_Gallery_folder = $doc_path . '/';
				if (!is_dir($Photo_Gallery_folder)) {
					mkdir($Photo_Gallery_folder, 0777);
				}
				//$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
				$vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
				$vImage = $vFile[0];
				$var_msg = "INSURANCE File uploaded successfully";
				$tbl = 'driver_vehicle';
				$sql = "SELECT * FROM " . $tbl . " WHERE iDriverVehicleId = '" .  $_REQUEST['id'] . "'";
				$db_data = $obj->MySQLSelect($sql);
				$q = "INSERT INTO ";
				$where = '';

				if (count($db_data) > 0) {
					$q = "UPDATE ";
					$where = " WHERE `iDriverVehicleId` = '" . $_REQUEST['id'] . "'";
				}
				$query = $q . " `" . $tbl . "` SET `vPermit` = '" . $vImage . "'" . $where ;
				$obj->sql_query($query);

				//Start :: Log Data Save
				if(empty($check_file[0]['vPermit'])){ $vNocPath = $vImage ; }else{ $vNocPath = $check_file[0]['vPermit']; }
				$generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'driver_vehicle','noc',$vNocPath);
				//End :: Log Data Save

				// Start :: Status in edit a Document upload time
				//$set_value = "`eStatus` ='inactive'";
				//$generalobj->estatus_change('driver_vehicle','iDriverVehicleId',$_REQUEST['id'],$set_value);
				// End :: Status in edit a Document upload time

				header("location:vehicle_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			}
			}
	}


	if ($action == 'reg') {

		if(SITE_TYPE=='Demo')
		{
				$var_msg='"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.';
				header("location:vehicle_document_action.php?success=2&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
				exit;
		}

		if (isset($_POST['doc_path'])) {
			$doc_path = $_POST['doc_path'];
		}
		$temp_gallery = $doc_path . '/';
		$image_object = $_FILES['reg']['tmp_name'];
		$image_name = $_FILES['reg']['name'];

		if($image_name=="")
		{
			$var_msg="Please Upload valid file format for Document. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png and Document Should not empty";
			header("location:vehicle_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			//$generalobjAdmin->getPostForm($_POST, $var_msg, "company_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);

			exit;
		}

		if ($image_name != "") {
			$check_file_query = "select * from driver_vehicle where iDriverVehicleId=" . $_REQUEST['id'];
			$check_file = $obj->sql_query($check_file_query);
			//$check_file['vRegisteration'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vRegisteration'];
			$check_file['vRegisteration'] = $doc_path . '/' . $check_file[0]['vRegisteration'];
			//echo $doc_path;
			$filecheck = basename($_FILES['reg']['name']);
			$fileextarr = explode(".", $filecheck);
			$ext = strtolower($fileextarr[count($fileextarr) - 1]);
			$flag_error = 0;
			if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
				$flag_error = 1;
				$var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
			}

			if ($flag_error == 1) {
				$generalobj->getPostForm($_POST, $var_msg, "vehicle_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
				exit;
				} else {
				$Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
				//$Photo_Gallery_folder = $doc_path . '/';
				if (!is_dir($Photo_Gallery_folder)) {
					mkdir($Photo_Gallery_folder, 0777);
				}
				//$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
				$vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
				$vImage = $vFile[0];
				$var_msg = "INSURANCE File uploaded successfully";
				$tbl = 'driver_vehicle';
				$sql = "SELECT * FROM " . $tbl . " WHERE iDriverVehicleId = '" .  $_REQUEST['id'] . "'";
				$db_data = $obj->MySQLSelect($sql);
				$q = "INSERT INTO ";
				$where = '';

				if (count($db_data) > 0) {
					$q = "UPDATE ";
					$where = " WHERE `iDriverVehicleId` = '" . $_REQUEST['id'] . "'";
				}
				$query = $q . " `" . $tbl . "` SET `vRegisteration` = '" . $vImage . "'" . $where ;
				$obj->sql_query($query);

				//Start :: Log Data Save
				if(empty($check_file[0]['vRegisteration'])){ $vNocPath = $vImage ; }else{ $vNocPath = $check_file[0]['vRegisteration']; }
				$generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'driver_vehicle','reg',$vNocPath);
				//End :: Log Data Save

				// Start :: Status in edit a Document upload time
				//$set_value = "`eStatus` ='inactive'";
				//$generalobj->estatus_change('driver_vehicle','iDriverVehicleId',$_REQUEST['id'],$set_value);
				// End :: Status in edit a Document upload time

				header("location:vehicle_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			}
			}
	}

	 $sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
				FROM driver_vehicle dv, register_driver rd, make m, model md, company c
				WHERE
				  dv.eStatus != 'Deleted'
				  AND dv.iDriverId = rd.iDriverId
				  AND dv.iCompanyId = c.iCompanyId
				  AND dv.iModelId = md.iModelId
				  AND dv.iMakeId = m.iMakeId
				  AND dv.iDriverVehicleId =".$id;
		//$vehicles = $obj->MySQLSelect($sql);
		$db_data = $obj->MySQLSelect($sql);
		//print_r($db_data);
if($db_data[0]['vPermit']!=NULL && $db_data[0]['vRegisteration'] !=NULL && $db_data[0]['vInsurance'] !=NULL)
		{
			$maildata['EMAIL'] =$db_data[0]['vEmail'];
			$maildata['NAME'] = $db_data[0]['vName'].' '.$db_data[0]['vLastName'];
		//$maildata['LAST_NAME'] = $vehicles[0]['companyFirstName'];
		if($db_data[0]['eStatus'] == "Inactive"){
			$maildata['DETAIL']="Your Vehicle's documents are being verified. You will be notified once your documents are processed and the account becomes active.";
				}else{
				$maildata['DETAIL']="Your Vehicle Documents For ".$db_data[0]['vTitle']." And  COMPANY ".$db_data[0]['companyFirstName'] ." is uploaded ".$db_data[0]['eStatus'];

				}
			$generalobj->send_email_user("ACCOUNT_STATUS",$maildata);

		}


?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title><?php echo $SITE_NAME?> | Driver <?php echo  $action; ?></title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="" name="keywords" />
		<meta content="" name="description" />
		<meta content="" name="author" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php  include_once('global_files.php'); ?>
		<!-- On OFF switch -->
		<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
		<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
		<link rel="stylesheet" href="../assets/css/bootstrap-fileupload.min.css" >
		 <script src="../assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
	</head>
	<!-- END  HEAD-->
	<!-- BEGIN BODY-->
	<body class="padTop53 " >

		<!-- MAIN WRAPPER -->
		<div id="wrap">
			<?
				include_once('header.php');
			?>
			<?
				include_once('left_menu.php');
			?>
			<!--PAGE CONTENT -->
			<div id="content">
				<div class="inner">
					<div class="row">
						<div class="col-lg-12">
							<h2><?php echo  ucfirst($action); ?> مدارک <?php echo  $_REQUEST['vehicle'];?> </h2>
							<a href="vehicles.php">
								<input type="button" value="بازگشت به لیست" class="add-btn">
							</a>
						</div>
					</div>
					<hr />
					<div class="body-div">
						<div class="form-group">
							<?php if ($success == 1) {?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									<?php echo  $var_msg ?>
								</div><br/>
							<?} ?>
							<?php if ($success == 2) {?>
								<div class="alert alert-danger alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									<?php echo  $var_msg ?>
								</div><br/>
							<?} ?>
							<input type="hidden" name="id" value="<?php echo  $id; ?>"/>
							<div class="row">
								<div class="col-sm-12">
									<h4 style="margin-top:0px;">مدارک</h4>
								</div>
							</div>
							<div class="row company-document-action">

								<div class="col-lg-3">
									<div class="panel panel-default upload-clicking">
										<div class="panel-heading">
											بیمه
										</div>
										<div class="panel-body">
											<?php if ($db_user[0]['vInsurance'] != '') {

												$img_path = $tconfig["tsite_upload_vehicle_doc_panel"];
											?>
											<?php $file_ext = $generalobj->file_ext($db_user[0]['vInsurance']);
												if($file_ext == 'is_image'){ ?>
												<img src = "<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"] . '/' .$_REQUEST['id'].'/'. $db_user[0]['vInsurance'] ?>" style="width:200px;" alt ="یافت نشد"/>
												<?php }else{ ?>
												<a href="<?php echo  $img_path  . '/' .$_REQUEST['id'].'/'. $db_user[0]['vInsurance']  ?>" target="_blank">فایل بیمه</a>
											<?php } ?>
											<?php } else { ?>
											نیاز به آپلود
											<?php } ?>
                                            <b>
											<button class="btn btn-info" data-toggle="modal" data-target="#uiModal_2">
											<?php if ($db_user[0]['vInsurance'] != '') {
												echo $langage_lbl['LBL_DOC_UPDATE_INS'];
												}else{
													echo $langage_lbl['LBL_DOC_ADD_INS'];
												}?>
											</button>
                                            </b>
										</div>
									</div>
								</div>


								<div class="col-lg-3">
									<div class="panel panel-default upload-clicking">
										<div class="panel-heading">
											مجوز
										</div>
										<div class="panel-body">
											<?php if ($db_user[0]['vPermit'] != '') {

												$img_path = $tconfig["tsite_upload_vehicle_doc_panel"];
											?>
											<?php $file_ext = $generalobj->file_ext($db_user[0]['vPermit']);
												if($file_ext == 'is_image'){ ?>
												<img src = "<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $_REQUEST['id'].'/'.$db_user[0]['vPermit'] ?>" style="width:200px;" alt ="یافت نشد"/>
												<?php }else{ ?>
												<a href="<?php echo  $img_path  . '/'. $_REQUEST['id'].'/'. $db_user[0]['vPermit']  ?>" target="_blank">فایل مجوز</a>
											<?php } ?>
											<?php } else { ?>
										نیاز به آپلود
											<?php } ?>
                                            <b>
											<button class="btn btn-info" data-toggle="modal" data-target="#uiModal_3">

												<?php if ($db_user[0]['vPermit'] != '') {
												echo $langage_lbl['LBL_DOC_UPDATE_PER'];
												}else{
													echo $langage_lbl['LBL_DOC_ADD_PER'];
												}?>
											</button>
                                            </b>
										</div>
									</div>
								</div>

								<div class="col-lg-3">
									<div class="panel panel-default upload-clicking">
										<div class="panel-heading">
											ثبت نام نقلیه
										</div>
										<div class="panel-body">
											<?php
												if ($db_user[0]['vRegisteration'] != '') {
												$img_path = $tconfig["tsite_upload_vehicle_doc"]; ?>
												<?php $file_ext = $generalobj->file_ext($db_user[0]['vRegisteration']);
													if($file_ext == 'is_image'){ ?>
													<img src = "<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"] . $_REQUEST['id'].'/'.
													 $db_user[0]['vRegisteration'] ?>" style="width:200px;" alt ="یافت نشد"/>
													<?php }else{ ?>
													<a href="<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"]. '/' .$_REQUEST['id'].'/'.$db_user[0]['vRegisteration']  ?>" target="_blank"> فایل</a>
												<?php } ?>
												<?php } else { ?>
												نیاز به آپلود
											<?php } ?>
                                            <b>
											<button class="btn btn-info" data-toggle="modal" data-target="#uiModal_4" >

												<?php if ($db_user[0]['vRegisteration'] != '') {
												echo $langage_lbl['LBL_DOC_UPDATE_REG'];
												}else{
													echo $langage_lbl['LBL_DOC_ADD_REG'];
												}?>
											</button>
                                            </b>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="modal fade" id="uiModal_2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
										<div class="modal-content image-upload-1">
											<div class="upload-content">
												<h4>بیمه</h4>
												<form class="form-horizontal" id="frm7" method="post" enctype="multipart/form-data" action="vehicle_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm7">
													<input type="hidden" name="action" value ="noc"/>
													<input type="hidden" name="doc_path" value ="    <?php echo $tconfig["tsite_upload_vehicle_doc"]; ?>"/>
													<div class="form-group">
														<div class="col-lg-12">
															<div class="fileupload fileupload-new" data-provides="fileupload">
																<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px; ">
																	<?php if ($db_user[0]['vInsurance'] == '') { ?>
																		تصویر بیمه
																		<?php } else { ?>
																		<?php $file_ext = $generalobj->file_ext($db_user[0]['vInsurance']);
																			if($file_ext == 'is_image'){ ?>
																			<img src = "<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"] . $_REQUEST['id'].'/' . $db_user[0]['vInsurance'] ?>" style="width:200px;" alt ="یافت نشد"/>
																			<?php }else{ ?>
																			<a href="<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"] .$_REQUEST['id'].  '/' . $db_user[0]['vInsurance'] ?>" target="_blank">فایل بیمه</a>
																		<?php } ?>
																	<?php } ?>
																</div>
																<div>
																	<span class="btn btn-file btn-success"><span class="fileupload-new">آپلود</span><span class="fileupload-exists">تغییر</span><input type="file" name="noc"/></span>
																	<a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">حذف</a>
																</div>
															</div>
														</div>
													</div>
													<input type="submit" class="save" name="save" value="ذخیره"><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="لغو">
												</form>


											</div>

										</div>
									</div>

									<div class="col-lg-12">
										<div class="modal fade" id="uiModal_4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
											<div class="modal-content image-upload-1">
												<div class="upload-content">
													<h4>ثبت نام</h4>
													<form class="form-horizontal" id="frm7" method="post" enctype="multipart/form-data" action="vehicle_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm7">
														<input type="hidden" name="action" value ="reg"/>
														<input type="hidden" name="doc_path" value ="    <?php echo $tconfig["tsite_upload_vehicle_doc"]; ?>"/>
														<div class="form-group">
															<div class="col-lg-12">
																<div class="fileupload fileupload-new" data-provides="fileupload">
																	<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px; ">
																		<?php if ($db_user[0]['vRegisteration'] == '') { ?>
																			تصویر ثبت نام
																			<?php } else { ?>
																			<?php $file_ext = $generalobj->file_ext($db_user[0]['vRegisteration']);
																				if($file_ext == 'is_image'){ ?>
																				<img src = "<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"] .$_REQUEST['id']. '/' . $db_user[0]['vRegisteration'] ?>" style="width:200px;" alt ="یافت نشد"/>
																				<?php }else{ ?>
																				<a href="<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"].$_REQUEST['id'] .  '/' . $db_user[0]['vRegisteration'] ?>" target="_blank">فایل ثبت نام</a>
																			<?php } ?>
																		<?php } ?>
																	</div>
																	<div>
																		<span class="btn btn-file btn-success"><span class="fileupload-new">آپلود</span><span class="fileupload-exists">تغییر</span><input type="file" name="reg"/></span>
																		<a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">حذف</a>
																	</div>
																</div>
															</div>
														</div>
														<input type="submit" class="save" name="save" value="ذخیره"><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="لغو">
													</form>


												</div>

											</div>
										</div>

										<div class="col-lg-12">
											<div class="modal fade" id="uiModal_3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
												<div class="modal-content image-upload-1">
													<div class="upload-content">
														<h4>گواهی تایید پلیس یا سازمان</h4>
														<form class="form-horizontal" id="frm8" method="post" enctype="multipart/form-data" action="vehicle_document_action.php?id=<?php echo $_REQUEST['id']; ?>">
															<input type="hidden" name="action" value="certi"/>
															<input type="hidden" name="doc_path" value ="<?php echo $tconfig["tsite_upload_vehicle_doc"]; ?> "/>

															<div class="form-group">


																<div class="col-lg-12">
																	<div class="fileupload fileupload-new" data-provides="fileupload">
																		<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px; ">
																			<?php if ($db_user[0]['vPermit'] =='') { ?>
																				تصویر
																				<?php } else {
																					$path=$tconfig["tsite_upload_vehicle_doc_panel"];
																				?>
																				<?php $file_ext = $generalobj->file_ext($db_user[0]['vPermit']);
																					if($file_ext == 'is_image'){ ?>
																					<img src = "<?php echo  $tconfig["tsite_upload_vehicle_doc_panel"].$_REQUEST['id']. '/'. $db_user[0]['vPermit'] ?>" style="width:200px;" alt ="یافت نشد"/>
																					<?php }else{ ?>
																					<a href="<?php echo   $path .$_REQUEST['id'].'/' . $db_user[0]['vPermit']  ?>" target="_blank">فایل</a>
																				<?php } ?>
                                        <?php } ?>
																		</div>
																		<div>
																			<span class="btn btn-file btn-success"><span class="fileupload-new">آپلود</span><span class="fileupload-exists">تغییر</span>
																			<input type="file" name="certi"/></span>
																			<a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">حذف</a>
																		</div>
																	</div>
																</div>
															</div>
															<input type="submit" class="save" name="save" value="ذخیره"><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="لفو">
														</form>
													</div>
												</div>
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>






			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->

		<?php include_once('footer.php');?>
		<link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css" />
		<!-- Start :: Datepicker-->

		<!-- Start :: Datepicker Script-->
		<script src="../assets/js/jquery-ui.min.js"></script>
		<script src="./assets/plugins/uniform/jquery.uniform.min.js"></script>
		<script src="../assets/plugins/inputlimiter/jquery.inputlimiter.1.3.1.min.js"></script>
		<script src="../assets/plugins/chosen/chosen.jquery.min.js"></script>
		<script src="../assets/plugins/colorpicker/js/bootstrap-colorpicker.js"></script>
		<script src="../assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
		<script src="../assets/plugins/validVal/js/jquery.validVal.min.js"></script>
		<script src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
		<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
		<script src="../assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script>
		<script src="../assets/plugins/autosize/jquery.autosize.min.js"></script>
		<script src="../assets/plugins/jasny/js/bootstrap-inputmask.js"></script>
		<script src="../assets/js/formsInit.js"></script>
		<script>
			$(function () {
				formInit();
			});
		</script>
		<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
	</body>
	<!-- END BODY-->
</html>


<!-- Start :: Datepicker css-->
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<!-- Start :: Datepicker-->

<!-- Start :: Datepicker Script-->
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/uniform/jquery.uniform.min.js"></script>
<script src="../assets/plugins/inputlimiter/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="../assets/plugins/chosen/chosen.jquery.min.js"></script>
<script src="../assets/plugins/colorpicker/js/bootstrap-colorpicker.js"></script>
<script src="../assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
<script src="../assets/plugins/validVal/js/jquery.validVal.min.js"></script>
<script src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script src="../assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="../assets/plugins/autosize/jquery.autosize.min.js"></script>
<script src="../assets/plugins/jasny/js/bootstrap-inputmask.js"></script>
<script src="../assets/js/formsInit.js"></script>
<script>
	$(function () {
		formInit();
	});

</script>
