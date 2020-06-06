<?
	ob_start();
	include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('COMPANY') == false)
		die('you dont`t have permission...');


	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();

	$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:'';
	$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
	$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
	$action 	= isset($_REQUEST['action'])?$_REQUEST['action']:'view';
	$success	= isset($_REQUEST['success'])?$_REQUEST['success']:0;
	$hdn_del_id	= isset($_REQUEST['hdn_del_id'])?$_REQUEST['hdn_del_id']:'';
	$script		= "darkhastsafarranandeh";

	$sql = "select * from country";
	$db_country = $obj->MySQLSelect($sql);
	//echo"<pre>";print_r($db_country);exit;

	$sql = "select * from language_master where eStatus = 'Active'";
	$db_lang = $obj->MySQLSelect($sql);

	/*Language Label Other*/
	$sql="select vLabel,vValue from language_label where vCode='PS'";
	$db_lbl=$obj->MySQLSelect($sql);
	foreach ($db_lbl as $key => $value) {
		$langage_lbl_array[$value['vLabel']] = $value['vValue'];
	//	$langage_lbl[$value['vLabel']] = $value['vValue']."  <span style='font-size:9px;'>".$value['vLabel'].'</span>';
	}
	//echo "<pre>";print_r($langage_lbl_array);exit;


	if ($iCompanyId != '' && $status != '') {
		if(SITE_TYPE !='Demo'){
			$query = "UPDATE delete_form SET eStatus = '" . $status . "' WHERE iCompanyId = '" . $iCompanyId . "'";
			$obj->sql_query($query);
			$var_msg="Company ".$status." Successfully.";
			header("Location:company.php?success=1&var_msg=".$var_msg);exit;
		}
		else{
			header("Location:company.php?success=2");
			echo "<script>document.location='company.php?success=2';</script>";
			exit;
		}
		$sql="SELECT * FROM `driver_request` ORDER BY BINARY NAME ASC";
		$db_status = $obj->MySQLSelect($sql);
		$maildata['EMAIL'] =$db_status[0]['vEmail'];
		$maildata['NAME'] = $db_status[0]['vCompany'];

		//$maildata['DETAIL']="Your Account is ".$db_status[0]['eStatus'];
		$status = ($db_status[0]['eStatus'] == "Active") ? $langage_lbl_array['LBL_ACTIVE_TEXT'] : $langage_lbl_array['LBL_INACTIVE_TEXT'];
		$maildata['DETAIL']=$langage_lbl_array['LBL_YOUR_ACCOUNT_TEXT']." ".$status.".";

		$generalobj->send_email_user("ACCOUNT_STATUS",$maildata);
	}




















	if($action == 'delete' && $hdn_del_id != '')
	{

		$query = "UPDATE driver_request SET eStatus = 'Deleted' WHERE iCompanyId = '".$hdn_del_id."'";
		$obj->sql_query($query);
		$action = "view";

	}

	$cmp_ssql = "";
	if(SITE_TYPE =='Demo'){
		$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
	}

	if($action == 'view')
	{
		$sqldvdv = "SELECT * FROM `sentsms` where 1=1";
		//$vehicles  	= $obj->MySQLSelect($sqldvdv);

	}





  if($action == 'date')
  {$sqldvdv = "SELECT * FROM `sentsms` where 1=1";
  	$startDate=$_REQUEST['startDate'];
  	$endDate=$_REQUEST['endDate'];
    //die($startDate . "s / e" . $endDate);
  	if($startDate!=''){
  		$sqldvdv.=" AND Date(`date`) >='".$startDate."'";
  	}
  	if($endDate!=''){
  		$sqldvdv.=" AND Date(`date`) <='".$endDate."'";
  	}


  }

  if($_GET['Status'] != '')
	{
		$sqldvdv .= " and type = '" . $_GET['Status'] . "'";
		//$vehicles  	= $obj->MySQLSelect($sqldvdv);

	}


  if($_GET['idc'] != '')
	{
		$listuseridc = '';
    $sql = "select * from register_driver WHERE eStatus != 'Deleted' and iCompanyId = '" . $_GET['idc'] . "'";
    $db_iDriverId = $obj->MySQLSelect($sql);


		/*for ($i = 0; $i < count($db_iDriverId); $i++) {

		 $sqldvdv .= " or (`driverId` = " . $db_iDriverId[$i]['iDriverId'] . ")";
	 }*/

		//$vehicles  	= $obj->MySQLSelect($sqldvdv);

	}




  if($_GET['driver'] != '')
  {
    $sqldvdv .= " and driverId = '" . $_GET['driver'] . "'";
    //$vehicles  	= $obj->MySQLSelect($sqldvdv);

  }
	else {
		if($_GET['idc'] != '')
		{
			$sqldvdv .= " and (1 = 2";
			for ($i = 0; $i < count($db_iDriverId); $i++) {

			 $sqldvdv .= " or `driverId` = '" . $db_iDriverId[$i]['iDriverId'] . "'";
		 }
		 $sqldvdv .= ")";
		}

	}


	//echo "<Pre>";print_r($sql);exit;
  $sqldvdv .= " ORDER BY `Date` DESC limit 100";
      $data_drv  	= $obj->MySQLSelect($sqldvdv);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>درخواست سفر راننده | ادمین</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />

		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		<?php include_once('global_files.php');?>
		<script>
			$(document).ready(function(){
				$("#show-add-form").click(function(){
					$("#show-add-form").hide(1000);
					$("#add-hide-div").show(1000);
					$("#cancel-add-form").show(1000);
				});

			});
		</script>
		<script>
			$(document).ready(function(){
				$("#cancel-add-form").click(function(){
					$("#cancel-add-form").hide(1000);
					$("#show-add-form").show(1000);
					$("#add-hide-div").hide(1000);
				});

			});

		</script>

	</head>

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
								<h2>درخواست سفر راننده</h2>
								<!--<a href="company_action.php"><input type="button" id="show-add-form" value="ADD A COMPANY" class="add-btn"></a>-->
								<input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn">
							</div>
						</div>
						<hr />
					</div>
					<?php if($success == 1) { ?>
						<div class="alert alert-success alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<?php echo isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "Company Updated SuccessFully.";?>
						</div><br/>
						<?php }elseif ($success == 2) { ?>
						<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
						</div><br/>
					<?php }?>







        </br>
        <div style="margin: 13px;margin-top: -6px;">
          شرکت ها<br>
          <select class="form-control" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">

            <?

