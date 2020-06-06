<?php
include_once('../common.php');

if(!isset($generalobjAdmin)){
require_once(TPATH_CLASS."class.general_admin.php");
$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();
$script 	= "Driver Accept Report";

$sql="select dr.iDriverId,rd.vName,rd.vLastName from driver_request dr left join register_driver rd on dr.iDriverId=rd.iDriverId group by dr.iDriverId order by rd.vName";
$db_driver_accept=$obj->MySQLSelect($sql);
//echo "<pre>";print_r($db_driver_accept);exit;

$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$iDriverIdBy=(isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'');
$ssql='';
if($action!='')
{
	$startDate=$_REQUEST['startDate'];
	$date1=$startDate.' '."00:00:00";
	$endDate=$_REQUEST['endDate'];
	$date2=$endDate.' '."23:59:59";
	//$iDriverIdBy=$_REQUEST['iDriverId'];
	if($startDate!='' && $endDate!=''){
		$ssql.=" AND rs.tDate between '$date1' and '$date2'";
	}

	if($_POST['iDriverId'] != "")
	{
		$iDriverId = $_POST['iDriverId'];
		$ssql .= " AND rd.iDriverId = '".$iDriverId."'";
	}
		
}

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

//

$sql = "SELECT  DISTINCT rd.iDriverId, rd.vName, rd.vLastName FROM `driver_request` as rs left join register_driver rd on rs.iDriverId = rd.iDriverId ".$ssql." ORDER BY rd.iDriverId ";
$db_res= $obj->MySQLSelect($sql);
//echo "<pre>";print_r($db_res);exit;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title><?php echo $SITE_NAME?> | Ride Acceptance Report </title>
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
                                        <h2> Ride Acceptance Report </h2>
                                       
                                   </div>
                              </div>
                              <hr />
                         </div>                    
                        
                         <div class="table-list">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <div class="panel panel-default">
                                             <div class="panel-heading">
                                                 <?php echo $langage_lbl_admin['LBL_RIDE_ACCETANCE_REPORT_ADMIN'];?>
                                             </div>
                                             <div class="panel-body">
											 <form name="search" id="searchIt" action="" method="post" onSubmit="return checkvalid()">
												<div class="Posted-date mytrip-page mytrip-page-select">
													<input type="hidden" name="action" value="search" />
													<h3><?php echo $langage_lbl_admin['LBL_MYTRIP_SEARCH_RIDES_POSTED_BY_DATE']; ?></h3>
													<span>
													<a onClick="return todayDate('dp4','dp5');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
													<a onClick="return yesterdayDate('dFDate','dTDate');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
													<a onClick="return currentweekDate('dFDate','dTDate');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
													<a onClick="return previousweekDate('dFDate','dTDate');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
													<a onClick="return currentmonthDate('dFDate','dTDate');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
													<a onClick="return previousmonthDate('dFDate','dTDate');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
													<a onClick="return currentyearDate('dFDate','dTDate');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
													<a onClick="return previousyearDate('dFDate','dTDate');"><?php echo $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
													</span> 
													<span>
													<input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value=""/>
													<input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value=""/>
						        <select name="iDriverId" id="iDriverId" class="form-control input-sm driver-trip-detail-select" style="display:table-row-group;">
                                <option value="">Search By <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> Name</option>
                                <?for($j=0;$j<count($db_driver_accept);$j++){?>
								
                                <option value="<?php echo $db_driver_accept[$j]['iDriverId'];?>" <?php if($iDriverIdBy == $db_driver_accept[$j]['iDriverId']){ ?>selected <?php } ?> ><?php echo $db_driver_accept[$j]['vName']." ".$db_driver_accept[$j]['vLastName'];?></option>
                                <?}?>
                              </select>
													<b><button class="driver-trip-btn"><?php echo $langage_lbl_admin['LBL_Search']; ?></button>
														<button onClick="resetform1();" class="driver-trip-btn"><?php echo $langage_lbl_admin['LBL_RESET']; ?></button></b>
													</span>
												</div>
											</form>
                                                  <div class="table-responsive">
                                                       <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                            <thead>
                                                               <tr>
                                                                    <th>DRIVER NAME</th>
                                                                    <th>TOTAL TRIPS REQUESTED</th>     
                                                                    <th>TRIPS ACCEPTED</th>
                                                                    <th>TRIPS CANCELLED</th>
                                                                    <th>ACCEPTANCE PERCENTAGE</th>
                                                                 
                                                               </tr>
                                                            </thead>
                                                            <tbody>
                                                           <?php  
                                                           $total_trip_req ="";
                                                           $total_trip_acce_req ="";
                                                           $total_trip_dec_req ="";
														   
                                                           for($i=0;$i<count($db_res);$i++) {

                                                           $sql_acp = "SELECT rd.vName, rd. vLastName, rs.tDate,count(rs.iDriverId)as totalacp FROM driver_request AS rs LEFT JOIN register_driver AS rd ON rd.iDriverId = rs.iDriverId WHERE rs.eStatus ='Accept' AND rs.iDriverId ='".$db_res[$i]['iDriverId']."' $ssql";
														   //echo "<hr>";
                                                              $db_acp = $obj->MySQLSelect($sql_acp);
															//echo "<pre>";print_r($db_acp);exit;
                                                              $sql_dec = "SELECT count(iDriverId) as totaldec FROM `driver_request` WHERE eStatus ='Decline' AND iDriverId ='".$db_res[$i]['iDriverId']."'";
                                                              $db_dec= $obj->MySQLSelect($sql_dec);                                          
															
                                                             $db_acp_val = $db_acp[0]['totalacp'];
                                                             $db_dec_val = $db_dec[0]['totaldec'];
                                                             $total = $db_acp_val + $db_dec_val;

                                                             $total_trip_req = $total_trip_req + $total;
                                                             $total_trip_acce_req = $total_trip_acce_req + $db_acp_val;
                                                             $total_trip_dec_req = $total_trip_dec_req + $db_dec_val;                               

                                                             $percentage = (100 * $db_acp_val)/$total;
                                                              $aceptance_percentage = number_format($percentage, 2);        
															  
															  if($db_acp[0]['vName'] != ""){
                                                              ?>
                                                             <tr class="gradeA">
                                                                  <td><?php echo $db_acp[0]['vName'].' '.$db_acp[0]['vLastName']; ?></td>
                                                                  <td><?php echo  $total;?></td>
                                                                  <td><?php echo $db_acp_val; ?></td>
                                                                  <td><?php echo $db_dec_val; ?></td>
                                                                  <td><?php echo $aceptance_percentage.' %'; ?></td>
                                                             </tr>

                                                           <?php 	}
															 } 
														   ?>                                                              
                                                            </tbody>
                                                            <tr class="gradeA">
                                                                <td><b>TOTAL</b></td>
                                                                <td><?php echo   $total_trip_req; ?></td>
                                                                <td><?php echo  $total_trip_acce_req; ?></td>
                                                                <td><?php echo   $total_trip_dec_req; ?></td>
                                                                <td></td>
                                                            </tr>

                                                       </table>
                                                  </div>

                                             </div>
                                        </div>
                                   </div> <!--TABLE-END-->
                              </div>
                         </div>
                         <div class="clear"></div>
                    </div>
               </div>
               <!--END PAGE CONTENT -->
          </div>
          <!--END MAIN WRAPPER -->


          <?php include_once('footer.php');?>
          <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
          <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
		  <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
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
		 function resetform1()
		 {
			//location.reload();
			document.search.reset();
			document.getElementById("iDriverId").value="";
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
          $(document).ready(function () {
               $('#dataTables-example').dataTable({
                 "order": [[ 2, "desc" ]]
               });
          });
          function confirm_delete()
          {
               var confirm_ans = confirm("Are You sure You want to Delete this Rider?");
               return confirm_ans;
               //document.getElementById(id).submit();
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
          </script>
     </body>
     <!-- END BODY-->
</html>
