<?
include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('REPORTS') == false)
		die('you dont`t have permission...');

$tbl_name 	= 'trips';
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$abc = 'admin,company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

 //$generalobj->setRole($abc,$url);
$script='Company Wallet Report';

#echo "<pre>";print_r($_REQUEST);exit;


# Code For Settle Payment of Driver
$iCountryCode = '';
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

//Country
$sql = "select iCountryId,vCountry,vCountryCode from country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);

//Select dates
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


$startDate = $monday;
$endDate = $sunday;
$ssql = "";
$success = 0;

if($action != "" && $action == 'newsearch') {

	$iCountryCode = $_REQUEST['iCountryCode'];
	$startDate = date("Y-m-d",strtotime($_REQUEST['startDate']));
	$endDate = date("Y-m-d",strtotime($_REQUEST['endDate']));
	$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : "0";


	if($startDate!=''){
		$ssql.=" AND Date(`user_wallet`.dDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(`user_wallet`.dDate) <='".$endDate."'";
	}

    $companySql = '';

    if($iCompanyId != '0')
    {
        //$companySQL .= " AND trips.iVehicleTypeId IN (SELECT iVehicleTypeId FROM `vehicle_type` LEFT JOIN company ON `vehicle_type`.`vSavarArea` = `company`.`iAreaId` WHERE `company`.`iCompanyId` = $iCompanyId )";
        $companySQL .= " AND trips.iVehicleTypeId IN (SELECT iVehicleTypeId FROM `vehicle_type` LEFT JOIN company ON `vehicle_type`.`vSavarArea` = `company`.`iAreaId` WHERE `company`.`iCompanyId` = $iCompanyId )";
    }

    $sql = "SELECT `user_wallet`.* FROM `user_wallet`
        LEFT JOIN trips ON `user_wallet`.`iTripId` = `trips`.`iTripId`
        LEFT JOIN register_driver as dr ON trips.iDriverId = dr.iDriverId
        WHERE `user_wallet`.`iTripId` != 0
        AND `user_wallet`.`eFor` = 'Referrer' AND `user_wallet`.`eType` = 'Credit'
        $ssql AND dr.iCompanyId = {$iCompanyId} GROUP BY `user_wallet`.iUserWalletId";


    $db_wallet = $obj->MySQLSelect($sql);

    #var_dump($db_payment);
    #die($sql);

    $referrerWalletBalance = 0;
	for($i=0;$i<count($db_wallet);$i++) {
		$referrerWalletBalance += intval($db_wallet[$i]['iBalance']);
	}


    $ssql = '';
    if($startDate!=''){
		$ssql.=" AND Date(tTripRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(tTripRequestDate) <='".$endDate."'";
	}


    $sql = "SELECT SUM(fDiscount) as discount, SUM(fCommision) as commision FROM trips LEFT JOIN register_driver as dr ON trips.iDriverId = dr.iDriverId WHERE 1=1
    $ssql AND dr.iCompanyId = {$iCompanyId}";
    #die($sql);

    $db_discount = $obj->MySQLSelect($sql);

    $discountBalance = $db_discount[0]['discount'];
    $commisionBalance = $db_discount[0]['commision'];


    $sql = "SELECT iCompanyId,iPercentageShare FROM `company` WHERE iCompanyId = {$iCompanyId}";
    #die($sql);

    $db_company = $obj->MySQLSelect($sql);

    $percentageShare = $db_company[0]['iPercentageShare'];

    $companyCommisionShare = $commisionBalance * ( $percentageShare / 100);

    $companyWallet = $companyCommisionShare - ( $discountBalance + $referrerWalletBalance );


}



$sql = "SELECT * from company WHERE eStatus='Active'";
$db_company = $obj->MySQLSelect($sql);


?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

