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

	$hdn_del_id = isset($_POST['hdn_del_id'])?$_POST['hdn_del_id']:'';
	$iModelId 	= isset($_GET['iModelId'])?$_GET['iModelId']:'';
	$status 	= isset($_GET['status'])?$_GET['status']:'';
	$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
	$tbl_name 	= 'model';
	$script 	= 'Settings';

	if($hdn_del_id != ''){
				if(SITE_TYPE !='Demo'){
		$query = "DELETE FROM `".$tbl_name."` WHERE iModelId = '".$hdn_del_id."'";
		$obj->sql_query($query);
	}
	else{
		header("Location:model.php?success=2");exit;
	}
	}
	if($iModelId != '' && $status != ''){
		  if(SITE_TYPE !='Demo'){
		$query = "UPDATE `".$tbl_name."` SET eStatus = '".$status."' WHERE iModelId = '".$iModelId."'";
		$obj->sql_query($query);
	}
	else{
		header("Location:model.php?success=2");exit;
	}
	}
	$sql = "SELECT model . * , make.vMake FROM model LEFT JOIN make ON model.iMakeId = make.iMakeId";
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
		<title>مدل ماشین | ادمین</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<?php include_once('global_files.php');?>

		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
		<script type="text/javascript">
			function confirm_delete()
			{
				var confirm_ans = confirm("Are You sure You want to Delete Model?");
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
							<h2>مدل ماشین</h2>
							<a href="model_action.php">
								<input type="button" value="افزودن مدل ماشین" class="add-btn">
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
									<div class="panel-heading">
										مدل ماشین
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>مدل ماشین</th>
														<th>اتومبیل</th>
														<th>وضعیت</th>
														<th>فعالیت</th>
													</tr>
												</thead>
												<tbody>
													<?
														$count_all = count($db_data);
														if($count_all > 0) {
															for($i=0;$i<$count_all;$i++) {
																$id = $db_data[$i]['iModelId'];
																$vMake = $db_data[$i]['vMake'];
																$vTitle = $db_data[$i]['vTitle'];
																$eStatus = $db_data[$i]['eStatus'];
																$checked = ($eStatus=="Active")?'checked':'';
															?>
															<tr class="gradeA">
																<td width="35%"><?php echo $vTitle;?></td>
																<td width="35%"><?php echo $vMake;?></td>
																<td width="10%" class="center">
																	<?php if($eStatus == 'Active') {
																		   $dis_img = "img/active-icon.png";
																			}else if($eStatus == 'Inactive'){
																			 $dis_img = "img/inactive-icon.png";
																				}else if($eStatus == 'Deleted'){
																				$dis_img = "img/delete-icon.png";
																				}?>
																		<img src="<?php echo $dis_img;?>" alt="<?php echo $eStatus;?>">
																</td>
																<td width="20%" class="veh_act">
																	<a href="model_action.php?id=<?php echo $id;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Edit Car Model">
																			<img src="img/edit-icon.png" alt="Edit">
																		</button>
																	</a>

																	<a href="model.php?iModelId=<?php echo $id;?>&status=Active" data-toggle="tooltip" title="Active Model">
																		<img src="img/active-icon.png" alt="<?php echo $eStatus ?>" >
																	</a>
																	<a href="model.php?iModelId=<?php echo  $id; ?>&status=Inactive" data-toggle="tooltip" title="Inactive Model">
																		<img src="img/inactive-icon.png" alt="<?php echo $eStatus; ?>" >
																	</a>

																	<!-- <a href="languages.php?id=<?php echo $id;?>&action=delete"><i class="icon-trash"></i> Delete</a>-->
																	<form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm_delete()" class="margin0">
																		<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $id;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Delete Car Model">
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