echo '<option value="driver_request.php?Status='. $_GET['Status'] .'">--select--</option>';

            $sql = "select * from company WHERE eStatus != 'Deleted'";
            $db_company = $obj->MySQLSelect($sql);
             for ($i = 0; $i < count($db_company); $i++) {
              if($db_company[$i]['iCompanyId'] == $_GET['idc'])
              {
              echo '<option selected value ="driver_request.php?idc=' . $db_company[$i]['iCompanyId'] . '&Status='. $_GET['Status'] .'">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
             }
             else {
              echo '<option value ="driver_request.php?idc=' . $db_company[$i]['iCompanyId'] . '&Status='. $_GET['Status'] .'">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
             }
} ?>
          </select>


<br>
راننده ها<br>
          <select class="form-control" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">
            <?
            echo '<option value="driver_request.php?Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">--Select--</option>';

            if($_GET['idc'] != ''){
              for ($i = 0; $i < count($db_iDriverId); $i++) {
               if($db_iDriverId[$i]['iDriverId'] == $_GET['driver'])
               {
               echo '<option selected value ="driver_request.php?driver=' . $db_iDriverId[$i]['iDriverId'] . '&Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">' . $db_iDriverId[$i]['vName'] . " " . $db_iDriverId[$i]['vLastName'] . " </option>";
              }
              else {
               echo '<option value ="driver_request.php?driver=' . $db_iDriverId[$i]['iDriverId'] . '&Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">' . $db_iDriverId[$i]['vName'] . " " . $db_iDriverId[$i]['vLastName'] . " </option>";
              }
 }
            }
            else {
              echo '<option value="driver_request.php">هنوز کمپانی انتخاب نشده است</option>';
            }





?>
          </select>





          <br>
          وضعیت<br>
                    <select class="form-control" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">
                      <?
                      echo '<option value="driver_request.php?' . 'idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">All</option>';