<!-- BEGIN HEAD-->
<head>
	<meta charset="UTF-8" />
    <title>گزارش مالی شرکت ها | ادمین</title>
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
		<?php include_once('header.php'); ?>
		<?php include_once('left_menu.php'); ?>

        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
				<div class="row">
				 <h2>گزارش مالی شرکت ها</h2>
				</div>
				<hr />
				<?php if($success == 1) { ?>
				 <div class="alert alert-success alert-dismissable">
					  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						Record(s) mark as settlled successful.
				 </div><br/>
				 <?php }elseif ($success == 2) { ?>
				   <div class="alert alert-danger alert-dismissable">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						"Mark as Settlled Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
				   </div><br/>
				 <?php } ?>

				<div class="">
					<div class="table-list">
						<div class="row">
								<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										گزارش مالی شرکت ها
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<div class="alert alert-error" id="alert" style="display: none;" >
												<strong>Oh snap!</strong>

												<p></p>
											</div>

											<form name="search" action="" method="post" onSubmit="return checkvalid()">
												<div class="Posted-date mytrip-page">
													<input type="hidden" name="action" value="newsearch" />
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
                                                        <div class="col-lg-6">
                                                                <select name="iCompanyId" id="iCompanyId" class="form-control" required>
                                                                    <option value="0">انتخاب شرکت</option>
                                                                <?php for ($j = 0; $j < count($db_company); $j++) { ?>
                                                                    <option value="<?php echo  $db_company[$j]['iCompanyId'] ?>" <?php if ($iCompanyId == $db_company[$j]['iCompanyId']) { ?> selected <?php } ?>><?php echo  $db_company[$j]['vCompany'] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                        </div>
                                                    </span>
													<span>
													<input type="text" id="dp4" name="startDate" placeholder="از تاریخ" class="form-control" value="" required/>
													<input type="text" id="dp5" name="endDate" placeholder="تا تاریخ" class="form-control" value="" required/>
													<b><button class="driver-trip-btn" >جست و جو</button>
														<button type="button" onClick="redirectpaymentpage('driver_pay_report.php');" class="driver-trip-btn">پاک سازی لیست</button></b>
													</span>

													<?php if(count($db_payment) > 0){ ?>
													<span><b><button type="button" class="driver-trip-btn" onclick="exportlist();">خروجی</button></b>
													</span>
													<?php } ?>




												</div>
											</form>

										</div>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>


                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    گزارش مالی شرکت ها
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive" style="direction:rtl;">
                                        <div style="font-size:28pt">کیف پول : <?php echo  $referrerWalletBalance ?></div>
                                        <div style="font-size:28pt">تخفیف : <?php echo  $discountBalance ?></div>
                                        <div style="font-size:28pt">کمیسیون : <?php echo  $commisionBalance ?></div>
                                        <div style="font-size:28pt">انتشار شرکت : <?php echo  $companyCommisionShare ?></div>
                                        <br />
                                        <div style="font-size:28pt">شرکت : <?php echo  $companyWallet ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


			</div>
        </div>
       <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->

	<form name="_submit_this" id="_submit_this" action="" >

	</form>


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
        $(document).ready(function () {
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

		 function setRideStatus(actionStatus) {
			 window.location.href = "trip.php?type="+actionStatus;
		 }
		 function todayDate()
		 {
			 $("#dp4").val('<?php echo  $Today;?>');
			 $("#dp5").val('<?php echo  $Today;?>');
		 }
		 function reset() {
			location.reload();

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
				 alert("From date should be lesser than To date.");
				 return false;
			 }
		 }

     function redirectpaymentpage(url)
     {
        //$("#frmsearch").reset();
        // document.getElementById("action").value = '';
        // document.getElementById("frmsearch").reset();
        window.location=url;
     }

     function getCheckCount(frmpayment)
     {
      	var x=0;
      	var threasold_value=0;
      	for(i=0;i < frmpayment.elements.length;i++)
      	{	if ( frmpayment.elements[i].checked == true)
      			{x++;}
      	}
      	return x;
     }


     function Paytodriver(){
        y = getCheckCount(document.frmpayment);

        if(y>0)
      	{
          ans = confirm("Are you sure you want to Pay To Driver?");
          if(ans == false)
          {
             return false;
          }
          $("#ePayDriver").val('Yes');
		  $("#frmpayment").attr('action','');
          document.frmpayment.submit();
        }
        else{
          alert("Select record for Pay To Driver");
          return false;
        }
      }

		function exportlist(){
			$("#actionpay").val('export');
			$("#frmpayment").attr('action',"export_driver_pay_details.php");
			document.frmpayment.submit();
		}

		function search_filters() {
			document.search.action="";
			document.search.submit();
		}


     /*$('#dataTables-example').DataTable( {
        paging: false
      } );*/

    </script>
</body>
	<!-- END BODY-->
</html>
