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

	//$hdn_del_id 	= isset($_POST['hdn_del_id'])?$_POST['hdn_del_id']:'';
	$tbl_name 		= 'email_templates';
	$script 		= 'Settings';

	/* if($hdn_del_id != ''){
		$query = "DELETE FROM `".$tbl_name."` WHERE iEmailId = '".$hdn_del_id."'";
		$obj->sql_query($query);
	} */

	$sql = "SELECT * FROM ".$tbl_name." WHERE eStatus = 'Active' ORDER BY iEmailId DESC";
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
		<title>قالب ایمیل | ادمین</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php include_once('global_files.php');?>
		<!-- <script type="text/javascript">
			/* function confirm_delete()
			{
				var confirm_ans = confirm("Are You sure You want to Delete this Page?");
				return confirm_ans;
				//document.getElementById(id).submit();
			} */
		</script> -->
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
							<h2>قالب ایمیل</h2>
							<!-- <a href="email_template_action.php">
								<input type="button" value="Add Email Template" class="add-btn">
							</a> -->
						</div>
					</div>
					<hr />
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										قالب ایمیل
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<!--<th>Section</th>-->
														<th>موضوع ایمیل</th>
														<th>ویرایش</th>
														<!-- <th>Delete</th> -->
													</tr>
												</thead>
												<tbody>
													<?
														$count_all = count($db_data);
														if($count_all > 0) {
															for($i=0;$i<$count_all;$i++) {
																$iEmailId = $db_data[$i]['iEmailId'];
																$vSection = $db_data[$i]['vSection'];
																$vSubject = $db_data[$i]['vSubject_'.$default_lang];
																$eStatus = $db_data[$i]['eStatus'];
															?>
															<tr class="gradeA">
																<!--<td><?php echo $vSection;?></td>-->
																<td><?php echo $vSubject;?></td>
																<td width="10%" align="center">
																	<a href="email_template_action.php?id=<?php echo $iEmailId;?>" data-toggle="tooltip" title="Edit Email Template">
																		<button class="remove_btn001">
																			<img src="img/edit-icon.png" alt="Edit">
																		</button>
																	</a>
																</td>
																<!-- <td width="10%" align="center">
																	<form name="delete_form" id="delete_form" method="post" action="" onsubmit="return confirm_delete()" class="margin0">
																		<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $iEmailId;?>">
																		<button class="btn btn-danger">
																			<i class="icon-remove icon-white"></i> Delete
																		</button>
																	</form>
																</td> -->
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
