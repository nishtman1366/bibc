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
	$iFaqId 		= isset($_GET['iFaqId'])?$_GET['iFaqId']:'';
	$status 		= isset($_GET['status'])?$_GET['status']:'';
	//sort order
	$flag 			= isset($_GET['flag'])?$_GET['flag']:'';
	$id 			= isset($_GET['id'])?$_GET['id']:'';

	$tbl_name 		= 'faqs';
	$script 		= 'Settings';

	//delete record
	if($hdn_del_id != ''){
	  if(SITE_TYPE =='Demo'){
	     header("Location:faq.php?success=2");exit;
	  }
		$data_q 	= "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM `".$tbl_name."`";
		$data_rec  	= $obj->MySQLSelect($data_q);
		//echo '<pre>'; print_r($data_rec); echo '</pre>';
		$order = isset($data_rec[0]['iDisplayOrder'])?$data_rec[0]['iDisplayOrder']:0;

		$data_logo =  $obj->MySQLSelect("SELECT iDisplayOrder FROM ".$tbl_name." WHERE iFaqId = '".$hdn_del_id."'");

		if(count($data_logo) > 0)
		{
			$iDisplayOrder =  isset($data_logo[0]['iDisplayOrder'])?$data_logo[0]['iDisplayOrder']:'';
			$obj->sql_query("DELETE FROM `".$tbl_name."` WHERE iFaqId = '".$hdn_del_id."'");

			if($iDisplayOrder < $order)
			for($i = $iDisplayOrder+1; $i <= $order; $i++)
			$obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i-1)." WHERE iDisplayOrder = ".$i);
		}
	}

	if($id != 0) {
		if($flag == 'up')
		{
			$sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM ".$tbl_name." WHERE iFaqId ='".$id."'");
			$order_data = isset($sel_order[0]['iDisplayOrder'])?$sel_order[0]['iDisplayOrder']:0;
			$val = $order_data - 1;
			if($val > 0) {
				$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$order_data."' WHERE iDisplayOrder='".$val."'");
				$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$val."' WHERE iFaqId = '".$id."'");
			}
		}

		else if($flag == 'down')
		{
			$sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM ".$tbl_name." WHERE iFaqId ='".$id."'");

			$order_data = isset($sel_order[0]['iDisplayOrder'])?$sel_order[0]['iDisplayOrder']:0;

			$val = $order_data+ 1;
			$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$order_data."' WHERE iDisplayOrder='".$val."'");
			$obj->MySQLSelect("UPDATE ".$tbl_name." SET iDisplayOrder='".$val."' WHERE iFaqId = '".$id."'");
		}
		header("Location:faq.php");
	}

	if($iFaqId != '' && $status != ''){
		$query = "UPDATE `".$tbl_name."` SET eStatus = '".$status."' WHERE iFaqId = '".$iFaqId."'";
		$obj->sql_query($query);
	}

	 $sql = "SELECT f.*, fc.vTitle cat_name FROM ".$tbl_name." f, faq_categories fc WHERE f.iFaqcategoryId = fc.iUniqueId AND fc.vCode = '".$default_lang."' ORDER BY f.iFaqcategoryId, f.iDisplayOrder";
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
		<title>پرسش و پاسخ | ادمین </title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="" name="keywords" />
		<meta content="" name="description" />
		<meta content="" name="author" />
		<?php include_once('global_files.php');?>

		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
		<script type="text/javascript">
			function confirm_delete()
			{
				var confirm_ans = confirm("Are You sure You want to Delete FAQ?");
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
							<h2>پرسش و پاسخ</h2>
							<a href="faq_action.php">
								<input type="button" value="افزودن پرسش و پاسخ" class="add-btn">
							</a>
						</div>
					</div>
					<hr />
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
							   <?php if ($_GET['success'] == 2) {?>
                   <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                        "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                   </div><br/>
                   <?} ?>
								<div class="panel panel-default">
									<div class="panel-heading">
										پرسش و پاسخ
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>عنوان</th>
														<th>دسته بندی</th>
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
																$vTitle 		= $db_data[$i]['vTitle_EN'];
																$cat_name 		= $db_data[$i]['cat_name'];
																$iDisplayOrder 	= $db_data[$i]['iDisplayOrder'];
																$eStatus 		= $db_data[$i]['eStatus'];
																$iFaqcategoryId	= $db_data[$i]['iFaqcategoryId'];
																$iFaqId 		= $db_data[$i]['iFaqId'];
																$checked		= ($eStatus=="Active")?'checked':'';
															?>
															<tr class="gradeA">
																<td><?php echo $vTitle;?></td>
																<td><?php echo $cat_name;?></td>
																<td width="12%" align="center">
																	<?php if($iDisplayOrder != 1) { ?>
																		<a href="faq.php?id=<?php echo $iFaqId;?>&flag=up">
																			<button class="btn btn-warning">
																				<i class="icon-arrow-up"></i>
																			</button>
																		</a>
																		<?php } if($iDisplayOrder != $count_all) { ?>
																		<a href="faq.php?id=<?php echo $iFaqId;?>&flag=down">
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
																<td width="18%"  class=" veh_act">
																	<a href="faq_action.php?id=<?php echo $iFaqId;?>&faq_cat_id=<?php echo $iFaqcategoryId;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Edit FAQ">
																			<img src="img/edit-icon.png" alt="Edit">
																		</button>
																	</a>

																	<a href="faq.php?iFaqId=<?php echo $iFaqId;?>&status=Active" data-toggle="tooltip" title="Active FAQ">
																		<img src="img/active-icon.png" alt="<?php echo $eStatus ?>" >
																	</a>
																	<a href="faq.php?iFaqId=<?php echo  $iFaqId; ?>&status=Inactive" data-toggle="tooltip" title="Inactive FAQ">
																		<img src="img/inactive-icon.png" alt="<?php echo $eStatus; ?>" >
																	</a>
																	<!-- <a href="languages.php?id=<?php echo $id;?>&action=delete"><i class="icon-trash"></i> Delete</a>-->
																	<form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm_delete()" class="margin0">
																		<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $iFaqId;?>">
																		<button class="remove_btn001" data-toggle="tooltip" title="Delete FAQ">
																			<img src="img/delete-icon.png" alt="Delete">
																		</button>
																	</form>
																</td>
															</tr>
															<?php }
														} else { ?>
														<tr class="gradeA">
															<td colspan="6">داده ای یافت نشد</td>
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
