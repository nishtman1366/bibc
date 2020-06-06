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
	
	$script = 'Students';
		
	$tbl_name = "register_student";
	
	$cmp_ssql = "";
	if(SITE_TYPE =='Demo'){
		$cmp_ssql .= " And cb.dAddredDate > '".WEEK_DATE."'";
	}

	if ($action == 'view' && $iAreaId != 0) {
		 $sql = "SELECT st.*, CONCAT(rd.vName,' ',rd.vLastName) as driverName, CONCAT(ru.vName,' ',ru.vLastName) as parentName FROM {$tbl_name} as st
		 LEFT JOIN register_user as ru on st.iParentId=ru.iUserId LEFT JOIN register_student_groups as groups ON  st.iGroupId=groups.iGroupId  LEFT JOIN register_driver as rd on groups.iDriverId=rd.iDriverId WHERE st.iAreaId = $iAreaId ".$cmp_ssql . "  ORDER BY `st`.`iStudentId` DESC";
        #die($sql);
		 $data_st = $obj->MySQLSelect($sql);
		 #echo "<pre>"; print_r($data_st); die;
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
					<h2 class="header-page trip-detail driver-detail1"><?php echo $langage_lbl['BTN_ADD_STUDENT']; ?><a href="javascript:void(0);" onClick=""><?php echo $langage_lbl['BTN_ADD_STUDENT']; ?></a>
					<a href="students_groups.php" onClick=""><?php echo $langage_lbl['LBL_SCHOOL_GROUPS']; ?></a></h2>
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
												<th width="20%"><?php echo  $langage_lbl['LBL_SCHOOL_NAME'];?></th>
												<th>نام پدر<?php echo  ''#$langage_lbl['LBL_PICKUP_TXT'];?></th>
												<th width="10%"><?php echo  $langage_lbl['LBL_COMPANY_TRIP_DRIVER'];?></th>
												<th width="15%" style="width: 67px;"><?php echo  $langage_lbl['LBL_ADD_SCHOOL_PLACE_TXT']; ?></th>
												<th width="14%"><?php echo  $langage_lbl['LBL_SCHOOL_GO_TIME_LIMIT'];?></th>
												<th width="14%"><?php echo  $langage_lbl['LBL_SCHOOL_REBACK_TIME_LIMIT'];?></th>
												<th width="8%"><?php echo  $langage_lbl['LBL_STATUS_TXT'];?></th>
											</tr>
										</thead>
										<tbody>
											<?php for ($i = 0; $i < count($data_st); $i++) { ?>
												<tr class="gradeA">
													<td data-order='[[ 1, "asc" ]]'><?php echo  $data_st[$i]['iStudentId']; ?></td>
													<td><a href="students_action.php?id=<?php echo  $data_st[$i]['iStudentId']; ?>"><?php echo  $data_st[$i]['vSName'].' '.$data_st[$i]['vSLastName']; ?></a></td>
													<td><?php echo  $data_st[$i]['vSchoolName']; ?></td>
													<td><?php echo  $data_st[$i]['parentName']; ?></td>
													<td><?php echo  $data_st[$i]['driverName'] == "" ? "---" : $data_st[$i]['driverName']; ?></td>
													<td><?php echo  $data_st[$i]['vHomeAddress']; ?></td>
													
													<td><?php if($data_st[$i]['eEnableGoing'] == "No") echo '---'; else echo  $data_st[$i]['tGoingTime']; ?> </td>
													<td><?php if($data_st[$i]['eEnableComeBack']== "No") echo '---'; else echo  $data_st[$i]['tComeBackTime']; ?> </td>
													<td><?php if($data_st[$i]['eStatus'] == "Assign") {
													echo "Driver Assigned";
													}else { 
														$sql="select iActive from trips where iTripId=".$data_st[$i]['iTripId'];
														$data_stat=$obj->MySQLSelect($sql);
													//echo "<pre>";print_r($data_stat); die;
													if($data_stat)
													{
														for($d=0;$d<count($data_stat);$d++)
														{echo $data_stat[$d]['iActive']; }
													}
													 else
													 {echo $data_st[$i]['eStatus'];}
												   }?>
													<?php if ($data_st[$i]['eStatus'] == "Cancel") { ?>
														<br /><a href="javascript:void(0);" class="btn btn-info" data-toggle="modal" data-target="#uiModal_<?php echo $data_st[$i]['iCabBookingId'];?>">Cancel Reason</a>
													<?php } ?>
											  </td>
											 </tr>
										   <div class="col-lg-12">
												 <div class="modal fade" id="uiModal_<?php echo $data_st[$i]['iCabBookingId'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
													  <div class="modal-content image-upload-1" style="width:400px;">
														   <div class="upload-content" style="width:350px;">
																<h3>Booking Cancel Reason</h3>
																<h4>Cancel By: <?php echo $data_st[$i]['eCancelBy'];?></h4>
																<h4>Cancel Reason: <?php echo $data_st[$i]['vCancelReason'];?></h4>
																<form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="" name="frm6">
																	 <div class="form-group">
																		  <div class="col-lg-12">
																			  
																		  </div>
																	 </div>
																	 <div class="col-lg-13">
																		  
																	 </div>
									 
																	 
																	 <input type="button" class="save" data-dismiss="modal" name="cancel" value="Close">
																</form>
														   </div>
													  </div>
												 </div>
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
