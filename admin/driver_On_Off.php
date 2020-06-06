<?
ob_start();
include_once('../common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
include_once('savar_check_permission.php');
if(checkPermission('COMPANY') == false)
die('you dont`t have permission...');

$script = 'driver_On_Off';
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
//$script		= "darkhastsafarranandeh";

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




















/*	if($action == 'delete' && $hdn_del_id != '')
{

$query = "UPDATE driver_request SET eStatus = 'Deleted' WHERE iCompanyId = '".$hdn_del_id."'";
$obj->sql_query($query);
$action = "view";

}*/

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
	$cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
}

if($action == 'view')
{
	//$sqldvdv = "SELECT * FROM `driver_log_report` WHERE 1=1 ";
	//$vehicles  	= $obj->MySQLSelect($sqldvdv);






	if($_GET['idc']  != '')
	{
		$sqldvdv2 = "SELECT * FROM `driver_log_report` WHERE 1=1 ";
	}else {
		$sqldvdv2 = "SELECT * FROM `driver_log_report` WHERE 1 ";
	}





	//$vehicles = $obj->MySQLSelect($sqldvdv);








}



$startDate='';
$endDate='';
$myssql = '';

if($action == 'date')
{
	$sqldvdv = "SELECT * FROM `driver_log_report` WHERE 1=1 ";
	$startDate=$_REQUEST['startDate'];
	$endDate=$_REQUEST['endDate'];
	//die($startDate . "s / e" . $endDate);
	if($startDate!=''){
		$sqldvdv.=" AND Date(`dLoginDateTime`) >='".$startDate."'";
		$myssql .= " AND Date(`dLoginDateTime`) >='".$startDate."'";
	}
	if($endDate!=''){
		$sqldvdv.=" AND Date(`dLogoutDateTime`) <='".$endDate."'";
		$myssql .= " AND Date(`dLogoutDateTime`) <='".$endDate."'";
	}
}




if($_GET['idc'] != '')
{
	$listuseridc = '';
	$sql = "select * from register_driver WHERE eStatus != 'Deleted' and iCompanyId = '" . $_GET['idc'] . "'";
	$db_iDriverId = $obj->MySQLSelect($sql);


	for ($i = 0; $i < count($db_iDriverId); $i++) {

		//$sqldvdv .= " or (`iDriverId` = " . $db_iDriverId[$i]['iDriverId'] . ")";
	}

	//$vehicles  	= $obj->MySQLSelect($sqldvdv);

}




if($_GET['driver'] != '')
{
	$sqldvdv .= " and `iDriverId` = '" . $_GET['driver'] . "'";
	//$vehicles  	= $obj->MySQLSelect($sqldvdv);

}
else {
	if($_GET['idc'] != '')
	{
		$sqldvdv .= " and (1 = 2";
		for ($i = 0; $i < count($db_iDriverId); $i++) {

			$sqldvdv .= " or `iDriverId` = '" . $db_iDriverId[$i]['iDriverId'] . "'";
		}
		$sqldvdv .= ")";
	}

}

if($_GET['idc']  != ''){
	$sql4 = "SELECT iDriverLogId FROM driver_log_report WHERE 1=1" .$sqldvdv. ' AND 1=1 order by iDriverLogId DESC ';

	$data_drv_recordcount = $obj->MySQLSelect($sql4);
}else {
	$sql4 = "SELECT iDriverLogId FROM driver_log_report WHERE 1=1" .$sqldvdv. ' AND 1=1 order by iDriverLogId DESC ';

	$data_drv_recordcount = $obj->MySQLSelect($sql4);
}



if($_GET['pagerecord'] == '')
{
	$sqldvdv .= "	limit 0,1000";
	$sqldvdv2 .= $sqldvdv;
	$data_drv  	= $obj->MySQLSelect($sqldvdv2);
}
else {
	$sqldvdv .= " limit " . $_GET['pagerecord'] * 1000 . ',1000';
	$sqldvdv2 .= $sqldvdv;
	$data_drv  	= $obj->MySQLSelect($sqldvdv2);
}