//$sql = "select * from company WHERE eStatus != 'Deleted'";
                      //$db_company = $obj->MySQLSelect($sql);
                       //for ($i = 0; $i < count($db_company); $i++) {
if($_GET['Status'] == 'Reject')
{
  echo'<option  selected value ="driver_request.php?Status=Reject' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Reject</option>';
}
else {
  echo'<option value ="driver_request.php?Status=Reject' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Reject</option>';
}

if($_GET['Status'] == 'Arrived')
{
  echo'<option selected value ="driver_request.php?Status=Arrived' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Arrived</option>';
}
else {
  echo'<option value ="driver_request.php?Status=Arrived' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Arrived</option>';
}


if($_GET['Status'] == 'Timeout')
{
  echo'<option selected value ="driver_request.php?Status=Timeout' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Timeout</option>';
}
else {
  echo'<option value ="driver_request.php?Status=Timeout' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Timeout</option>';
}


if($_GET['Status'] == 'Accept')
{
  echo'<option selected value ="driver_request.php?Status=Accept' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Accept</option>';
}
else {
  echo'<option value ="driver_request.php?Status=Accept' . '&idc='. $_GET['idc'] .'&driver='. $_GET['driver'] .'">Accept</option>';
}






          //}
          ?>
                    </select>









                    <br>

                    <form name="search" action="" method="post" onSubmit="return checkvalid()">
                      <div class="Posted-date mytrip-page">
                        <input type="hidden" name="action" value="date" />
                        <h3>جست و جو بر اساس تاریخ</h3>

                        <span>
                        <input type="date" id="dp4" name="startDate" placeholder="از تاریخ" class="form-control" value=""  style="cursor:default; background-color: #fff" />
                        <input type="date" id="dp5" name="endDate" placeholder="تا تاریخ" class="form-control" value=""  style="cursor:default; background-color: #fff"/>
                        <b><button class="driver-trip-btn">جست و جو</button>
                          <button onClick="reset();" class="driver-trip-btn">پاک سازی لیست</button></b>
                        </span>
                      </div>
                    </form>





