<?
	include_once('common.php');
	require_once(TPATH_CLASS .'savar/jalali_date.php');
	$tbl_name 	= 'register_user';
	$script="Trips";
	$generalobj->check_member_login();
	$abc = 'admin,rider';
	$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$generalobj->setRole($abc,$url);
	$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
	$ssql='';
	if($action!='')
	{
		$startDate=  $_REQUEST['startDate'];
		$endDate=    $_REQUEST['endDate'];
		$gstartDate=  savar_request_date_to_gregorian($_REQUEST['startDate']);
		$gendDate=    savar_request_date_to_gregorian($_REQUEST['endDate']);
		
		if($gstartDate!=''){
			$ssql.=" AND Date(t.tEndDate) >='".$gstartDate."'";
		}
		if($gendDate!=''){
			$ssql.=" AND Date(t.tEndDate) <='".$gendDate."'";
		}
	}

if($_SESSION['sess_user']== "driver")
{
  $sql = "SELECT * FROM register_".$_SESSION['sess_user']." WHERE iDriverId='".$_SESSION['sess_iUserId']."'";
  $db_booking = $obj->MySQLSelect($sql);

  $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='".$db_booking[0]['vCurrencyDriver']."'";
  $db_curr_ratio = $obj->MySQLSelect($sql);
}
else
{
  $sql = "SELECT * FROM register_user WHERE iUserId='".$_SESSION['sess_iUserId']."'";
  $db_booking = $obj->MySQLSelect($sql);  

  $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='".$db_booking[0]['vCurrencyPassenger']."'";
  $db_curr_ratio = $obj->MySQLSelect($sql);
}
$tripcursymbol=$db_curr_ratio[0]['vSymbol'];
$tripcur=$db_curr_ratio[0]['Ratio'];
$tripcurname=$db_curr_ratio[0]['vName'];
$tripcurthholsamt=$db_curr_ratio[0]['fThresholdAmount'];

	$sql = "SELECT t.*, u.vName, u.vLastName,t.tEndDate,t.iActive, t.iFare,t.fRatioPassenger,t.vCurrencyPassenger, d.iDriverId, t.vRideNo, t.tSaddress,t.eType, d.vName AS name, d.vLastName AS lname,t.eCarType,t.iTripId,vt.vVehicleType_".$_SESSION['sess_lang']." as vVehicleType
	FROM register_user u
	RIGHT JOIN trips t ON u.iUserId = t.iUserId
	LEFT JOIN register_driver d ON t.iDriverId = d.iDriverId
	LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId
	WHERE u.iUserId = '".$_SESSION['sess_iUserId']."'".$ssql." ORDER BY t.iTripId DESC";
	$db_trip = $obj->MySQLSelect($sql);
	//echo '<pre>'; print_R($db_trip); echo '</pre>';exit;
	$sql="select vName from currency where eDefault='Yes'";
	$db_currency=$obj->MySQLSelect($sql);
	

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
	
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $SITE_NAME?> | <?php echo $langage_lbl['LBL_MYTRIP_TRIPS']; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");
	$rtls = "";
	if($lang_ltr == "yes") {
		$rtls = "dir='rtl'";
	}
	?>
   
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
			  	<h2 class="header-page"><?php echo $langage_lbl['LBL_MYTRIP_TRIPS']; ?></h2>
		  		<!-- trips page -->
			  	<div class="trips-page">
			  		<form name="search" action="" method="post" onSubmit="return checkvalid()">
			  			<div class="Posted-date mytrip-page">
							<input type="hidden" name="action" value="search" />
				      		<h3><?php echo $langage_lbl['LBL_SEARCH_RIDES_POSTED_BY_DATE']; ?></h3>
							<span>
                            <a onClick="return todayDate('dp4','dp5');"><?php echo $langage_lbl['LBL_Today']; ?></a>
                            <a onClick="return yesterdayDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_Yesterday']; ?></a>
                            <a onClick="return currentweekDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_Current_Week']; ?></a>
                            <a onClick="return previousweekDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_Previous_Week']; ?></a>
                            <a onClick="return currentmonthDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_Current_Month']; ?></a>
                            <a onClick="return previousmonthDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_Previous Month']; ?></a>
                            <a onClick="return currentyearDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_Current_Year']; ?></a>
                            <a onClick="return previousyearDate('dFDate','dTDate');"><?php echo $langage_lbl['LBL_Previous_Year']; ?></a>
				      		</span> 
				      		<span>

                            <input type="text" id="dp4" name="startDate" placeholder="<?php echo $langage_lbl['LBL_FROM_DATE']?>" class="form-control" value=""/>
                            <input type="text" id="dp5" name="endDate" placeholder="<?php echo $langage_lbl['LBL_TO_DATE']?>" class="form-control" value=""/>
                            <b><button class="driver-trip-btn"><?php echo $langage_lbl['LBL_MYTRIP_Search']; ?></button>
								<button onClick="reset();" class="driver-trip-btn"><?php echo $langage_lbl['LBL_MYTRIP_RESET']; ?></button></b>
					      	</span>
				      	</div>
		      		</form>
			    	<div class="trips-table"> 
			    	
			      		<div class="trips-table-inner">
			      		<div class="driver-trip-table">
			        		<table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example" <?php echo $rtls; ?>>
			          			<thead>
									<tr>
	        							<tr>
	        								<?php if($_SESSION['sess_systype'] != "uberforx"){?>
											<th><?php echo $langage_lbl['LBL_MYTRIP_TRIP_TYPE_TXT_ADMIN'];?></th>
											<?}?>
											<th width="17%"><?php echo $langage_lbl['LBL_MYTRIP_RIDE_NO']; ?></th>
											<th width="18%"><?php echo $langage_lbl['LBL_MYTRIP_DRIVER']; ?></th>
											<th width="15%"><?php echo $langage_lbl['LBL_MYTRIP_Trip_Date']; ?></th>
											<th width="15%"><?php echo $langage_lbl['LBL_Your_Fare']; ?></th>
											<th width="15%"><?php echo $langage_lbl['LBL_MYTRIP_Car']; ?></th>
											<th width="16%"><?php echo $langage_lbl['LBL_MYTRIP_View_Invoice']; ?></th>
										</tr>
									</tr>
								</thead>
								<tbody>
								<?
									for($i=0;$i<count($db_trip);$i++)
									{
										$pickup = $db_trip[$i]['tSaddress'];
										$driver = $db_trip[$i]['name'].' '.$db_trip[$i]['lname'];
										$fare = $generalobj->trip_currency_payment($db_trip[$i]['iFare'],$db_trip[$i]['fRatio_'.$tripcurname]);
										$car = $db_trip[$i]['vVehicleType'];
										$eType = $db_trip[$i]['eType'];
										$trip_type = ($eType == 'Ride')? $langage_lbl['LBL_RIDE_TXT'] : $langage_lbl['LBL_DELIVER_TEXT'];
								?>
									<tr class="gradeA">
	        							<?php if($_SESSION['sess_systype'] != "uberforx"){?>
										<td ><?php echo $trip_type;?></td>
										<?}?>
										<td align="center"  data-order="<?php echo $db_trip[$i]['iTripId']?>"><?php echo $db_trip[$i]['vRideNo'];?></td>
										<td >
											<?php if($driver==''){echo '--';}else{echo $driver;}?>
										</td>
										<td align="center"><?php echo  jdate('d-M-Y',strtotime($db_trip[$i]['tEndDate']));?></td>
										<td align="right" class="center">
											<!--<?php if($fare==0){echo 'Canceled';}else{echo $tripcursymbol.' '.$fare;}?>-->
											<?php if($db_trip[$i]['iActive']=='Canceled'){
												 echo $fare.' '.$tripcursymbol;
												}
												else{
													echo $generalobj->trip_currency($fare);}?>
										</td>
										<!--<td class="center">
											<?php if($fare==0){
											echo $tripcursymbol.' '.$fare;
												}
												else{
													echo $generalobj->trip_currency($fare);}?>
										</td>-->
										<td align="center" class="center">
											<?php if($car==''){echo '--';}else{echo $car;}?>
										</td>
										<?php 
										
									if($db_trip[$i]['iActive'] == 'Canceled')	
									{?>
										<td class="center">
										<?php echo $langage_lbl['LBL_CANCELED']; ?>
										</td>
									<?php }else{?>	

										<td class="center">
											<a href="rider_invoice.php?iTripId=<?php echo $db_trip[$i]['iTripId']?>"><strong><?php echo $langage_lbl['LBL_VIEW']; ?></strong></a>
										</td>
										<?php } ?>
									</tr>
								<?php } ?>
								</tbody>
			        		</table>
			      		</div></div>
			   
			    </div>
			    <!-- -->
			    <!-- <?php //if(SITE_TYPE=="Demo"){?>
			    <div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
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
				 $("#dp4").datepicker('refresh');
			 }
			 if('<?php echo $endDate?>'!=''){
				 $("#dp5").val('<?php echo  $endDate;?>');
				 $("#dp5").datepicker('refresh');
			 }
             $('#dataTables-example').dataTable({
			  "order": [[ 0, "desc" ]],
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
		 function reset() {
			location.reload();
			
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
				 	message: "<h4><?php echo $langage_lbl['LBL_FROM_DATE_SHOULD_LESS'];?></h4>",
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
