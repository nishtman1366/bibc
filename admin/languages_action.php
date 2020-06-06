<?
	include_once('../common.php');

	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:'';
	$pageid 		= isset($_REQUEST['lp_id'])?$_REQUEST['lp_id']:'';
	$lp_name 		= isset($_REQUEST['lp_name'])?$_REQUEST['lp_name']:'';
	$success	= isset($_REQUEST['success'])?$_REQUEST['success']:0;
	$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
	$action 	= ($id != '')?'Edit':'Add';

	$tbl_name 	= 'language_label';

	//echo '<prE>'; print_R($_REQUEST); echo '</pre>';

	// fetch all lang from language_master table
	$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
	$db_master = $obj->MySQLSelect($sql);
	$count_all = count($db_master);
	//echo '<pre>'; print_R($db_master); echo '</pre>';

	// set all variables with either post (when submit) either blank (when insert)
	$vLabel = isset($_POST['vLabel'])?$_POST['vLabel']:$id;
	if($count_all > 0) {
		for($i=0;$i<$count_all;$i++) {
			$vValue = 'vValue_'.$db_master[$i]['vCode'];
			$$vValue  = isset($_POST[$vValue])?$_POST[$vValue]:'';
		}
	}

	if(isset($_POST['submit'])) {

		if($id == ''){
		   $sql = "SELECT * FROM `language_label` WHERE vLabel = '".$vLabel."'";
	       $db_label_check = $obj->MySQLSelect($sql);
	       if(count($db_label_check) > 0){
	       	   $var_msg = "Language Label Already Exists In General Label";
	       	   header("Location:languages_action.php?var_msg=".$var_msg.'&success=0');
	       	   exit;
	       }

	       $sql = "SELECT * FROM `language_label_other` WHERE vLabel = '".$vLabel."'";
	       $db_label_check_ride = $obj->MySQLSelect($sql);
	       if(count($db_label_check_ride) > 0){
	       	   $var_msg = "Language Label Already Exists In Ride Label";
	       	   header("Location:languages_action.php?var_msg=".$var_msg.'&success=0');
	       	   exit;
	       }
		}

		if(SITE_TYPE=='Demo')
		{
				header("Location:languages_action.php?id=".$vLabel.'&success=2');
				exit;
		}

		if($count_all > 0) {
			for($i=0;$i<$count_all;$i++) {

				$q = "INSERT INTO ";
				$where = '';

				if($id != '' ){
					$q = "UPDATE ";
					$where = " WHERE `vLabel` = '".$vLabel."' AND vCode = '".$db_master[$i]['vCode']."'";
				}

				$vValue = 'vValue_'.$db_master[$i]['vCode'];

				$query = $q ." `".$tbl_name."` SET
				`lPage_id` = '".$pageid."',
				`vLabel` = '".$vLabel."',
				`vCode` = '".$db_master[$i]['vCode']."',
				`vValue` = '".$$vValue."'"
				.$where;

				$obj->sql_query($query);
			}
		}

		header("Location:languages_action.php?id=".$vLabel.'&success=1&lp_id='.$pageid.'&lp_name='.$lp_name);

	}

	// for Edit
	if($action == 'Edit') {
		$sql = "SELECT * FROM ".$tbl_name." WHERE vLabel = '".$id."'";
		$db_data = $obj->MySQLSelect($sql);
		//echo '<pre>'; print_R($db_data); echo '</pre>'; exit;
		$vLabel = $id;
		if(count($db_data) > 0) {
			foreach($db_data as $key => $value) {
				$vValue = 'vValue_'.$value['vCode'];
				$$vValue = $value['vValue'];
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
		<title>Admin | Language <?php echo $action;?></title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php include_once('global_files.php');?>
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
							<h2><?php echo $action;?> برچسب زبان</h2>
							<a href="languages.php?lp_id=<?php echo  $pageid; ?>&lp_name=<?php echo  $lp_name; ?>">
								<input type="button" value="بازگشت" class="add-btn">
							</a>
						</div>
					</div>
					<hr />
					<div class="body-div">
						<div class="form-group">
							<?php if($success == 1) { ?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									برچسب زبان با موفقیت ویرایش شد
								</div><br/>
								<?php }elseif ($success == 2) { ?>
									<div class="alert alert-danger alert-dismissable">
											 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
											 "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
									</div><br/>
								<?php }elseif ($success == 0 && $var_msg !='') { ?>
									<div class="alert alert-danger alert-dismissable">
											 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
											 <?php echo $var_msg;?>
									</div><br/>
								<?php }?>
							<form method="post" action="">
								<input type="hidden" name="id" value="<?php echo $id;?>"/>
								<div class="row">
									<div class="col-lg-12">
										<label>برچسب زبان<?php echo ($id != '')?'':'<span class="red"> *</span>';?></label>
									</div>
									<div class="col-lg-6">
										<input type="text" class="form-control" name="vLabel"  id="vLabel" value="<?php echo $vLabel;?>" placeholder="Language Label" <?php echo ($id != '')?'disabled':'required';?>>
									</div>
								</div>
								<?
									if($count_all > 0) {
										for($i=0;$i<$count_all;$i++) {
											$vCode = $db_master[$i]['vCode'];
											$vTitle = $db_master[$i]['vTitle'];
											$eDefault = $db_master[$i]['eDefault'];

											$vValue = 'vValue_'.$vCode;

											$required = ($eDefault == 'Yes')?'required':'';
											$required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
										?>
										<div class="row">
											<div class="col-lg-12">
												<label><?php echo $vTitle;?> مقدار <span class="red"> *</span></label>
											</div>
											<div class="col-lg-6">
												<input type="text" class="form-control" name="<?php echo $vValue;?>" id="<?php echo $vValue;?>" value="<?php echo $$vValue;?>" placeholder="<?php echo $vTitle;?> Value" <?php echo $required;?>>
											</div>
										</div>
										<?php }
									} ?>
									<div class="row">
										<div class="col-lg-12">
											<input type="submit" class="save btn-info" name="submit" id="submit" value="<?php echo $action;?> Label">
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
	</body>
	<!-- END BODY-->
</html>
