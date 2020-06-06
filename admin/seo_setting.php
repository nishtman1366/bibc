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

	$tbl_name 		= 'seo_sections';
	$script			= "Settings";


	$sql = "SELECT * FROM ".$tbl_name." ORDER BY iId DESC";
	$db_data = $obj->MySQLSelect($sql);


?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>تنظیمات سئو | ادمین</title>
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
					<!---<div class="row">
						<div class="col-lg-12">
							<h2>Seo Setting Page</h2>
							<a href="appscreenshot_action.php">
								<input type="button" value="Add Screenshort" class="add-btn">
							</a>
						</div>
					</div>
					<hr />-->
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										تنظیمات سئو
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>نام صفحه</th>
														<th>ویرایش</th>														
													</tr>
												</thead>
												<tbody>
													<?

													if(!empty($db_data)){

														foreach($db_data as $value){

															$iId = $value['iId'];
															$vPagename = $value['vPagename'];

															?>
															<tr class="gradeA">
																<td width="25%" ><?php echo $vPagename;?></td>
																<td width="10%" align="center">
																	<a href="seosetting_action.php?id=<?php echo $iId;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Edit SEO Setting">
																			<img src="img/edit-icon.png" alt="Edit">
																		</button>
																	</a>
																</td>

															</tr>
														<?php }
													} else { ?>
														<!--<tr class="gradeA">
															<td colspan="4">No Records found.</td>
														</tr>-->
												<?php } ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div> <!--TABLE-END-->
						</div>
					</div>
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
