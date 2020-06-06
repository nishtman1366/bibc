<?

include_once('common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
$script="Reports";
$tbl_name 	= 'register_driver';
 $generalobj->check_member_login();
 $abc = 'admin,company';
 $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 $generalobj->setRole($abc,$url);
$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');


// add by seyyed amir for manager password
$isManagerLogin = $_SESSION['sess_manager_login'];
if($isManagerLogin == false)
{
    header("Location: company-reports");
    die();
}
/////////////////



$ssql='';

        // ADDED BY SEYYED AMIR
        $iCompanyId = $_SESSION['sess_iUserId'];
        $sql = "SELECT `iCompanyId` FROM `company` where `iParentId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
		$comp_childs = $obj->MySQLSelect($sql);
        $comp_list = $iCompanyId;
        
        foreach($comp_childs as $comp)
        {
            $comp_list .= ',' . $comp['iCompanyId'];
        }

        ////////////////////


$sql = "select * from register_driver WHERE iCompanyId IN (".$comp_list.") AND eStatus = 'active'";
$db_driver_app = $obj->MySQLSelect($sql);


if($action!='')
{
	$startDate=$_REQUEST['startDate'];
	$iCompanyId = $_REQUEST['iCompanyId'];
	$endDate=$_REQUEST['endDate'];
    
    $gstartDate=  savar_request_date_to_gregorian($_REQUEST['startDate']);
	$gendDate=    savar_request_date_to_gregorian($_REQUEST['endDate']);
    
  $iDriverId = $_REQUEST['iDriverId'];
  $iUserId = $_REQUEST['iUserId'];
  $eDriverPaymentStatus = $_REQUEST['eDriverPaymentStatus'];
  $vTripPaymentMode = $_REQUEST['vTripPaymentMode'];
  
	if($startDate!=''){
		$ssql.=" AND Date(tEndDate) >='".$gstartDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(tEndDate) <='".$gendDate."'";
	}
  
  if($iCompanyId!=''){
      if($iDriverId!=''){
      $ssql.=" AND tr.iDriverId = '".$iDriverId."' AND rd.iCompanyId IN(". $comp_list .")";
    }else{
      $sql = "select iDriverId from register_driver WHERE iCompanyId IN (".$comp_list.") ";
		  $db_driver2 = $obj->MySQLSelect($sql);
      if(count($db_driver2)>0)
  		{
  			for($i=0;$i<count($db_driver2);$i++)
  			{
  				 $id.=$db_driver2[$i]['iDriverId'].",";
  			}
  			$id=rtrim($id,",");
  		  $ssql.=" AND tr.iDriverId IN($id)";
  		}else{
        $ssql.=" AND tr.iDriverId = ''";
      }                        
    }
	}else{
    if($iDriverId!=''){
		  $ssql.=" AND tr.iDriverId = '".$iDriverId."'";
	  }
  }
  
	
	if($iUserId!=''){
		$ssql.=" AND tr.iUserId = '".$iUserId."'";
	}
  
  if($vTripPaymentMode!=''){
     $ssql.=" AND tr.vTripPaymentMode = '".$vTripPaymentMode."'";
		/*if($vTripPaymentMode == 'Mbirr'){
      $ssql.=" AND tr.vTripPaymentMode = 'Cash' AND eMBirr = 'Yes'";
    }else{
      $ssql.=" AND tr.vTripPaymentMode = '".$vTripPaymentMode."'";
    }*/  
	}
  
  if($eDriverPaymentStatus!=''){
		$ssql.=" AND tr.eDriverPaymentStatus = '".$eDriverPaymentStatus."'";
	}
}

if($ssql == '')
{
    $ssql=" AND Date(tEndDate) >='".date("Y-m-d")."'";
	$ssql.=" AND Date(tEndDate) <='".date("Y-m-d",strtotime("+1 day"))."'";
}

//$sql = "SELECT * from trips LEFT JOIN register_drivers WHERE 1=1 ".$ssql." ORDER BY iTripId DESC";
$sql = "SELECT tr.*,c.vCompany FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId  WHERE 1 ".$ssql." AND rd.iCompanyId in (".$comp_list.") ORDER BY tr.iTripId DESC";  

#die($sql);

$db_trip = $obj->MySQLSelect($sql);



$Today=jdate('Y-m-d');
$tdate=jdate("d")-1;
$mdate=jdate("d");
$Yesterday = jdate("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

$curryearFDate = jdate("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = jdate("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = jdate("Y-m-d",mktime(0,0,0,'1','1',date("Y")-1));
$prevyearTDate = jdate("Y-m-d",mktime(0,0,0,"12","31",date("Y")-1));

$currmonthFDate = jdate("Y-m-d",mktime(0,0,0,date("m"),date("d")-$tdate,date("Y")));
$currmonthTDate = jdate("Y-m-d",mktime(0,0,0,date("m")+1,date("d")-$mdate,date("Y")));
$prevmonthFDate = jdate("Y-m-d",mktime(0,0,0,date("m")-1,date("d")-$tdate,date("Y")));
$prevmonthTDate = jdate("Y-m-d",mktime(0,0,0,date("m"),date("d")-$mdate,date("Y")));

$monday = jdate( 'Y-m-d', strtotime( 'sunday this week -1 week' ) );
$sunday = jdate( 'Y-m-d', strtotime( 'saturday this week' ) );

$Pmonday = jdate( 'Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = jdate( 'Y-m-d', strtotime('saturday this week -1 week'));





$actionpayment = $_REQUEST['actionpayment'];
$ePayDriver = $_REQUEST['ePayDriver'];
if($actionpayment == "pay_driver" && $ePayDriver == "Yes"){
#echo "<pre>";print_r($_REQUEST);exit;
   $iTripId = $_REQUEST['iTripId'];
   for($k=0;$k<count($iTripId);$k++){
     $sql = "SELECT ePaymentDriverStatus from payments WHERE iTripId = '".$iTripId[$k]."' and ePaymentDriverStatus = 'UnPaid'";
     $db_pay = $obj->MySQLSelect($sql);
     if(count($db_pay) > 0){
       $query = "UPDATE payments SET ePaymentDriverStatus = 'Paid' WHERE iTripId = '" .$iTripId[$k]. "'";
       $obj->sql_query($query);
       
       $query = "UPDATE trips SET eDriverPaymentStatus = 'Settelled', ePayment_request = 'Yes' WHERE iTripId = '" .$iTripId[$k]. "'";
       $obj->sql_query($query);
     }else{
       $query = "UPDATE trips SET eDriverPaymentStatus = 'Settelled', ePayment_request = 'Yes' WHERE iTripId = '" .$iTripId[$k]. "'";
       $obj->sql_query($query);
     }
   }
   header("Location:payment_report.php?".$_SERVER['QUERY_STRING']);
   exit;
}


?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $SITE_NAME?> | Trips</title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    

    <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
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
			  	
		  		<!-- trips page -->
			  	<div class="trips-page">
			  		<form name="search" action="" method="post" onSubmit="return checkvalid()">
                        <input type="hidden" name="action" id="action" value="search" />
                        <div class="Posted-date">
				      		<h3><?php echo $langage_lbl['LBL_COMPANY_TRIP_SEARCH_RIDES_POSTED_BY_DATE']; ?></h3>
				      		<span>
				      			<input type="text" id="dp4" name="startDate" placeholder="<?php echo $langage_lbl['LBL_FROM_DATE']?>" class="form-control" value=""/>
				      			<input type="text" id="dp5" name="endDate" placeholder="<?php echo $langage_lbl['LBL_TO_DATE']?>" class="form-control" value=""/>
					      	</span>
				      	</div>
				    	<div class="time-period">
				      		<h3><?php echo $langage_lbl['LBL_COMPANY_TRIP_SEARCH_RIDES_POSTED_BY_TIME_PERIOD']; ?></h3>
				      		<span>
								<a onClick="return todayDate('dp4','dp5');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></a>
								<a onClick="return yesterdayDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></a>
								<a onClick="return currentweekDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></a>
								<a onClick="return previousweekDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></a>
								<a onClick="return currentmonthDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></a>
								<a onClick="return previousmonthDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></a>
								<a onClick="return currentyearDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></a>
								<a onClick="return previousyearDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></a>
				      		</span> 
                            
				      		<span>
                            
                             <style>
                             .search-select .caret{display:none}
                             </style>
                             <span style="width: 18%;margin-top:0px" class="search-select">
                            <select name="iDriverId" id="iDriverId" class="form-control input-sm driver-trip-detail-select selectpicker" style="display:table-row-group;" data-show-subtext="true" data-live-search="true">
                                <option value="">Search By  <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> Name</option>
                                <?for($j=0;$j<count($db_driver_app);$j++){?>
                                <option value="<?php echo $db_driver_app[$j]['iDriverId'];?>" <?php if($iDriverId == $db_driver_app[$j]['iDriverId']){?>selected <?}?>><?php echo $db_driver_app[$j]['vName'];?> <?php echo $db_driver_app[$j]['vLastName'];?></option>
                                <?}?>
                            </select>
                            </span>
                                
                           
                                
                            
                            
                           <select name="eDriverPaymentStatus" id="eDriverPaymentStatus" class="form-control input-sm" style="width:18%;display:table-row-group;">
                            <option value="">Search By  <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> Payment Status</option>
                            <option value="Settelled" <?php if($eDriverPaymentStatus == "Settelled"){?>selected <?}?>>Settelled</option>
                            <option value="Unsettelled" <?php if($eDriverPaymentStatus == "Unsettelled"){?>selected <?}?>>Unsettelled</option>
                          </select>
                                
                            </span>
				      	
                            
                            
				      		<b><button class="driver-trip-btn"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button></b> 
			      		</div>
                        
                        
                        
                        
                        
		      		</form>
			    	<div class="trips-table"> 
			      		
                       
                        
                        
                        <form name="frmpayment" id="frmpayment" method="post" action="">
                          <input type="hidden" id="actionpayment" name="actionpayment" value="pay_driver">
                          <input type="hidden"  name="iTripId" id="iTripId" value="">
                          <input type="hidden"  name="ePayDriver" id="ePayDriver" value="">
 											<table class="table table-striped table-bordered table-hover" id="dataTables-example123" <?php if($action == ""){?>style="display:none;"<?}else{?> style="display:;" <?}?>>
												<thead>
													<tr>
														<th># </th>
														<th>نام راننده</th>
														<th>نام مسافر</th>
														<th>تاریخ سفر</th>
														<!--<th>Address</th>-->
														<th>جمع هزینه</th>
														<th>کمیسیون</th>
														<th>تخفیف</th>
														<th>کیف پول</th>
														<th>حق راننده</th>
														<th>وضعیت سفر</th>
														<th>نوع پرداخت</th>
														<th>پرداخت راننده</th> 
														                            
													</tr>
												</thead>
												<tbody>
													<?
                          if(count($db_trip) > 0){
                            $tot_fare = 0.00;
                            $tot_site_commission = 0.00;
                            $tot_promo_discount = 0.00;
                            $tot_driver_refund = 0.00;
                            $tot_wallentPayment = 0.00;
                          	
  							for($i=0;$i<count($db_trip);$i++)
                            {
                              $sq="select concat(vName,' ',vLastName) as drivername from register_driver where iDriverId='".$db_trip[$i]['iDriverId']."'";
                              $name=$obj->MySQLSelect($sq);
							  
                              $db_trip[$i]["drivername"]=$name[0]["drivername"];
                              $totalfare = $db_trip[$i]['fTripGenerateFare'];
                              $site_commission = $db_trip[$i]['fCommision'];
                              $promocodediscount = $db_trip[$i]['fDiscount'];
                              $wallentPayment = $db_trip[$i]['fWalletDebit'];
                              $driver_payment = $totalfare+$promocodediscount-$site_commission;
                              
                              $tot_fare = $tot_fare+$totalfare;
                              $tot_site_commission = $tot_site_commission+$site_commission;
                              $tot_promo_discount = $tot_promo_discount+$promocodediscount;
                              $tot_wallentPayment = $tot_wallentPayment+$wallentPayment;
                              //[DISABLE BY SEYYED AMIR]$tot_driver_refund = $tot_driver_refund+$driver_payment;
                              // add by seyyed amir
                                $tot_driver_refund = $tot_driver_refund+($wallentPayment + $promocodediscount - $site_commission);
                             
                                 $paymentmode = $db_trip[$i]['vTripPaymentMode'];
								 
								 $sq="select concat(vName,' ',vLastName) as passanger from register_user where iUserId='".$db_trip[$i]['iUserId']."'";
                              $name2=$obj->MySQLSelect($sq);
                               
                              $db_trip[$i]["passanger"]=$name2[0]["passanger"];
  													?>
  															<tr class="gradeA">
  															  <td><?php echo $db_trip[$i]['vRideNo'];?></td>
															  <td><?php echo $db_trip[$i]['drivername'];?></td>
															  <td><?php echo $db_trip[$i]['passanger'];?></td>
															  <td><?php echo  jdate('d-m-Y',strtotime($db_trip[$i]['tTripRequestDate']));?></td>
																<td align="center"><?php if($db_trip[$i]['fTripGenerateFare'] != "" && $db_trip[$i]['fTripGenerateFare'] != 0) { echo $generalobj->trip_currency($db_trip[$i]['fTripGenerateFare']); }else { echo '-'; }?></td>
																<td align="center"><?php if($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0) { echo $generalobj->trip_currency($db_trip[$i]['fCommision']); }else { echo '-'; } ?></td>
																<td align="center"><?php if($db_trip[$i]['fDiscount'] != "" && $db_trip[$i]['fDiscount'] != 0) { echo $generalobj->trip_currency($db_trip[$i]['fDiscount']); }else { echo '-'; }?></td>
																<td align="center"><?php if($db_trip[$i]['fWalletDebit'] != "" && $db_trip[$i]['fWalletDebit'] != 0) { echo $generalobj->trip_currency($db_trip[$i]['fWalletDebit']); }else { echo '-'; }?></td>
															  <td align="center"><?php if($driver_payment != "" && $driver_payment != 0) { echo $generalobj->trip_currency($driver_payment); }else { echo '-'; }?></td>
																<td><?php echo $db_trip[$i]['iActive'];?></td>
															  <td><?php echo $paymentmode;?></td>
															  <td><?php echo $db_trip[$i]['eDriverPaymentStatus'];?></td>
															  
  															</tr>
  													<?php } ?>
                            <tr class="gradeA">
                              <td colspan="10" align="right">کل هزینه سفر</td>
                              <td align="right" colspan="2"><?php echo $generalobj->trip_currency($tot_fare);?></td>
                            </tr>
                            <tr class="gradeA">
                              <td colspan="10" align="right">کل کمیسیون</td>
                              <td  align="right" colspan="2"><?php echo $generalobj->trip_currency($tot_site_commission);?></td>
                            </tr>
                            <tr class="gradeA">
                              <td colspan="10" align="right">کل تخفیف ها</td>
                              <td  align="right" colspan="2"><?php echo $generalobj->trip_currency($tot_promo_discount);?></td>
                            </tr>
							<tr class="gradeA">
                              <td colspan="10" align="right">ول پرداخت ها از کیف پول</td>
                              <td  align="right" colspan="2"><?php echo $generalobj->trip_currency($tot_wallentPayment);?></td>
                            </tr>
                            <tr class="gradeA">
                              <td colspan="10" align="right">کل پرداخت به راننده</td>
                              <td  align="right" colspan="2"><?php echo $generalobj->trip_currency($tot_driver_refund);?></td>
                            </tr>
                            <tr class="gradeA">
                              <td colspan="12" align="right"><div class="row payment-report-button">
                            <span>
                            <a onClick="javascript:Paytodriver(); return false;" href="javascript:void(0);"><button class="btn btn-primary ">Mark As Settelled</button></a>
        					</span>
        					</div></td>
                            </tr>
                          
                          <?}else{?>
                          <tr class="gradeA">
                               <td colspan="12" style="text-align:center;"> No Payment Details Found.</td>
                          </tr>
                          <?}?>
                          

												</tbody>
											</table>
                      </form>
                        
                        
                        
                        
                        
                        
                        
			       </div>
			    <!-- -->
			    <?php //if(SITE_TYPE=="Demo"){?>
			    <!-- <div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
			      This feature will be enabled in the main product we will provide you.</span> </div>
			      <?php //}?> -->
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
	<script src="assets/js/jquery.ui.datepicker-cc-fa.js"></script>
    <script type="text/javascript">
         $(document).ready(function () {
         	$( "#dp4" ).datepicker({
         		dateFormat: "yy-mm-dd",
         		changeYear: true,
     		  	changeMonth: true,
     		  	yearRange: "-100:+10"
         	});
         	$( "#dp5" ).datepicker({
         		dateFormat: "yy-mm-dd",
         		changeYear: true,
     		  	changeMonth: true,
     		  	yearRange: "-100:+10"
         	});
			 if('<?php echo $startDate?>'!=''){
				 $("#dp4").val('<?php echo $startDate?>');
				 //$("#dp4").datepicker('refresh');
			 }
			 if('<?php echo $endDate?>'!=''){
				 $("#dp5").val('<?php echo  $endDate;?>');
				 //$("#dp5").datepicker('refresh');
			 }
             $('#dataTables-example').dataTable({
					"language": {
								<?php echo Datatablelang?>
						},
				 });    
             // formInit();
         });
		 function todayDate()
		 {
			 $("#dp4").val('<?php echo  $Today;?>');
			 $("#dp5").val('<?php echo  $Today;?>');
		 }
		 function yesterdayDate()
		 {
			 $("#dp4").val('<?php echo  $Yesterday;?>');
			 $("#dp5").val('<?php echo  $Yesterday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');			 
		 }
		 function currentweekDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $monday;?>');			 
			 $("#dp5").val('<?php echo  $sunday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousweekDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $Pmonday;?>');
			 $("#dp5").val('<?php echo  $Psunday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function currentmonthDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $currmonthFDate;?>');
			 $("#dp5").val('<?php echo  $currmonthTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousmonthDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $prevmonthFDate;?>');
			 $("#dp5").val('<?php echo  $prevmonthTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function currentyearDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $curryearFDate;?>');
			 $("#dp5").val('<?php echo  $curryearTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousyearDate(dt,df)
		 {
			 $("#dp4").val('<?php echo  $prevyearFDate;?>');
			 $("#dp5").val('<?php echo  $prevyearTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
	 	function checkvalid(){
			 if($("#dp5").val() < $("#dp4").val()){
				 //bootbox.alert("<h4>From date should be lesser than To date.</h4>");
			 	bootbox.dialog({
				 	message: "<h4>From date should be lesser than To date.</h4>",
				 	buttons: {
				 		danger: {
				      		label: "OK",
				      		className: "btn-danger"
				   	 	}
			   	 	}
		   	 	});
			 	return false;
		 	}
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