</div>













					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										درخواست سفر راننده
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>نام راننده</th>
														<!--<th>EMAIL</th>-->
														<th>نام مسافر</th>
														<th>شماره سفر</th>
														<!--<th>iMSGCode</th>-->
														<th>وضعیت</th>
														<th>تاریخ</th>
														<!--<th>EDIT DOCUMENT</th>-->
														<th align="center" style="text-align:center;">حذف</th>
														<!--<th>Delete</th>-->
													</tr>
												</thead>
												<tbody>

													<?for($i=0;$i<count($data_drv);$i++)
														{
															$sql = "SELECT * from register_driver where iDriverId = '".$data_drv[$i]['driverId']."'";
															$db_cnt = $obj->MySQLSELECT($sql);
															$data_drv[$i]['namelastname'] = $db_cnt[0]['vName'] . " " . $db_cnt[0]['vLastName'];






                              $sql = "SELECT count(iUserId), vName, vLastName from register_user where iUserId = '".$data_drv[$i]['riderId']."'";
                              $db_cnt2 = $obj->MySQLSELECT($sql);
                              $data_drv[$i]['namelastnamerider'] = $db_cnt2[0]['vName'] . " " . $db_cnt2[0]['vLastName'];
															//echo "<pre>";print_r($db_cnt);echo "</pre>";
														?>
														<tr class="gradeA">
															<td><?php echo $data_drv[$i]['namelastname']; ?></td>
															<!--<td><?php ?></td>-->
															<td>
<?php echo $data_drv[$i]['namelastnamerider']; ?>
                                <!--<a href="driver.php?iCompanyid=<?php echo  $data_drv[$i]['iUserId']; ?>" target="_blank"><?php echo $data_drv[$i]['count']; ?></a>-->

                              </td>
															<!--<td class="center"><?php echo $data_drv[$i]['vServiceLoc']; ?></td>-->
															<!--<td><?php echo $data_drv[$i]['vPhone']; ?></td>-->
															<?php
															$sql = "SELECT * from savar_area where aId = '".$data_drv[$i]['iAreaId']."'";
															$db_cnt23 = $obj->MySQLSELECT($sql);
															$scscscscs = $db_cnt23[0]['sAreaNamePersian'];
															 ?>
															<td>
<a href="invoice.php?iTripId=<?php echo  $data_drv[$i]['iMsgCode']; ?>" target="_blank"><?php echo $data_drv[$i]['iMsgCode']; ?></a>
                                </td>
															<!--<td><?php echo  $data_drv[$i]['iMsgCode'];?></td>-->
                              <?php if($data_drv[$i]['iCompanyId']==1) {?>
                                <td width="10%" align="center">
                                   <b align="center">-----</b>
                                </td>

                                <?php }else {?>
                                <td width="10%" align="center">
                                <?php if($data_drv[$i]['eStatus'] == 'Active') {
                                    $dis_img = "img/active-icon.png";
                                  }else if($data_drv[$i]['eStatus'] == 'Inactive'){
                                     $dis_img = "img/inactive-icon.png";
                                  }else if($data_drv[$i]['eStatus'] == 'Deleted'){
                                    $dis_img = "img/delete-icon.png";
                                  }
                                  ?>
                                  <?php echo $data_drv[$i]['type']?>
                                </td>
                              <?php }?>
															<td style="direction: rtl;"><?php  echo jdate('d-F-Y H:i:s',strtotime($data_drv[$i]['tDate']));?>
																<!-- --->

																<!--<td align="center" width="10%">
																	<?php if($data_drv[$i]['iCompanyId']==1) {?>
																		<b align="center">-----</b>
																	</td>
																	<?php }else {?>
																	<a href="company_document_action.php?id=<?php echo  $data_drv[$i]['iCompanyId']; ?>&action=edit">
																		<img src="img/edit-doc.png" alt="Edit Document" >
																	</a>
																<?php }?>
															</td>-->
															<td class="center" width="12%"  align="center" style="text-align:center;">
																<!--<a href="company_action.php?id=<?php echo $data_drv[$i]['iCompanyId'];?>" data-toggle="tooltip" title="Edit">
																	<img src="img/edit-icon.png" alt="Edit">
																</a>
																<a href="company.php?iCompanyId=<?php echo  $data_drv[$i]['iCompanyId']; ?>&status=Active" data-toggle="tooltip" title="Active Company">
																	<?php if($data_drv[$i]['iCompanyId']!=1) {?>
																		<img src="img/active-icon.png" alt="Active" >
																	<?php } ?>
																</a>
																<a href="company.php?iCompanyId=<?php echo  $data_drv[$i]['iCompanyId']; ?>&status=Inactive " data-toggle="tooltip" title="Inactive Company">
																	<?php if($data_drv[$i]['iCompanyId']!=1) {?>
																		<img src="img/inactive-icon.png" alt="Inactive" >
																	<?php } ?>
																</a>-->



																<form name="delete_form" id="delete_form" method="post" action="" onsubmit="return confirm_delete()" class="margin0">
																<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $data_drv[$i]['iCompanyId'];?>">
																<input type="hidden" name="action" id="action" value="delete">
																<button class="btn btn-danger remove_btn001">
																<img src="img/delete-icon.png" alt="Delete">
																</button>
																</form>
															</td>
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
					<!-- </div> -->
				<div class="clear"></div>
				</div>
				<div class="clear"></div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->


		<?php include_once('footer.php');?>
		<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script>
			$(document).ready(function () {
				$('#dataTables-example').dataTable({
					"order": [[ 3, "desc" ]]
				});
			});
			function confirm_delete()
			{
				var confirm_ans = confirm("Are You sure You want to Delete Company?");
				return confirm_ans;
				//document.getElementById(id).submit();
			}
			function changeCode(id)
			{
				var request = $.ajax({
					type: "POST",
					url: 'change_code.php',
					data: 'id='+id,

					success: function(data)
					{
						document.getElementById("code").value = data ;
						//window.location = 'profile.php';
					}
				});
			}

      function reset() {
       location.reload();

     }

     function checkvalid(){
       if($("#dp5").val() < $("#dp4").val()){
         alert("From date should be lesser than To date.")
         return false;
       }
     }
		</script>
	</body>
	<!-- END BODY-->
</html>
