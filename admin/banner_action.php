<?
	include_once('../common.php');
	require_once(TPATH_CLASS."Imagecrop.class.php");

	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	$default_lang = $generalobj->get_default_lang();
	$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:''; // iUniqueId
	$success	= isset($_REQUEST['success'])?$_REQUEST['success']:'';
	$action 	= ($id != '')?'Edit':'Add';

	//$temp_gallery = $tconfig["tpanel_path"];
	$tbl_name 	= 'banners';
	$script 	= 'Banner';

	//echo '<prE>'; print_R($_REQUEST); echo '</pre>';

	// fetch all lang from language_master table
	$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
	$db_master = $obj->MySQLSelect($sql);
	$count_all = count($db_master);
	//echo '<pre>'; print_R($db_master); echo '</pre>';

	// set all variables with either post (when submit) either blank (when insert)
	$vImage 		= isset($_POST['vImage'])?$_POST['vImage']:'';
	$eStatus_check 	= isset($_POST['eStatus'])?$_POST['eStatus']:'off';
	$vTitle = isset($_POST['vTitle'])?$_POST['vTitle']:'';
	$eStatus 		= ($eStatus_check == 'on')?'Active':'Inactive';
	$thumb = new thumbnail();
	/* to fetch max iDisplayOrder from table for insert */
	$select_order	= $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM ".$tbl_name." WHERE vCode = '".$default_lang."'");
	$iDisplayOrder	= isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
	$iDisplayOrder	= $iDisplayOrder + 1; // Maximum order number

	$iDisplayOrder	= isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
	$temp_order 	= isset($_POST['temp_order'])? $_POST['temp_order'] : "";

	/*if($count_all > 0) {
		for($i=0;$i<$count_all;$i++) {
		$vTitle = 'vTitle_'.$db_master[$i]['vCode'];
		$$vTitle  = isset($_POST[$vTitle])?$_POST[$vTitle]:'';
		}
	}*/

	if(isset($_POST['submit'])) { //form submit

		//echo "<pre>";print_r($_REQUEST);exit;
		if($temp_order > $iDisplayOrder) {
			for($i = $temp_order; $i >= $iDisplayOrder; $i--) {
				$obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i+1)." WHERE iDisplayOrder = ".$i);
			}
			} else if($temp_order < $iDisplayOrder) {
			for($i = $temp_order; $i <= $iDisplayOrder; $i++) {
				$obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i-1)." WHERE iDisplayOrder = ".$i);
			}
		}

		$select_order		= $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM ".$tbl_name." WHERE vCode = '".$default_lang."'");
		$iUniqueId			= isset($select_order[0]['iUniqueId'])?$select_order[0]['iUniqueId']:0;
		$iUniqueId			= $iUniqueId + 1; // Maximum order number

		if($count_all > 0) {
			for($i=0;$i<$count_all;$i++) {

				$q = "INSERT INTO ";
				$where = '';

				if($id != '' ){
					$q = "UPDATE ";
					$where = " WHERE `iUniqueId` = '".$id."' AND vCode = '".$db_master[$i]['vCode']."'";
					$iUniqueId = $id;
				}
				$image_object = $_FILES['vImage']['tmp_name'];
				$image_name   = $_FILES['vImage']['name'];

				if($image_name != ""){
					$filecheck = basename($_FILES['vImage']['name']);
					$fileextarr = explode(".",$filecheck);
					$ext=strtolower($fileextarr[count($fileextarr)-1]);
					$flag_error = 0;
					if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
						$flag_error = 1;
						$var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
					}
					/*if($_FILES['vImage']['size'] > 1048576){
						$flag_error = 1;
						$var_msg = "Image Size is too Large";
					}*/
					if($flag_error == 1){

					//echo $tconfig['tsite_url'];
						$generalobj->getPostForm($_POST,$var_msg,"banner_action.php?success=0&var_msg=".$var_msg);
						exit;
						}else{
						$Photo_Gallery_folder = $tconfig["tsite_upload_images_panel"].'/';
						if(!is_dir($Photo_Gallery_folder)){
							mkdir($Photo_Gallery_folder, 0777);
						}
						$img = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg');
						$vImage = $img[0];
					}
				}
				//$vTitle = 'vTitle_'.$db_master[$i]['vCode'];

				$query = $q ." `".$tbl_name."` SET
					`vTitle` = '".$vTitle."',
					`vImage` = '".$vImage."',
					`eStatus` = '".$eStatus."',
					`iUniqueId` = '".$iUniqueId."',
					`iDisplayOrder` = '".$iDisplayOrder."',
					`vCode` = '".$db_master[$i]['vCode']."'"
				.$where;
				$obj->sql_query($query);
			}
		}
		//header("Location:banner_action.php?id=".$iUniqueId.'&success=1');
	}

	// for Edit
	if($action == 'Edit') {
		$sql = "SELECT * FROM ".$tbl_name." WHERE iUniqueId = '".$id."'";
		$db_data = $obj->MySQLSelect($sql);
		//echo '<pre>'; print_R($db_data); echo '</pre>';
		$iUniqueId = $id;
		if(count($db_data) > 0) {
			foreach($db_data as $key => $value) {
				//$vTitle 			= 'vTitle_'.$value['vCode'];
				$vTitle 			= $value['vTitle'];
				$eStatus 			= $value['eStatus'];
				$vImage 			= $value['vImage'];
				$iDisplayOrder 		= $value['iDisplayOrder'];
			}
		}
	}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>Admin | Banner <?php echo $action;?></title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php include_once('global_files.php');?>
		<!-- On OFF switch -->
		<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
		<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
	</head>
	<!-- END  HEAD-->
	<!-- BEGIN BODY-->
	<body class="padTop53 " >

		<!-- MAIN WRAPPER -->
		<div id="wrap">
			<?php include_once('header.php'); ?>
			<?php include_once('left_menu.php'); ?>
			<!--PAGE CONTENT -->
			<div id="content">
				<div class="inner">
					<div class="row">
						<div class="col-lg-12">
							<h2><?php echo $action;?> بنر</h2>
							<a href="banner.php">
								<input type="button" value="بازگشت" class="add-btn">
							</a>
						</div>
					</div>
					<hr />
					<div class="body-div">
						<div class="form-group">
							<?php if($success !=Null) { ?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									بنر با موفقیت ویرایش شد
								</div><br/>
							<?php } ?>
							<form method="post" action="" enctype="multipart/form-data">
								<input type="hidden" name="id" value="<?php echo $id;?>"/>
								<div class="row">
									<div class="col-lg-12">
										<label>تصویر<?php echo ($vImage == '')?'<span class="red"> *</span>':'';?></label>
									</div>
									<div class="col-lg-6">
										<?php if($vImage != '') { ?>
											<img src="<?php echo $tconfig['tsite_upload_images'].$vImage;?>" style="width:200px;height:100px;">
											<input type="file" name="vImage" id="vImage" value="<?php echo $vImage;?>"/>
										<?php } else { ?>
											<input type="file" name="vImage" id="vImage" value="<?php echo $vImage;?>" required/>
										<?php } ?>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>عنوان</label>
									</div>
									<div class="col-lg-6">
										<input type="text" name="vTitle" id="vTitle" value="<?php echo $vTitle?>" class="form-control" />
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>وضعیت</label>
									</div>
									<div class="col-lg-6">
										<div class="make-switch" data-on="success" data-off="warning">
											<input type="checkbox" name="eStatus" <?php echo ($id != '' && $eStatus == 'Inactive')?'':'checked';?>/>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>سفارش</label>
									</div>
									<div class="col-lg-6">
										<?
											$temp = 1;
											$query1 = mysqli_query($condbc,"SELECT iDisplayOrder FROM ".$tbl_name." WHERE vCode = '".$default_lang."' ORDER BY iDisplayOrder")or die(mysqli_error($condbc));
											$dataArray = array();


											while($res = mysql_fetch_array($query1))
											{
												$dataArray[] = $res['iDisplayOrder'];
												$temp = $iDisplayOrder;
											}
										?>
										<input type="hidden" name="temp_order" id="temp_order" value="<?php echo $temp?>">
										<select name="iDisplayOrder" class="form-control">
											<?php foreach($dataArray as $arr):?>
											<option <?php echo  $arr == $temp ? ' selected="selected"' : '' ?> value="<?php echo $arr;?>" >
												-- <?php echo  $arr ?> --
											</option>
											<?php endforeach; ?>
											<?php if($action=="Add") {?>
												<option value="<?php echo $temp;?>" >
													-- <?php echo  $temp ?> --
												</option>
											<?php }?>
										</select>
									</div>
								</div>

								<div class="row">
									<div class="col-lg-12">
										<input type="submit" class="save btn-info" name="submit" id="submit" value="<?php echo $action;?> Banner">
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->


		<?php include_once('footer.php');?>
		<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
	</body>
	<!-- END BODY-->
</html>
