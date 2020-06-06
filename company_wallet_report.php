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


// add by seyyed amir for manager password
$isManagerLogin = $_SESSION['sess_manager_login'];
if($isManagerLogin == false)
{
    header("Location: company-reports");
    die();
}
/////////////////

if($action!='')
{
	$startDate=$_REQUEST['startDate'];
	$endDate=$_REQUEST['endDate'];
    
    $gstartDate=  savar_request_date_to_gregorian($_REQUEST['startDate']);
	$gendDate=    savar_request_date_to_gregorian($_REQUEST['endDate']);
    
      $iDriverId = $_REQUEST['iDriverId'];
      $iUserId = $_REQUEST['iUserId'];
      $eDriverPaymentStatus = $_REQUEST['eDriverPaymentStatus'];
      $vTripPaymentMode = $_REQUEST['vTripPaymentMode'];
    
    /////////////////////////////////////////
    
    if($gstartDate!=''){
		$ssql.=" AND Date(`user_wallet`.dDate) >='".$gstartDate."'";
	}
	if($gendDate!=''){
		$ssql.=" AND Date(`user_wallet`.dDate) <='".$gendDate."'";
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
        $ssql AND dr.iCompanyId = '{$iCompanyId}' GROUP BY `user_wallet`.iUserWalletId";
	
    
    $db_wallet = $obj->MySQLSelect($sql);
	
    #var_dump($db_payment);
    #die($sql);
    
    $referrerWalletBalance = 0;
	for($i=0;$i<count($db_wallet);$i++) {
		$referrerWalletBalance += intval($db_wallet[$i]['iBalance']);
	}
    
    
    $ssql = '';
    if($gstartDate!=''){
		$ssql.=" AND Date(tTripRequestDate) >='".$gstartDate."'";
	}
	if($gendDate!=''){
		$ssql.=" AND Date(tTripRequestDate) <='".$gendDate."'";
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
    ///////////////////////
    
    $isCalculatedWaller = true;

}

if($ssql == '')
{
    $ssql=" AND Date(tEndDate) >='".date("Y-m-d")."'";
	$ssql.=" AND Date(tEndDate) <='".date("Y-m-d",strtotime("+1 day"))."'";
}


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
			  	<h2 class="header-page"><?php echo $langage_lbl['LBL_Transaction_HISTORY'];?></h2>
			  	
			  	<?php if($isManagerLogin == false) : ?>
                <div class="trips-page">
			  		<form name="masterloginform" action="" method="post" class="form-signin login-form-left">
                        <input type="hidden" name="action" id="action" value="masterlogin" />
                        <b>
                            <label><?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT']?></label>
                            <input name="managerPassword" placeholder="<?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT']?>" class="login-input" id="managerPassword" value="" required="" type="password">
                        </b>
                    </form>
                </div>
                <?php else : ?>
			  	
		  		<!-- trips page -->
			  	<div class="trips-page">
			  		<form name="search" action="" method="post" onSubmit="return checkvalid()">
                        <input type="hidden" name="action" id="action" value="search" />
                        <div class="Posted-date">
				      		<h3><?php echo $langage_lbl['LBL_DATE_TXT']; ?></h3>
				      		<span>
				      			<input type="text" id="dp4" name="startDate" placeholder="<?php echo $langage_lbl['LBL_FROM_DATE']?>" class="form-control" value=""/>
				      			<input type="text" id="dp5" name="endDate" placeholder="<?php echo $langage_lbl['LBL_TO_DATE']?>" class="form-control" value=""/>
					      	</span>
				      	</div>
				    	<div class="time-period">
				      		
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
                            
				      		<b><button class="driver-trip-btn"><?php echo $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button></b>
			      		</div>
                        
                        
                        
                        
                        
		      		</form>
		      		
			    	
			    <!-- -->
			    <?php //if(SITE_TYPE=="Demo"){?>
			    <!-- <div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
			      This feature will be enabled in the main product we will provide you.</span> </div>
			      <?php //}?> -->
			    <!-- -->
			  </div>
			  <!-- -->
			  
                  <?php if($isCalculatedWaller) : ?>

                    <div class="trips-table"> 

                        <form name="frmpayment" id="frmpayment" method="post" action="">
                          <input type="hidden" id="actionpayment" name="actionpayment" value="pay_driver">
                          <input type="hidden"  name="iTripId" id="iTripId" value="">
                          <input type="hidden"  name="ePayDriver" id="ePayDriver" value="">
                                            <table class="table table-striped table-bordered table-hover" id="dataTables-example123" <?php if($action == ""){?>style="display:none;"<?}else{?> style="display:;" <?}?>>
                                                <thead>
                                                    <tr>
                                                        <th style="width:20%">موضوع</th>
                                                        <th>مقدار</th>                  
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><?php echo $langage_lbl['LBL_WALLET_DISCOUNT']; ?></td>
                                                        <td><?php echo  $referrerWalletBalance ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $langage_lbl['LBL_DISCOUNTS_TXT']; ?></td>
                                                        <td><?php echo  $discountBalance ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $langage_lbl['LBL_Commision']; ?></td>
                                                        <td><?php echo  $commisionBalance ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $langage_lbl['LBL_Commision'] . ' ' . $langage_lbl['LBL_YOUR_SHARE']; ?></td>
                                                        <td><?php echo  $companyCommisionShare ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b><?php echo $langage_lbl['TXT_UNSETTELED_CREDIT']; ?></b></td>
                                                        <td><b><?php echo  $companyWallet ?></b></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                      </form>
                   </div>

                  <?php endif; ?>
		      
		      <?php endif; // if $isManagerLogin  ?>
		      		
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
