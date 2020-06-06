<?
	include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('SETINGS') == false)
		die('you dont`t have permission...');


	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	$default_lang 	= $generalobj->get_default_lang();
	$hdn_del_id 	= isset($_POST['hdn_del_id'])?$_POST['hdn_del_id']:'';
	$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
	$pageid = isset($_REQUEST['lp_id']) ? $_REQUEST['lp_id'] : 0;
	$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : 0;
	$tbl_name 		= 'language_label';
	$script			= "Settings";

	if($hdn_del_id != ''){
			if(SITE_TYPE !='Demo'){
		$query = "DELETE FROM `".$tbl_name."` WHERE vLabel = '".$hdn_del_id."'";
		$obj->sql_query($query);
	}
	else{
		header("Location:languages.php?success=2");exit;
	}
	}
#echo $default_lang;exit;
	#$sql = "SELECT * FROM ".$tbl_name." WHERE vCode = '".$default_lang."' ORDER BY LanguageLabelId DESC";
	#$db_data = $obj->MySQLSelect($sql);
	$pageid=$_REQUEST['lp_id'];
	$sql = "SELECT * FROM ".$tbl_name." WHERE vCode = '".$default_lang."' ORDER BY LanguageLabelId DESC";
	$db_data = $obj->MySQLSelect($sql);
	//echo '<pre>'; print_R($db_data); echo '</pre>';
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>زبان | ادمین</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="" name="keywords" />
		<meta content="" name="description" />
		<meta content="" name="author" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php include_once('global_files.php');?>
		<script type="text/javascript">
			function confirm_delete()
			{
				var confirm_ans = confirm("Are You sure You want to Delete Language Label?");
				return confirm_ans;
				//document.getElementById(id).submit();
			}
		</script>
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
							<a href="development_languages.php?type=web">
								<input type="button" value="Back" class="add-btn">
							</a>
						</div>
						<div class="col-lg-12">
							<h2>برچسب ها</h2>
							<a href="languages_action.php?lp_id=<?php echo $pageid;?>&lp_name=<?php echo $lp_name;?>">
								<input type="button" value="افزودن برچسب" class="add-btn">
							</a>
						</div>

					</div>
					<hr />
					<?php if ($success == 2) { ?>
							<div class="alert alert-danger alert-dismissable">
									 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									 "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
							</div><br/>
						<?php } ?>
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading driver-neww1 driver-neww2">
									<b>برچسب های زبان</b>
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>کد</th>
														<th>مقدار در زبان فارسی / انگلیسی</th>
														<th align="center" style=" text-align:center;">Action</th>
													</tr>
												</thead>
												<tbody>
													<?
														$count_all = count($db_data);
														if($count_all > 0) {
															for($i=0;$i<$count_all;$i++) {
																$LanguageLabelId = $db_data[$i]['LanguageLabelId'];
																$vLabel = $db_data[$i]['vLabel'];
																$vValue = $db_data[$i]['vValue'];
															?>
															<tr class="gradeA">
																<td width="40%"><?php echo $vLabel;?></td>
																<td width="40%"><?php echo $vValue;?></td>
																<td class="center veh_act" align="center" style="text-align:center;">
																	<a href="languages_action.php?id=<?php echo $vLabel;?>&lp_id=<?php echo $pageid;?>&lp_name=<?php echo $lp_name;?>">
																	<button class="remove_btn001" data-toggle="tooltip" title="Edit Language Label">
																	<img src="img/edit-icon.png" alt="Edit">
																	</button>
																	</a>
																	<!-- <a href="languages.php?id=<?php echo $vLabel;?>&action=delete"><i class="icon-trash"></i> Delete</a>-->
																<form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm_delete()" class="margin0">
																		<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $vLabel;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Delete Language Label">
																			<img src="img/delete-icon.png" alt="Delete">
																		</button>
																	</form>
																</td>
															</tr>
															<?php }
														} else { ?>
														<tr class="gradeA">
															<td colspan="4">داده ای یافت نشد</td>
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
                    <div class="clear"></div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->


		<?php include_once('footer.php');?>
		<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script>
			$(document).ready(function () {
				$('#dataTables-example').dataTable();
			});
		</script>
	</body>
	<!-- END BODY-->
</html>
