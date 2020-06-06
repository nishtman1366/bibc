<?
	include_once('../common.php');

	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	require_once(TPATH_CLASS."Imagecrop.class.php");

	$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:'';
	$success	= isset($_REQUEST['success'])?$_REQUEST['success']:0;
	$action 	= ($id != '')?'Edit':'Add';

	$tbl_name 	= 'pages';
	$script 	= 'Page';

	//echo '<prE>'; print_R($_REQUEST); echo '</pre>';

	// fetch all lang from language_master table
	$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
	$db_master = $obj->MySQLSelect($sql);
	$count_all = count($db_master);
	//echo '<pre>'; print_R($db_master); echo '</pre>';

	// set all variables with either post (when submit) either blank (when insert)
	$iPageId = isset($_POST['iPageId'])?$_POST['iPageId']:$id;
	$vPageName = isset($_REQUEST['vPageName'])?$_REQUEST['vPageName']:'';
	$vTitle = isset($_REQUEST['vTitle'])?$_REQUEST['vTitle']:'';
	$tMetaKeyword = isset($_REQUEST['tMetaKeyword'])?$_REQUEST['tMetaKeyword']:'';
	$tMetaDescription = isset($_REQUEST['tMetaDescription'])?$_REQUEST['tMetaDescription']:'';
	$vImage 		= isset($_POST['vImage'])?$_POST['vImage']:'';
	$vImage1 		= isset($_POST['vImage1'])?$_POST['vImage1']:'';
	$thumb = new thumbnail();
	if($count_all > 0) {
		for($i=0;$i<$count_all;$i++) {
			$vPageTitle = 'vPageTitle_'.$db_master[$i]['vCode'];
			$$vPageTitle  = isset($_POST[$vPageTitle])?$_POST[$vPageTitle]:'';
			$tPageDesc = 'tPageDesc_'.$db_master[$i]['vCode'];
			$$tPageDesc  = isset($_POST[$tPageDesc])?$_POST[$tPageDesc]:'';
		}
	}

	if(isset($_POST['submit'])) {

		if(SITE_TYPE=="Demo"){
			header("Location:page_action.php?id=".$iPageId.'&success=2');
			exit;
		}

		if(count($db_master) > 0) {
			$str = '';
			for($i=0;$i<count($db_master);$i++) {
				$vPageTitle = 'vPageTitle_'.$db_master[$i]['vCode'];

				$$vPageTitle = $_REQUEST[$vPageTitle];

				$tPageDesc = 'tPageDesc_'.$db_master[$i]['vCode'];
				$$tPageDesc = $_REQUEST[$tPageDesc];

				$str .= " ".$vPageTitle." = '".$$vPageTitle."', ".$tPageDesc." = '".$$tPageDesc."', ";

			}

		}


		$image_object = $_FILES['vImage']['tmp_name'];
		$image_name   = $_FILES['vImage']['name'];
		//echo "<pre>";print_r( $_FILES);print_r($_POST);echo "</pre>";exit;
		if($image_name != ""){
			$filecheck = basename($_FILES['vImage']['name']);
			$fileextarr = explode(".",$filecheck);
			$ext=strtolower($fileextarr[count($fileextarr)-1]);
			$flag_error = 0;
			if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
				$flag_error = 1;
				$var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
			}
			if($_FILES['vImage']['size'] > 1048576){
				$flag_error = 1;
				$var_msg = "Image Size is too Large";
			}
			if($flag_error == 1){
				$generalobj->getPostForm($_POST,$var_msg,$tconfig['tsite_url']."page_action&success=0");
				exit;
				}else{
				$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"].'/';
				if(!is_dir($Photo_Gallery_folder)){
                   	mkdir($Photo_Gallery_folder, 0777);
				}


			  $img = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg');
				//echo "<pre>";print_r($img);exit;
				 $vImage = $img[0];
			}
		}

		$image_object1 = $_FILES['vImage1']['tmp_name'];
		$image_name1   = $_FILES['vImage1']['name'];
	//echo "<pre>";print_r( $_FILES);echo "</pre>";exit;
		if($image_name1 != ""){
			$filecheck1 = basename($_FILES['vImage1']['name']);
			$fileextarr1 = explode(".",$filecheck1);
			$ext1=strtolower($fileextarr1[count($fileextarr1)-1]);
			$flag_error1 = 0;
			if($ext1 != "jpg" && $ext1 != "gif" && $ext1 != "png" && $ext1 != "jpeg" && $ext1 != "bmp"){
				$flag_error1 = 1;
				$var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
			}
			if($_FILES['vImage1']['size'] > 1048576){
				$flag_error1 = 1;
				$var_msg = "Image Size is too Large";
			}
			if($flag_error1 == 1){
				$generalobj->getPostForm($_POST,$var_msg,$tconfig['tsite_url']."page_action&success=0");
				exit;
				}else{
				$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"].'/';
				if(!is_dir($Photo_Gallery_folder)){
                   	mkdir($Photo_Gallery_folder, 0777);
				}
				$img1 = $generalobj->fileupload($Photo_Gallery_folder,$image_object1,$image_name1, '','jpg,png,gif,jpeg');
				//echo "<pre>";print_r($img);exit;
				$vImage1 = $img1[0];
			}
		}

		$q = "INSERT INTO ";
		$where = '';

		if($id != '' ){
			$q = "UPDATE ";
			$where = " WHERE `iPageId` = '".$iPageId."'";
		}


 	$query = $q ." `".$tbl_name."` SET ".$str."
		`vPageName` = '".$vPageName."',
		`vTitle` = '".$vTitle."',
		`tMetaKeyword` = '".$tMetaKeyword."',
		`tMetaDescription` = '".$tMetaDescription."'";
		if($image_name!=''){
			$query.= ", vImage = '".$vImage."'";
		}
		if($image_name1!=''){
			$query.= ", vImage1 = '".$vImage1."'";
		}
		$query.=$where;
		$Id = $obj->sql_query($query);
		//$s = mysql_insert_id();
		if($action == 'Add')
		{
			$iPageId =  mysql_insert_id();
		}

		header("Location:page_action.php?id=".$iPageId.'&success=1');

	}

	// for Edit
	if($action == 'Edit') {
		$sql = "SELECT * FROM ".$tbl_name." WHERE iPageId = '".$id."'";
		$db_data = $obj->MySQLSelect($sql);
		//echo '<pre>'; print_R($db_data); echo '</pre>'; exit;
		$vLabel = $id;


		if(count($db_data) > 0) {
			for($i=0;$i<count($db_master);$i++)
			{
				foreach($db_data as $key => $value) {
					$vPageTitle = 'vPageTitle_'.$db_master[$i]['vCode'];
					$$vPageTitle = $value[$vPageTitle];
					$tPageDesc = 'tPageDesc_'.$db_master[$i]['vCode'];
					$$tPageDesc = $value[$tPageDesc];
					$vPageName = $value['vPageName'];
					$vTitle = $value['vTitle'];
					$tMetaKeyword = $value['tMetaKeyword'];
					$tMetaDescription = $value['tMetaDescription'];
					$vImage = $value['vImage'];
					$vImage1 = $value['vImage1'];
				}
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
		<title>Admin | Static Page <?php echo $action;?></title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php include_once('global_files.php');?>
		<!-- PAGE LEVEL STYLES -->
		<link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
		<link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css" />
		<link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css" />
		<link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css" />
		<link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css" />
		<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
		<style>
			ul.wysihtml5-toolbar > li {
			position: relative;
			}
		</style>
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
							<h2><?php echo $action;?> Static Page</h2>
							<a href="page.php">
								<input type="button" value="Back to Listing" class="add-btn">
							</a>
						</div>
					</div>
					<hr />
					<div class="body-div">
						<div class="form-group">
							<?php if($success == 1) { ?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									Record Updated successfully.
								</div><br/>
							<?php }elseif($success == 2){ ?>
								<div class="alert alert-danger alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you. 
								</div><br/>
							<?php } ?>
							<form method="post" action=""  enctype="multipart/form-data">
								<input type="hidden" name="id" value="<?php echo $id;?>"/>
								<div class="row">
									<div class="col-lg-12">
										<label>Page/Section</label>
									</div>
									<div class="col-lg-6">
										<input type="text" class="form-control" name="vPageName"  id="vPageName" value="<?php echo $vPageName;?>" placeholder="Page Name">
									</div>
								</div>
								<?
									$style_v = "";
									if(in_array($iPageId,array('29','30'))){
										$style_v = "style = 'display:none;'";
									}
									if($count_all > 0) {
										for($i=0;$i<$count_all;$i++) {
											$vCode = $db_master[$i]['vCode'];
											$vLTitle = $db_master[$i]['vTitle'];
											$eDefault = $db_master[$i]['eDefault'];

											$vPageTitle = 'vPageTitle_'.$vCode;
											$tPageDesc = 'tPageDesc_'.$vCode;

											$required = ($eDefault == 'Yes')?'required':'';
											$required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
										?>
										<div class="row">
											<div class="col-lg-12">
												<label>Page Title (<?php echo $vLTitle;?>) <?php echo $required_msg;?></label>
											</div>
											<div class="col-lg-6">
												<input type="text" class="form-control" name="<?php echo $vPageTitle;?>"  id="<?php echo $vPageTitle;?>" value="<?php echo $$vPageTitle;?>" placeholder="<?php echo $vPageTitle;?> Value" <?php echo $required;?>>
											</div>
										</div>

										<!--- Editor ---->
										<div class="row" <?php echo $style_v?>>
											<div class="col-lg-12">
												<label> Page Description (<?php echo $vLTitle;?>) <?php echo $required_msg;?></label>
											</div>
											<div class="col-lg-12">
												<textarea class="form-control ckeditor" rows="10" name="<?php echo $tPageDesc;?>"  id="<?php echo $tPageDesc;?>"  placeholder="<?php echo $tPageDesc;?> Value" <?php echo $required;?>> <?php echo $$tPageDesc;?></textarea>
											</div>
										</div>

										<!--- Editor ---->
										<?php }
									} ?>
									
									
<?php
									if(!in_array($iPageId,array('23','24','25','26','27'))){
								?>
								
									<div class="row" <?php echo $style_v?>>
										<div class="col-lg-12">
											<label>Meta Title</label>
										</div>
										<div class="col-lg-6">
											<input type="text" class="form-control" name="vTitle"  id="vTitle" value="<?php echo $vTitle;?>" placeholder="Meta Title">
										</div>
									</div>
									<div class="row" <?php echo $style_v?>>
										<div class="col-lg-12">
											<label>Meta Keyword</label>
										</div>
										<div class="col-lg-6">
											<input type="text" class="form-control" name="tMetaKeyword"  id="tMetaKeyword" value="<?php echo $tMetaKeyword;?>" placeholder="Meta Keyword">
										</div>
									</div>

									<div class="row" <?php echo $style_v?>>
										<div class="col-lg-12">
											<label>Meta Description</label>
										</div>
										<div class="col-lg-6">
											<textarea class="form-control" rows="10" name="tMetaDescription"  id="<?php echo $tMetaDescription;?>"  placeholder="<?php echo $tMetaDescription;?> Value" <?php echo $required;?>> <?php echo $tMetaDescription;?></textarea>
										</div>
									</div>

								<?php
									}	if(!in_array($iPageId,array('1','2','7','4','3','6','23','27'))){
								?>
									<?php
										if(in_array($iPageId,array())){
									?>
									<div class="row">
										<div class="col-lg-12">
											<label>Left Image</label>
										</div>
										<div class="col-lg-6">
											<?php if($vImage != '') { ?>
												<a target="_blank" href="<?php echo $tconfig['tsite_upload_page_images'].$vImage;?>"><img src="<?php echo $tconfig['tsite_upload_page_images'].$vImage;?>" style="width:200px;height:100px;"></a>
											<?php } ?>
											<input type="file" name="vImage" id="vImage" />
										</div>
									</div>
									<div class="row">
										<div class="col-lg-12">
											<label>Right Image</label>
										</div>
										<div class="col-lg-6">
											<?php if($vImage1 != '') { ?>
												<a target="_blank" href="<?php echo $tconfig['tsite_upload_page_images'].$vImage1;?>"><img src="<?php echo $tconfig['tsite_upload_page_images'].$vImage1;?>" style="width:200px;height:100px;"></a>
											<?php } ?>
											<input type="file" name="vImage1" id="vImage1" />
										</div>
									</div>
									<?php
										}else {
									?>
									<div class="row">
										<div class="col-lg-12">
											<label>Image</label>
										</div>
										<div class="col-lg-6">
											<?php if($vImage != '') { ?>
												<a target="_blank" href="<?php echo $tconfig['tsite_upload_page_images'].$vImage;?>"><img src="<?php echo $tconfig['tsite_upload_page_images'].$vImage;?>" style="width:200px;height:100px;"></a>
											<?php } ?>
											<input type="file" name="vImage" id="vImage" />
										</div>
									</div>
									<?php
										}
									?>
								<?php
									}
								?>

									<div class="row">
										<div class="col-lg-12">
											<input type="submit" class="save btn-info" name="submit" id="submit" value="<?php echo $action;?> Static Page">
										</div>
									</div>
							</form>
						</div>
					</div>
                    <div class="clear"></div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->


		<?php include_once('footer.php');?>


		<!-- GLOBAL SCRIPTS -->
		<script src="../assets/plugins/jquery-2.0.3.min.js"></script>
		<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
		<!-- END GLOBAL SCRIPTS -->

		<!-- PAGE LEVEL SCRIPTS -->
		<script src="../assets/plugins/wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
		<script src="../assets/plugins/bootstrap-wysihtml5-hack.js"></script>
		<script src="../assets/plugins/CLEditor1_4_3/jquery.cleditor.min.js"></script>
		<script src="../assets/plugins/pagedown/Markdown.Converter.js"></script>
		<script src="../assets/plugins/pagedown/Markdown.Sanitizer.js"></script>
		<script src="../assets/plugins/Markdown.Editor-hack.js"></script>
		<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
		<script src="../assets/plugins/ckeditor/config.js"></script>
		<script src="../assets/js/editorInit.js"></script>
		<script>
			$(function () { formWysiwyg(); });
			CKEDITOR.replace( 'ckeditor',{
				allowedContent : {
					i:{
						classes:'fa*'
					},
					span: true
				}
			} );
		</script>

	</body>
	<!-- END BODY-->
</html>
