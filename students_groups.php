<?php
	include_once('common.php');
	require_once(TPATH_CLASS .'savar/jalali_date.php');
	$generalobj->check_member_login();
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
	$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
	$iCompanyId = $_SESSION['sess_iUserId'];
	
 

	#echo "<pre>";print_r($_SESSION);exit;
	
        // ADDED BY SEYYED AMIR
        $sql = "SELECT `iCompanyId` FROM `company` where `iParentId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
		$comp_childs = $obj->MySQLSelect($sql);
        $comp_list = $iCompanyId;
        
        foreach($comp_childs as $comp)
        {
            $comp_list .= ',' . $comp['iCompanyId'];
        }
        ////////////////////////
        
        $iAreaId = 0;
        $sql = "SELECT * FROM `company` where `iCompanyId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
		$thiscomp = $obj->MySQLSelect($sql);
        
        if(count($thiscomp) > 0)
            $iAreaId = $thiscomp[0]['iAreaId'];

#die("a".$iAreaId);

	$sql = "select * from country";
	$db_country = $obj->MySQLSelect($sql);
	
	$sql = "select * from language_master where eStatus = 'Active'";
	$db_lang = $obj->MySQLSelect($sql);
	
	$script = 'StudentsGroups';
		
	$tbl_name = "register_student_groups";
	

	if ($action == 'view' && $iAreaId != 0) {
		 $sql = "SELECT stg.*, CONCAT(rd.vName,' ',rd.vLastName) as driverName FROM {$tbl_name} as stg
		 LEFT JOIN register_driver as rd on stg.iDriverId=rd.iDriverId
		 WHERE 1 ORDER BY `stg`.`iGroupId` DESC";
        //die($sql);
		 $data_stg = $obj->MySQLSelect($sql);
		 #echo "<pre>"; print_r($data_std); die;
	}
	
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo $SITE_NAME?> | <?php echo $langage_lbl['BTN_ADD_STUDENT']; ?></title>
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		<!-- End: Default Top Script and css-->
	</head>
	<body>
		<!-- home page -->
		<div id="main-uber-page">
			<!-- Left Menu -->
			<?php include_once("top/left_menu.php");?>
			<!-- End: Left Menu-->
			<!-- Top Menu -->
			<?php include_once("top/header_topbar.php");?>
			<!-- End: Top Menu-->
			<!-- contact page-->
			<div class="page-contant">
				<div class="page-contant-inner">
					<h2 class="header-page trip-detail driver-detail1"><?php echo $langage_lbl['LBL_SCHOOL_GROUPS']; ?><a href="students_groups_action.php" onClick=""><?php echo $langage_lbl['BTN_ADD_STUDENT_GROUP']; ?></a></h2>
					<!-- trips page -->
					<div class="trips-page trips-page1">
						<?php if ($_REQUEST['success']==1) {?>
							<div class="alert alert-success alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
								<?php echo  $var_msg ?>
							</div>
							<?}else if($_REQUEST['success']==2){ ?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
							</div>
							<?php 
							} else if(isset($_REQUEST['success']) && $_REQUEST['success']==0){?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
								<?php echo  $var_msg ?>
							</div>
							<?php }
						?>
						<div class="trips-table trips-table-driver trips-table-driver-res"> 
							<div class="trips-table-inner">
								<div class="driver-trip-table">
									<table width="100%" border="0" cellpadding="0" cellspacing="0" id="dataTables-example">
										<thead>
											<tr>
												<th width="20%">#</th>
												<th width="25%"><?php echo  $langage_lbl['LBL_FIRST_NAME_TXT'];?></th>
												<th width="10%"><?php echo  $langage_lbl['LBL_COMPANY_TRIP_DRIVER'];?></th>
												<th width="20%"></th>
												<th><?php echo  ''#$langage_lbl['LBL_PICKUP_TXT'];?></th>
												<th><?php echo  $langage_lbl['LBL_SEATS_NUMBER'];?></th>
												<th>Usage</th>
												
											</tr>
										</thead>
										<tbody>
											<?php for ($i = 0; $i < count($data_stg); $i++) { ?>
												<tr class="gradeA">
													<td data-order='[[ 1, "asc" ]]'><?php echo  $data_stg[$i]['iGroupId']; ?></td>
													<td><a href="students_groups_action.php?id=<?php echo  $data_stg[$i]['iGroupId']; ?>"><?php echo  $data_stg[$i]['vGroupName']; ?></a></td>
													<td><?php echo  $data_stg[$i]['driverName'] == "" ? "---" : $data_stg[$i]['driverName']; ?></td>
													<td><?php echo  $data_stg[$i]['tMorningStartTime']; ?></td>
													<td><?php echo  $data_stg[$i]['tNoonStartTime']; ?></td>
													<td><?php echo  $data_stg[$i]['iSeatNumber']; ?></td>
													<td><?php echo  $data_stg[$i]['iSeatUsage']; ?></td>
													
											 </tr>
										   <div class="col-lg-12">
												 
											</div>                                                                 
											 <?php } ?>
										</tbody>
									</table>
								</div>  </div>
						</div>
						<!-- -->
						<?php //if(SITE_TYPE=="Demo"){?>
							<!-- <div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
							This feature will be enabled in the main product we will provide you.</span> </div> --->
						<?php //}?>
						<!-- -->
					</div>
					<!-- -->
					<div style="clear:both;"></div>
				</div>
			</div>
			<!-- footer part -->
			<?php include_once('footer/footer_home.php');?>
			<!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
		</div>
		<!-- home page end-->
		<!-- Footer Script -->
		<?php include_once('top/footer_script.php');?>
		<script src="assets/js/jquery-ui.min.js"></script>
		<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#dataTables-example').dataTable({
					 "language": {
					 <?php echo Datatablelang?>
				 },
					});
			});
			function confirm_delete(id)
			{
				bootbox.confirm("Are You sure You want to Delete this Driver?", function(result) {
					if(result){
						document.getElementById('delete_form_'+id).submit();
					}
				});
			}
			function changeCode(id)
			{
				var request = $.ajax({
					type: "POST",
					url: 'change_code.php',
					data: 'id=' + id,
					success: function (data)
					{
						document.getElementById("code").value = data;
						//window.location = 'profile.php';
					}
				});
			}
			
			function manual_dispatch_form(){
				window.location.href = "manual_dispatch.php";
			}
		</script>
		
		<script type="text/javascript">
			$(document).ready(function(){
				$("[name='dataTables-example_length']").each(function(){
					$(this).wrap("<em class='select-wrapper'></em>");
					$(this).after("<em class='holder'></em>");
				});
				$("[name='dataTables-example_length']").change(function(){
					var selectedOption = $(this).find(":selected").text();
					$(this).next(".holder").text(selectedOption);
				}).trigger('change');
			})
		</script>
		<!-- End: Footer Script -->
	</body>
</html>
