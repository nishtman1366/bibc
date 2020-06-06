<?

include_once('../common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
	include_once('savar_check_permission.php');
	if(checkPermission('REPORTS') == false)
		die('you dont`t have permission...');


	$tbl_name 	= 'register_driver';
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$script='CancelledTrips';
$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$ssql='';
if($action!='')
{
	 $iDriverId =$_REQUEST['iDriverId'];
	if($iDriverId!=''){
		$ssql.=" AND t.iDriverId='".$iDriverId."'";
	}
	$startDate=$_REQUEST['startDate'];

	$endDate=$_REQUEST['endDate'];

	if($startDate!=''){

		$ssql.=" AND Date(t.tEndDate) >='".$startDate."'";
	}
	if($endDate!=''){

		$ssql.=" AND Date(t.tEndDate) <='".$endDate."'";
	}
}

$cmp_ssql = "";
if(SITE_TYPE =='Demo'){
	$cmp_ssql = " And t.tEndDate > '".WEEK_DATE."'";
}

$sql = "SELECT t.*,concat(rd.vName,' ',rd.vLastName) as dName from trips t left join register_driver rd on t.iDriverId=rd.iDriverId WHERE 1 AND (t.iActive = 'Canceled' OR t.eCancelled='yes')".$ssql.$cmp_ssql."
ORDER BY t.iTripId DESC limit 5000";
$db_trip = $obj->MySQLSelect($sql);

$sql = "select * from register_driver WHERE 1=1 order by vName  limit 5000";
$db_driver_app = $obj->MySQLSelect($sql);

$Today=Date('Y-m-d');
$tdate=date("d")-1;
$mdate=date("d");
$Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

$curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")-1));
$prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")-1));

$currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$tdate,date("Y")));
$currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m")+1,date("d")-$mdate,date("Y")));
$prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d")-$tdate,date("Y")));
$prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$mdate,date("Y")));

$monday = date( 'Y-m-d', strtotime( 'sunday this week -1 week' ) );
$sunday = date( 'Y-m-d', strtotime( 'saturday this week' ) );

