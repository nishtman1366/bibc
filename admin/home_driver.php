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
	//Delete
	$hdn_del_id 	= isset($_POST['hdn_del_id'])?$_POST['hdn_del_id']:'';
	// Update eStatus
	$iDriverId 		= isset($_GET['iDriverId'])?$_GET['iDriverId']:'';
	$status 		= isset($_GET['status'])?$_GET['status']:'';
	//sort order
	$flag 			= isset($_GET['flag'])?$_GET['flag']:'';
	$id 			= isset($_GET['id'])?$_GET['id']:'';

	$tbl_name 		= 'home_driver';
	$script 		= 'Settings';

	//delete record
	if($hdn_del_id != ''){
		if(SITE_TYPE =='Demo'){
			header("Location:home_driver.php?success=2");exit;
		}
		$data_q 	= "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM `".$tbl_name."`";
		$data_rec  	= $obj->MySQLSelect($data_q);
		//echo '<pre>'; print_r($data_rec); echo '</pre>';
		$order = isset($data_rec[0]['iDisplayOrder'])?$data_rec[0]['iDisplayOrder']:0;

		$data_logo =  $obj->MySQLSelect("SELECT iDisplayOrder FROM ".$tbl_name." WHERE iDriverId = '".$hdn_del_id."'");

		if(count($data_logo) > 0)
		{
			$iDisplayOrder =  isset($data_logo[0]['iDisplayOrder'])?$data_logo[0]['iDisplayOrder']:'';
			$obj->sql_query("DELETE FROM `".$tbl_name."` WHERE iDriverId = '".$hdn_del_id."'");

			if($iDisplayOrder < $order)
			for($i = $iDisplayOrder+1; $i <= $order; $i++)
			$obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i-1)." WHERE iDisplayOrder = ".$i);
		}
	}

	if($id != 0) {
		if($flag == 'up')
		{
			$sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM ".$tbl_name." WHERE iDriverId ='".$id."'");
			$order_data = isset($sel_order[0]['iDisplayOrder'])?$sel_order[0]['iDisplayOrder']:0;
			$val = $order_data - 1;
			if($val > 0) {
				$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$order_data."' WHERE iDisplayOrder='".$val."'");
				$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$val."' WHERE iDriverId = '".$id."'");
			}
		}

		else if($flag == 'down')
		{
			$sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM ".$tbl_name." WHERE iDriverId ='".$id."'");

			$order_data = isset($sel_order[0]['iDisplayOrder'])?$sel_order[0]['iDisplayOrder']:0;

			$val = $order_data+ 1;
			$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$order_data."' WHERE iDisplayOrder='".$val."'");
			$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$val."' WHERE iDriverId = '".$id."'");
		}
		header("Location:home_driver.php");
	}

	if($iDriverId != '' && $status != ''){
		$query = "UPDATE `".$tbl_name."` SET eStatus = '".$status."' WHERE iDriverId = '".$iDriverId."'";
		$obj->sql_query($query);

		$var_msg="Driver Information ".$status."ted Successfully.";
		header("Location:home_driver.php?success=1&var_msg=".$var_msg);
	}

	$sql = "SELECT * FROM ".$tbl_name." ORDER BY iDisplayOrder";
	$db_data = $obj->MySQLSelect($sql);
	//echo '<pre>'; print_R($db_data); echo '</pre>';	exit;

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>

		<meta charset="UTF-8" />
		<title>صفحه خانه رانندگان | ادمین </title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="" name="keywords" />
		<meta content="" name="description" />
		<meta content="" name="author" />
		<?php include_once('global_files.php');?>

		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
		<script type="text/javascript">
			function confirm_delete()
			{
				var confirm_ans = confirm("Are You sure You want to Delete Home Page Driver?");
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
							<h2>صفحه خانه رانندگان</h2>
							<a href="home_driver_action.php">
								<input type="button" value="افزودن راننده" class="add-btn">
							</a>
						</div>
					</div>
					<hr />
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<?php if ($_REQUEST['success'] == 2) {?>
									<div class="alert alert-danger alert-dismissable">
										<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
										"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
									</div><br/>
								<?} ?>
								<?php if ($_REQUEST['success'] == 1) {?>
									<div class="alert alert-success alert-dismissable">
										<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
										<?php echo $_REQUEST['var_msg'];?>
									</div><br/>
								<?} ?>
								<div class="panel panel-default">
									<div class="panel-heading">
										صفحه خانه رانندگان
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>تصویر</th>
														<th>نام</th>
														<th>تعیین</th>
														<th>سفارش</th>
														<th>وضعیت</th>
														<th>فعالیت</th>
													</tr>
												</thead>
												<tbody>
													<?
														$count_all = count($db_data);
														if($count_all > 0) {
															for($i=0;$i<$count_all;$i++) {
																$iDriverId 		= $db_data[$i]['iDriverId'];
																$vImage 		= $db_data[$i]['vImage'];
																$vName 			= $db_data[$i]['vName_EN'];
																$iDisplayOrder 	= $db_data[$i]['iDisplayOrder'];
																$eStatus 		= $db_data[$i]['eStatus'];
																$vDesignation 	= $db_data[$i]['vDesignation_EN'];
																$checked		= ($eStatus=="Active")?'checked':'';
															?>
															<tr class="gradeA">
																<td><?php echo $vImage;?></td>
																<td><?php echo $vName;?></td>
																<td><?php echo $vDesignation;?></td>
																<td width="10%" align="center">
																	<?php if($iDisplayOrder != 1) { ?>
																		<a href="home_driver.php?id=<?php echo $iDriverId;?>&flag=up">
																			<button class="btn btn-warning">
																				<i class="icon-arrow-up"></i>
																			</button>
																		</a>
																		<?php } if($iDisplayOrder != $count_all) { ?>
																		<a href="home_driver.php?id=<?php echo $iDriverId;?>&flag=down">
																			<button class="btn btn-warning">
																				<i class="icon-arrow-down"></i>
																			</button>
																		</a>
																	<?php } ?>

																</td>
																<td width="10%" align="center">
																	<?php if($eStatus == 'Active') {
																		   $dis_img = "img/active-icon.png";
																			}else if($eStatus == 'Inactive'){
																			 $dis_img = "img/inactive-icon.png";
																				}else if($eStatus == 'Deleted'){
																				$dis_img = "img/delete-icon.png";
																				}?>
																		<img src="<?php echo $dis_img;?>" alt="<?php echo $eStatus;?>">
																</td>
																<td width="15%" align="center" class="veh_act">
																	<a href="home_driver_action.php?id=<?php echo $iDriverId;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Edit Home Driver">
																			<img src="img/edit-icon.png" alt="Edit">
																		</button>
																	</a>

																	<a href="home_driver.php?iDriverId=<?php echo $iDriverId;?>&status=Active" data-toggle="tooltip" title="Active Home Driver">
																		<img src="img/active-icon.png" alt="<?php echo $eStatus ?>" >
																	</a>
																	<a href="home_driver.php?iDriverId=<?php echo  $iDriverId; ?>&status=Inactive" data-toggle="tooltip" title="Inactive Home Driver">
																		<img src="img/inactive-icon.png" alt="<?php echo $eStatus; ?>" >
																	</a>

																	<!-- <a href="languages.php?id=<?php echo $id;?>&action=delete"><i class="icon-trash"></i> Delete</a>-->
																	<form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm_delete()" class="margin0">
																		<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $iDriverId;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Delete Home Driver">
																			<img src="img/delete-icon.png" alt="Delete">
																		</button>
																	</form>
																</td>
															</tr>
															<?php }
														} else { ?>
														<tr class="gradeA">
															<td colspan="7"  align="center">داده ای یافت نشد</td>
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
				$('#dataTables-example').dataTable( {"bSort": false } );
			});
		</script>
	</body>
	<!-- END BODY-->
</html>
