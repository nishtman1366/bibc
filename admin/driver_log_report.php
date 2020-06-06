<?php
include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('REPORTS') == false)
		die('you dont`t have permission...');
	

if(!isset($generalobjAdmin)){
require_once(TPATH_CLASS."class.general_admin.php");
$generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();


$script   = "Driver Log Report";

$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$iDriverIdBy=(isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'');

$ssql='';
if($action!='')
{
	$startDate=$_REQUEST['startDate'];
	$endDate=$_REQUEST['endDate'];
	//$startDate1=$startDate.' '."00:00:00";
	//$endDate1=$endDate.' '."23:59:59";
	//$iDriverIdBy=$_REQUEST['iDriverId'];
	if($startDate!=''){
		$ssql.=" AND dlr.dLoginDateTime BETWEEN '".$startDate."' AND '".$endDate."'";
	}
	/*if($endDate!=''){
		$ssql.=" AND Date(dlr.dLoginDateTime) <='".$endDate."'";
	}*/
	if($iDriverIdBy!='')
	{
		$ssql.=" And rd.iDriverId = '".$iDriverIdBy."'";
	}
}

$sql = "SELECT rd.vName, rd.vLastName, rd.vEmail, dlr.dLoginDateTime, dlr.dLogoutDateTime
FROM driver_log_report AS dlr
LEFT JOIN register_driver AS rd ON rd.iDriverId = dlr.iDriverId where 1=1 ".$ssql." order by dlr.iDriverLogId DESC LIMIT 1000";
$db_log_report = $obj->MySQLSelect($sql);
$sql = "select * from register_driver WHERE eStatus != 'Deleted' order by vName LIMIT 300";
$db_company = $obj->MySQLSelect($sql);
//echo "<pre>"; print_r($db_log_report); exit;

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
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title><?php echo $SITE_NAME?> | Driver Log Report<?php echo $langage_lbl_admin['LBL_DRIVER_LOG_REPORT_SMALL_ADMIN'];?></title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

		  
		  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css" />
		  
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
                                        <h2>Driver Log Report</h2>
                                       
                                   </div>
                              </div>
                              <hr />
                         </div>
                         <?php if($success == 1) { ?>
                         <div class="alert alert-success alert-dismissable">
                              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                             <?php echo $_REQUEST['succe_msg']; echo isset($_REQUEST['succe_msg'])? $_REQUEST['succe_msg'] : ''; ?>
                         </div><br/>
                         <?php }elseif ($success == 2) { ?>
                           <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                           </div><br/>
                         <?php } ?>
                        
                         <div class="table-list">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <div class="panel panel-default">
                                             <div class="panel-heading">
                                                  <?php echo $langage_lbl_admin['LBL_DRIVER_LOG_REPORT_SMALL_ADMIN'];?>
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
													<input type="text" id="dp4" name="startDate" placeholder="From Driver Online Date" class="form-control" value=""/>
													<input type="text" id="dp5" name="endDate" placeholder="To Driver Online Date" class="form-control" value=""/>
						        
								<style>
								 .search-select .caret{display:none}
								 </style>
								 <span style="width: 18%;margin-top:0px" class="search-select">
								<select name="iDriverId" id="iDriverId" class="form-control input-sm driver-trip-detail-select selectpicker" style="display:table-row-group;"
								data-show-subtext="true" data-live-search="true">
                                <option value="">Search By <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> Name</option>
                                <?for($j=0;$j<count($db_company);$j++){?>
								
                                <option value="<?php echo $db_company[$j]['iDriverId'];?>" <?php if($iDriverIdBy == $db_company[$j]['iDriverId']){ ?>selected <?php } ?> ><?php echo $db_company[$j]['vName']." ".$db_company[$j]['vLastName'];?></option>
                                <?}?>
                              </select>
							  </span>
													<b><button class="driver-trip-btn"><?php echo $langage_lbl_admin['LBL_Search']; ?></button>
														<button onClick="resetform();" class="driver-trip-btn"><?php echo $langage_lbl_admin['LBL_RESET']; ?></button></b>
													</span>
												</div>
											</form>
                                                  <div class="table-responsive">
                                                       <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                                                            <thead>
                                                                 <tr>
                                                                      <th>NAME</th>
                                                                      <th>EMAIL</th>
                                                                      <th>Online Time</th>
                                                                      <th>Offline Time</th>                                       
                                                                      <th>Total Hours Login</th>
                                                                     
                                                                 </tr>
                                                            </thead>
                                                            <tbody>
                                                                 <?php for($i=0;$i<count($db_log_report);$i++) {

                                                                  $dstart = $db_log_report[$i]['dLoginDateTime'];
                                                                    if( $db_log_report[$i]['dLogoutDateTime'] == '0000-00-00 00:00:00' || $db_log_report[$i]['dLogoutDateTime'] == '' ){

                                                                       $dLogoutDateTime = '--';
                                                                       $totalTimecount = '--';

                                                                    }else{

                                                                       $dLogoutDateTime = $db_log_report[$i]['dLogoutDateTime'];
                                                                       $totalhours = get_left_days_jobsave ($dLogoutDateTime,$dstart);
                                                                       $totalTimecount = mediaTimeDeFormater ($totalhours);
                                                                       //$totalTimecount = $totalTimecount.' hrs';
                                                                    }     ?>

                                                                 <tr class="gradeA">
                                                                      <td><?php echo $db_log_report[$i]['vName'].' '.$db_log_report[$i]['vLastName']; ?></td>
                                                                      <td><?php echo $generalobjAdmin->clearEmail($db_log_report[$i]['vEmail']); ?></td>
                         