$Pmonday = date( 'Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date( 'Y-m-d', strtotime('saturday this week -1 week'));

?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8" />
    <title>سفرهای کنسل شده | ادمین </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<meta content="" name="keywords" />
	<meta content="" name="description" />
	<meta content="" name="author" />
    <?php include_once('global_files.php');?>

    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 " >

    <!-- MAIN WRAPPER -->
    <div id="wrap">
		<?php include_once('header2.php'); ?>
		<?php include_once('left_menu.php'); ?>

        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
				<div class="row">
                <div class="col-lg-12">
				 <h2>سفر های کنسل شده</h2>
                 </div>
				</div>
				<hr />
                <div class="">
					<div class="table-list">
						<div class="row">
								<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading driver-neww1 driver-neww2">
										<b>سفر های کنسل شده</b>
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<div class="alert alert-error" id="alert" style="display: none;" >
												<strong>Oh snap!</strong>

												<p></p>
											</div>
											<form name="search" action="" method="post" id="cancel_trip" onSubmit="return checkvalid()">
												<div class="Posted-date mytrip-page mytrip-page-select">
													<input type="hidden" name="action" value="search" />
													<h3>انتخاب مدت زمان</h3>
													<span>
													<a onClick="return todayDate('dp4','dp5');">امروز</a>
													<a onClick="return yesterdayDate('dFDate','dTDate');">روز گذشته</a>
													<a onClick="return currentweekDate('dFDate','dTDate');">هفته جاری</a>
													<a onClick="return previousweekDate('dFDate','dTDate');">هفته گذشته</a>
													<a onClick="return currentmonthDate('dFDate','dTDate');">ماه جاری</a>
													<a onClick="return previousmonthDate('dFDate','dTDate');">ماه گذشته</a>
													<a onClick="return currentyearDate('dFDate','dTDate');">سال جاری</a>
													<a onClick="return previousyearDate('dFDate','dTDate');">سال گذشته</a>
													</span>
													<span>
													<input type="text" id="dp4" name="startDate" placeholder="از تاریخ" class="form-control" value=""/>
													<input type="text" id="dp5" name="endDate" placeholder="تا تاریخ" class="form-control" value=""/>
                                                    <select name="iDriverId" id="iDriverId" class="form-control input-sm driver-trip-detail-select" style="display:table-row-group;">
														<option value="">جست و جو بر اساس نام راننده</option>
					                                <?for($j=0;$j<count($db_driver_app);$j++){?>
					                                <option value="<?php echo $db_driver_app[$j]['iDriverId'];?>" <?php if($iDriverId== $db_driver_app[$j]['iDriverId']){?>selected <?}?>><?php echo $db_driver_app[$j]['vName'];?> <?php echo $db_driver_app[$j]['vLastName'];?></option>
					                                <?}?>
					                              </select>
																				  <input type="text"  title="Enter Mobile Number." class="form-control add-book-input" name="search_driver_ajax2"  id="search_driver_ajax2" value="<?php echo  $vPhone; ?>" placeholder="نام یا نام خانوادگی یا شماره موبایل راننده را وارد کنید"  style="background:none;">
																				<a class="btn btn-sm btn-info" id="search_driver_ajax" >جست و جو</a>

													<b><button class="driver-trip-btn">جست و جو</button>
														<button onClick="resetBtn();" class="driver-trip-btn">پاک سازی لیست</button></b>

													</span>
												</div>

											</form>
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th><?php echo $langage_lbl_admin['LBL_TRIP_DATE_ADMIN'];?> </th>
														<th>کنسل شده توسط</th>
														<th>دلیل کنسلی</th>
														<th>نام راننده</th>
														<th>شماره سفر</th>
														<th>آدرس</th>
													</tr>
												</thead>
												<tbody>
													<?
													for($i=0;$i<count($db_trip);$i++)
													{
														$vCancelReason = $db_trip[$i]['vCancelReason'];
														$trip_cancel = ($vCancelReason != '')? $vCancelReason: '--';
														$eCancelled = $db_trip[$i]['eCancelled'];
														$CanceledBy = ($eCancelled == 'Yes' && $vCancelReason != '' )? 'Driver': 'Passenger';

													 ?>

													 <?php if (count($db_trip) > 0): ?>
														 <tr class="gradeA">
 															<td><?php echo  date('d-F-Y',strtotime($db_trip[$i]['tTripRequestDate']));?></td>
 															<td align="center">
 																<?php echo $CanceledBy;?>

 															</td>
 															<td align="center">
 																<?php echo $trip_cancel;?>
 															</td>
 															<td>
 																<?php echo $db_trip[$i]['dName']?>
 															</td>
 															<td>
 																<a href="javascript:void(0);" onclick='javascript:window.open("invoice.php?iTripId=<?php echo $db_trip[$i]['iTripId']?>","_blank")'; ><?php echo $db_trip[$i]['vRideNo'];?></a>
 															</td>
 															<td width="30%" data-order="<?php echo $db_trip[$i]['iTripId']?>"><?php echo $db_trip[$i]['tSaddress'].' -> '.$db_trip[$i]['tDaddress'];?></td>

 														</tr>
													 <?php else: ?>
														 <tr class="gradeA">
															 </tr>
													 <?php endif; ?>

												 <?php } ?>

												</tbody>
											</table>
										</div>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
                <div class="clear"></div>
			</div>
        </div>
       <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->

	<?php include_once('footer.php');?>
	<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
	<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
	<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
	<script src="../assets/js/jquery-ui.min.js"></script>
	<script src="../assets/plugins/uniform/jquery.uniform.min.js"></script>
	<script src="../assets/plugins/inputlimiter/jquery.inputlimiter.1.3.1.min.js"></script>
	<script src="../assets/plugins/chosen/chosen.jquery.min.js"></script>
	<script src="../assets/plugins/colorpicker/js/bootstrap-colorpicker.js"></script>
	<script src="../assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
	<script src="../assets/plugins/validVal/js/jquery.validVal.min.js"></script>
	<script src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
	<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script>
	<script src="../assets/plugins/autosize/jquery.autosize.min.js"></script>
	<script src="../assets/plugins/jasny/js/bootstrap-inputmask.js"></script>
	<script src="../assets/js/formsInit.js"></script>
    <script>





		$('#search_driver_ajax').on('click', function () {
		  $('#iDriverId')
		      .find('option')
		      .remove()

		  ;
		    var phone = $('#search_driver_ajax2').val();
		    $.ajax({
		        type: "POST",
		        url: 'search_driver_ajax.php',
		        data: 'phone=' + phone,
		        success: function (dataHtml)
		        {
		console.log(dataHtml);
		            if (dataHtml != "" || dataHtml != ":::~" || dataHtml != " ") {
		              var result;
		                var result1 = dataHtml.split('~');
		                //alert(result1.length);
		                for(i = 0; i<result1.length-1;i++)
		                {
		                  result = result1[i].split(':');
		                  //alert(result1[1]);
		                                          $('#iDriverId')
		                                              .find('option')
		                                                              .end()
		                                              .append('<option value="' + result[2] + '">' + result[0] + ' ' + result[1] + '</option>')
		                                              .val(result[2])
		                                          ;
		                }


		  }else {
		    $('#iDriverId')
		        .find('option')
		        .remove()
		        .end()
		        .append('<option value="داده ای یافت نشد">داده ای یافت نشد</option>')
		        .val('داده ای یافت نشد')
		    ;
		}
		}
		});
		});








         $(document).ready(function () {
         	 //$("#dp4").val('');
         	 //$("#dp5").val('');
			 if('<?php echo $startDate?>'!=''){

				 $("#dp4").val('<?php echo $startDate?>');

				 $("#dp4").datepicker('update' , '<?php echo $startDate?>');
			 }
			 if('<?php echo $endDate?>'!=''){
				 $("#dp5").datepicker('update' , '<?php echo  $endDate;?>');
				 $("#dp5").val('<?php echo  $endDate;?>');
			 }
             $('#dataTables-example').dataTable({
				  "order": [[ 0, "desc" ]]
				 });
			 formInit();
         });


		 function todayDate()
		 {
			 $("#dp4").val('<?php echo  $Today;?>');
			 $("#dp5").val('<?php echo  $Today;?>');
		 }
		 function resetBtn() {


		 	document.getElementById("dp4").value = "";
		 	document.getElementById("dp5").value = "";
		 	document.getElementById("iDriverId").value = "";
			$("#cancel_trip").onSubmit();

		}
		 function yesterdayDate()
		 {
			 $("#dp4").val('<?php echo  $Yesterday;?>');
			 $("#dp4").datepicker('update' , '<?php echo  $Yesterday;?>');
			 $("#dp5").datepicker('update' , '<?php echo  $Yesterday;?>');
			 $("#dp4").change();
			 $("#dp5").change();
			 $("#dp5").val('<?php echo  $Yesterday;?>');
		 }
		 function currentweekDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $monday;?>');
			 $("#dp4").datepicker('update' , '<?php echo  $monday;?>');
			 $("#dp5").datepicker('update' , '<?php echo  $sunday;?>');
			 $("#dp5").val('<?php echo  $sunday;?>');
		 }
		 function previousweekDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $Pmonday;?>');
			 $("#dp4").datepicker('update' , '<?php echo  $Pmonday;?>');
			 $("#dp5").datepicker('update' , '<?php echo  $Psunday;?>');
			 $("#dp5").val('<?php echo  $Psunday;?>');
		 }
		 function currentmonthDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $currmonthFDate;?>');
			 $("#dp4").datepicker('update' , '<?php echo  $currmonthFDate;?>');
			 $("#dp5").datepicker('update' , '<?php echo  $currmonthTDate;?>');
			 $("#dp5").val('<?php echo  $currmonthTDate;?>');
		 }
		 function previousmonthDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $prevmonthFDate;?>');
			 $("#dp4").datepicker('update' , '<?php echo  $prevmonthFDate;?>');
			 $("#dp5").datepicker('update' , '<?php echo  $prevmonthTDate;?>');
			 $("#dp5").val('<?php echo  $prevmonthTDate;?>');
		 }
		 function currentyearDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $curryearFDate;?>');
			 $("#dp4").datepicker('update' , '<?php echo  $curryearFDate;?>');
			 $("#dp5").datepicker('update' , '<?php echo  $curryearTDate;?>');
			 $("#dp5").val('<?php echo  $curryearTDate;?>');
		 }
		 function previousyearDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $prevyearFDate;?>');
			 $("#dp4").datepicker('update' , '<?php echo  $prevyearFDate;?>');
			 $("#dp5").datepicker('update' , '<?php echo  $prevyearTDate;?>');
			 $("#dp5").val('<?php echo  $prevyearTDate;?>');
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