$JameSaatKarkard = '';
$JameSaatKarkard3 = '';
if($action == 'date' && $_GET['idc']  != '' && $_GET['driver']  != '')
{
	$JameSaatKarkard = "SELECT SUM(TIMESTAMPDIFF(minute , `dLoginDateTime` , `dLogoutDateTime`)) AS
	 minutes, SUM(TIMESTAMPDIFF(minute , `dLoginDateTime` , `dLogoutDateTime`))/60 AS hours ,
	 SUM(TIMESTAMPDIFF(minute , `dLoginDateTime` , `dLogoutDateTime`))/(60*24) AS days24,
	 SUM(TIMESTAMPDIFF(minute , `dLoginDateTime` , `dLogoutDateTime`))/(60*12) AS days12,
	 SUM(TIMESTAMPDIFF(minute , `dLoginDateTime` , `dLogoutDateTime`))/(60*8) AS days8,
	 SUM(TIMESTAMPDIFF(minute , `dLoginDateTime` , `dLogoutDateTime`))/(60*6) AS days6
	  FROM `driver_log_report` WHERE `iDriverId`=" . $_GET['driver'] . " AND
	 `dLogoutDateTime` > `dLoginDateTime` AND `dLogoutDateTime` <> '0000-00-00 00:00'". $myssql ." ORDER BY `driver_log_report`.`dLoginDateTime` DESC";
	$JameSaatKarkard2  	= $obj->MySQLSelect($JameSaatKarkard);

	$hours=$JameSaatKarkard2[0]['hours'];
	$mins=$JameSaatKarkard2[0]['minutes'];
	$days24=$JameSaatKarkard2[0]['days24'];
	$days12=$JameSaatKarkard2[0]['days12'];
	$days8=$JameSaatKarkard2[0]['days8'];
	$days6=$JameSaatKarkard2[0]['days6'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>ادمین | ساعات کارکرد راننده</title>
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
		<?php include_once('header2.php'); ?>
		<?php include_once('left_menu.php'); ?>

		<!--PAGE CONTENT -->
		<div id="content">
			<div class="inner">

				<div id="add-hide-show-div">
					<div class="row">
						<div class="col-lg-12">
							<h2>ساعات کارکرد راننده</h2>
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


				<!-- <br>
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
</select>-->














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










شرکت ها<br>
<select class="form-control" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">

	<?

	echo '<option value="driver_On_Off.php?Status='. $_GET['Status'] .'">--select--</option>';

	$sql = "select * from company WHERE eStatus != 'Deleted'";
	$db_company = $obj->MySQLSelect($sql);
	for ($i = 0; $i < count($db_company); $i++) {
		if($db_company[$i]['iCompanyId'] == $_GET['idc'])
		{
			echo '<option selected value ="driver_On_Off.php?idc=' . $db_company[$i]['iCompanyId'] . '&Status='. $_GET['Status'] .'">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
		}
		else {
			echo '<option value ="driver_On_Off.php?idc=' . $db_company[$i]['iCompanyId'] . '&Status='. $_GET['Status'] .'">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
		}
	} ?>
</select>


<br>
راننده ها<br>
<select class="form-control" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">
	<?
	echo '<option value="driver_On_Off.php?Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">--Select--</option>';

	if($_GET['idc'] != ''){
		for ($i = 0; $i < count($db_iDriverId); $i++) {
			if($db_iDriverId[$i]['iDriverId'] == $_GET['driver'])
			{
				echo '<option selected value ="driver_On_Off.php?driver=' . $db_iDriverId[$i]['iDriverId'] . '&Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">' . $db_iDriverId[$i]['vName'] . " " . $db_iDriverId[$i]['vLastName'] . " </option>";
			}
			else {
				echo '<option value ="driver_On_Off.php?driver=' . $db_iDriverId[$i]['iDriverId'] . '&Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">' . $db_iDriverId[$i]['vName'] . " " . $db_iDriverId[$i]['vLastName'] . " </option>";
			}
		}
	}
	else {
		echo '<option value="driver_request.php">هنوز کمپانی انتخاب نشده است</option>';
	}





	?>
</select>
</br>

<div style="margin: 13px;margin-top: -6px;">
	تعداد رکورد در هر لیست<br>
	<select class="form-control" name = 'iCompanyId' id = 'iCompanyId' onchange="location = this.value;">
		<?php $listrecord1 = 1; $listrecord2 = count($data_drv_recordcount)/1000; for ($i = 0; $i < count($data_drv_recordcount)/1000; $i++) {

			if($_GET['pagerecord'] == $i)
			{
				echo '<option selected value ="driver_On_Off.php?pagerecord=' . $i . '&driver=' . $_GET['driver'] . '&Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">' . $listrecord1 . ' to ' . $listrecord1+= 1000 . " </option>";
			}
			else {
				echo '<option value ="driver_On_Off.php?pagerecord=' . $i . '&driver=' . $_GET['driver'] . '&Status='. $_GET['status'] .'' . '&idc='. $_GET['idc'] .'">' . $listrecord1 . ' to ' . $listrecord1+= 1000 . " </option>";
			}
			//$listrecord1 += 1000;
		} ?>
	</select>
	<?php if($JameSaatKarkard3 != '00:00:00'){ ?>
	</br>
	<h2> جمع ساعت کارکرد راننده در بازه زمانی مورد نظر</h2>
</br>
<h3></h3>
</br>

<h3>روزهای کارکرد بر مبنای ۲۴ ساعت:</h3>
<?php echo  $days24?>
</br>

<h3>روزهای کارکرد بر مبنای ۱۲ساعت:</h3>
<?php echo  $days12?>
</br>

<h3>روزهای کارکرد بر مبنای ۸ ساعت:</h3>
<?php echo  $days8?>
</br>

<h3>روزهای کارکرد بر مبنای ۶ساعت:</h3>
<?php echo  $days6?>
</br>

<h3>ساعات کارکرد:</h3>
<?php echo  $hours?>
</br>

<h3>دقایق کارکد:</h3>
<?php echo  $mins?>
</br>

<?}?>
</div>

<div class="table-list">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					ساعات کارکرد راننده
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="dataTables-example">
							<thead>
								<tr>
									<th>نام راننده</th>
									<!--<th>EMAIL</th>-->
									<th>ساعت و تاریخ آنلاین شدن</th>
									<th>ساعت و تاریخ آفلاین شدن</th>
									<!--<th>iMSGCode</th>-->

									<th></th>
									<!--<th>EDIT DOCUMENT</th>
									<th align="center" style="text-align:center;">حذف</th>-->
									<!--<th>Delete</th>-->
								</tr>
							</thead>
							<tbody>

								<?for($i=0;$i<count($data_drv);$i++)
								{
									$sql = "SELECT * from register_driver where iDriverId = '".$data_drv[$i]['iDriverId']."'";
									$db_cnt = $obj->MySQLSELECT($sql);
									$data_drv[$i]['namelastname'] = $db_cnt[0]['vName'] . " " . $db_cnt[0]['vLastName'];







									//echo "<pre>";print_r($db_cnt);echo "</pre>";
									?>
									<tr class="gradeA">
										<td><?php echo $data_drv[$i]['namelastname']; ?></td>


										<td style="direction: rtl;"><?php  echo jdate('d-F-Y H:i:s',strtotime($data_drv[$i]['dLoginDateTime']));?>
											<?php //echo $vehicles[$i]['dLoginDateTime']; ?>
										</td>


										<td style="direction: rtl;"><?php  echo jdate('d-F-Y H:i:s',strtotime($data_drv[$i]['dLogoutDateTime']));?>
											<?php //echo $vehicles[$i]['dLogoutDateTime']; ?>
										</td>

										<td style="direction: rtl;"><!--<?php  echo jdate('d-F-Y H:i:s',strtotime($data_drv[$i]['tDate']));?>

											<!--<td class="center" width="12%"  align="center" style="text-align:center;">




											<form name="delete_form" id="delete_form" method="post" action="" onsubmit="return confirm_delete()" class="margin0">
											<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $data_drv[$i]['iCompanyId'];?>">
											<input type="hidden" name="action" id="action" value="delete">
											<button class="btn btn-danger remove_btn001">
											<img src="img/delete-icon.png" alt="Delete">
										</button>
									</form>
								</td>-->
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
