<?
	include_once('../common.php');

	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();
	$success=$_REQUEST['success'];
		 $sql = "SELECT * FROM currency WHERE eStatus = 'Active' order by iDispOrder";
		$db_currency = $obj->MySQLSelect($sql);
	//echo "<pre>";print_r($db_currency);exit;
		$vName="SELECT vName FROM currency WHERE eStatus = 'Active' order by iDispOrder";
	 $db_vName=$obj->MySQLSelect($vName);
	  //print_r($db_vName);
	  for($i=0;$i<count($db_vName);$i++)
	  {
	     $db_name[$i]=$db_vName[$i]["vName"];
	  }
		$script 	= 'Settings';
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>واحد پول | ادمین</title>
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
					<div id="add-hide-show-div">
						<div class="row">
							<div class="col-lg-12">
								<h2>مدیرت نرخ ارز</h2>
								<!-- <input type="button" id="show-add-form" value="ADD A DRIVER" class="add-btn">
								<input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn"> -->
							</div>
						</div>
						<hr />
					</div>
					<?php if ($success == 1) {?>
					<div class="alert alert-success alert-dismissable">
							 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								واحد پول با موفقیت ویرایش شد
					</div><br/>
					<?}
					else if($success == 2)
					{
					?>
					<div class="alert alert-danger alert-dismissable">
								 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								 "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
						</div><br/>
					<?
						}
					?>
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
										<div class="table-responsive">
											<form action="currency_action.php" method="post">
												<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>واحد پول</th>
														<th>نسبت</th>
														<th>مقدار آستانه</th>
														<th>نماد</th>
													</tr>
												</thead>
												<tbody>
													<?php  foreach ($db_currency as $key => $value) {
														echo '
																<tr>
																	<td><input class="form-control" type="hidden" name="iCurrencyId[]" value="'.$value['iCurrencyId'].'" />'.$value["vName"].'</td>
																	<td><input class="form-control" name="Ratio[]" type="text" value='.$value['Ratio'].' /></td>
																	<td><input class="form-control" name="fThresholdAmount[]" type="text" value='.$value['fThresholdAmount'].' /></td>
																	<td><input  class="form-control" name="vSymbol[]" type="text" value='.$value['vSymbol'].' /></td>';
																	echo '</tr>';
													}
												?>
												<tr><td colspan="<?echo count($db_currency)+1;?>" align="center"><button name="submit" class="btn btn-default">ویرایش واحد پول</button></td></tr>
												</tbody>
											</table>
										</form>
										</div>
							</div> <!--TABLE-END-->
						</div>
					</div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->


		<?
			include_once('footer.php');
		?>
		<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
		<!--<script>
			$(document).ready(function () {
				$('#dataTables-example').dataTable();
			});
		</script>-->
	</body>
	<!-- END BODY-->
</html>
