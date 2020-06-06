<!-- HEADER SECTION -->
<?php

	include_once('../common.php');
//require_once(TPATH_CLASS .'savar/jalali_date.php');



//	$host4="localhost"; // Host name
//$username4="partaxi1_KrG68"; // Mysql username
//$password4='Kamelia.irir*****'; // Mysql password
//$db_name4="partaxi1_KrG68"; // Database name
////$tbl_nameh="user"; // Table name
//// Create connection
//$con5=mysqli_connect("$host4","$username4","$password4","$db_name4");
//
//// Check connection
//if (mysqli_connect_errno($con5))
//  {
//  echo "Failed to connect to MySQL: " . mysqli_connect_error();
//  }
//mysqli_set_charset($con5, "utf8");

/*
$conmanyareaadmin = '';
$ids = $_SESSION['sess_area'];
$companeyadminarea=mysqli_query($con5,"SELECT * FROM company where iAreaId = '" . $_SESSION['sess_area'] . "'");
while($rowcs = mysqli_fetch_array($companeyadminarea)){
   $conmanyareaadmin .= " And iCompanyId = '" . $rowcs['iCompanyId']."'";
	 //$ids++;
}
//die($conmanyareaadmin);
*/


//
//
//$showtables= mysqli_query($con5, "SHOW TABLES FROM partaxi1_KrG68");
//
//while($table = mysqli_fetch_array($showtables)) { // go through each row that was returned in $result
//		mysqli_query($con5, "DELETE FROM $table[0] WHERE eStatus = 'Deleted'");    // print the table that was returned on that row.
//}
//
//
//
//
//
//
//

	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	/*-----------driver data -----------*/
	$generalobjAdmin->check_member_login();
	$sql="SELECT * FROM register_driver Where eStatus='Active'";
	// disable for limit memory
    //$db_driver = $obj->MySQLSelect($sql);
	$actDri=count($db_driver);
	$sql="SELECT * FROM register_driver";
    // disable for limit memory
	//$db_driver_total = $obj->MySQLSelect($sql);
	$totalDri=count($db_driver_total);
	/*------------*/

	/*-----------Company data -----------*/

	$sql="SELECT * FROM company Where eStatus='Active'";
	$db_Company = $obj->MySQLSelect($sql);
	$actCom=count($db_Company);
	$sql="SELECT * FROM company";
	//$db_Company_total = $obj->MySQLSelect($sql);
	//$totalCom=count($db_Company_total);
	/*------------*/

	/*-----------Rider data -----------*/

	//  Mamad  $sql="SELECT * FROM register_user Where eStatus='Active'";
	//$db_Rider = $obj->MySQLSelect($sql);
	//$actRider=count($db_Rider);
	//  Mamad  $sql="SELECT * FROM register_user";
	//$db_Rider_total = $obj->MySQLSelect($sql);
	//$totalRider=count($db_Rider_total);
	/*------------*/

	/*-----------Vehicle data -----------*/

/*	$sql="SELECT * FROM driver_vehicle Where eStatus='Active'";
	$db_vehicle = $obj->MySQLSelect($sql);
	$actVehicle=count($db_vehicle);
	$sql="SELECT * FROM driver_vehicle";*/
	//$db_vehicle_total = $obj->MySQLSelect($sql);
	//$totalVehicle=count($db_vehicle_total);
	/*------------*/

	/*-------ride status----------*/

	$sql="SELECT * FROM trips t JOIN register_driver rd ON t.iDriverId=rd.iDriverId WHERE iActive='Finished' ORDER BY tEndDate DESC LIMIT 0,4";
	$db_finished = $obj->MySQLSelect($sql);

	/*------------------*/

	$sql="SELECT *,lf.iDriverId as did,lf.iCompanyId as cid, rd.vName as Driver,c.vName as Company FROM log_file lf LEFT JOIN company c ON lf.iCompanyId=c.iCompanyId LEFT JOIN register_driver rd ON lf.iDriverId=rd.iDriverId ORDER BY tDate DESC LIMIT 0,10";
	$db_notification = $obj->MySQLSelect($sql);


	if(isset($_REQUEST['allnotification']))
	{
		$sql="SELECT *,rd.vName as Driver,c.vName as Company FROM log_file lf LEFT JOIN company c ON lf.iCompanyId=c.iCompanyId LEFT JOIN register_driver rd ON lf.iDriverId=rd.iDriverId ORDER BY tDate DESC";
		$db_notification = $obj->MySQLSelect($sql);
	}

?>
<div id="top">
	<nav class="navbar navbar-inverse navbar-fixed-top" style="padding:7px 0;">
		<a data-original-title="Show/Hide Menu" data-placement="bottom" data-tooltip="tooltip" class="accordion-toggle btn btn-primary btn-sm visible-xs" data-toggle="collapse" href="#menu" id="menu-toggle"><i class="icon-align-justify"></i></a>
		<!-- LOGO SECTION -->
		<header class="navbar-header">
			<a href="dashboard.php" class="navbar-brand"><img src="../assets/img/logo.png" alt="" height="37" /></a>
		</header>
		<!-- END LOGO SECTION -->
		<ul class="nav navbar-top-links navbar-right">

			<!-- MESSAGES SECTION -->
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">
					<span class="label label-success"></span><i class="icon-envelope-alt"></i>&nbsp; <i class="icon-chevron-down"></i>
				</a>

				<ul class="dropdown-menu dropdown-messages">
				<?php for($i=0;$i<count($db_finished);$i++){?>
					<li>
						<a href="invoice.php?iTripId=<?php echo $db_finished[$i]['iTripId']?>">
							<div>
							   <strong><?php echo $db_finished[$i]['vName']." ".$db_finished[$i]['vLastName']; ?></strong>
								<span class="pull-right text-muted">
									<em><?php
														$regDate=$db_finished[$i]['tEndDate'];
										$dif=strtotime(Date('Y-m-d H:i:s'))-strtotime($regDate);
														if($dif<3600)
												{
												$time=floor($dif/(60));
												echo $time." minites ago";
												}
												else if($dif<86400)
												{
												$time=floor($dif/(60*60));
												 echo $time." hour ago";
												}
												else
												{
												 $time=floor($dif/(24*60*60));
												  echo $time." Days ago";
												}
														?></em>
								</span>
							</div>

							<div><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']." : " . $db_finished[$i]['vRideNo'];?>

								<br />
								<span class="label label-primary"><?php echo "Ride Status: ".$db_finished[$i]['iActive'];?> </span>

							</div>
						</a>
					</li>
					<li class="divider"></li>
				<?} ?>
					<!--<li>
						<a href="#">
							<div>
								<strong>Raphel Jonson</strong>
								<span class="pull-right text-muted">
									<em>Yesterday</em>
								</span>
							</div>
							<div>Lorem ipsum dolor sit amet, consectetur adipiscing.
								 <br />
								<span class="label label-success"> Moderate </span>
							</div>
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="#">
							<div>
								<strong>Chi Ley Suk</strong>
								<span class="pull-right text-muted">
									<em>26 Jan 2014</em>
								</span>
							</div>
							<div>Lorem ipsum dolor sit amet, consectetur adipiscing.
								 <br />
								<span class="label label-danger"> Low </span>
							</div>
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a class="text-center" href="#">
							<strong>Read All Messages</strong>
							<i class="icon-angle-right"></i>
						</a>
					</li>-->
				</ul>

			</li>
			<!--END MESSAGES SECTION -->

			<!--TASK SECTION -->
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">
					<!-- <span class="label label-danger"></span> -->   <i class="icon-tasks"></i>&nbsp; <i class="icon-chevron-down"></i>
				</a>

				<ul class="dropdown-menu dropdown-tasks">
					<li>
						<a href="company.php">
							<div>
								<p>
									<strong> Company </strong>
									<span class="pull-right text-muted"><?php $ComAct=floor(($actCom/$totalCom)*100);
										echo $ComAct."% Active";
										?></span>
								</p>
								<div class="progress progress-striped active">
									<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: <?echo $ComAct."%";?>">
										<span class="sr-only"><?php echo $ComAct."% Active"; ?></span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="driver.php">
							<div>
								<p>
									<strong><?php echo $langage_lbl_admin['LBL_PROGRESS_DRIVER_ADMIN'];?> </strong>
									<span class="pull-right text-muted"><?php $driAct=floor(($actDri/$totalDri)*100);
										echo $driAct."% Active";
										?></span>
								</p>
								<div class="progress progress-striped active">
									<div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $driAct."%";?>">
										<span class="sr-only"><?php echo $driAct."% Active";?></span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="rider.php">
							<div>
								<p>
									<strong> <?php echo $langage_lbl_admin['LBL_PROGRESS_RIDER_NAME_ADMIN'];?> </strong>
									<span class="pull-right text-muted">
										<?php $RidAct=floor(($actRider /$totalRider)*100);
										echo $RidAct."% Active";
										?>
									</span>
								</p>
								<div class="progress progress-striped active">
									<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $RidAct."%";?>">
										<span class="sr-only"><?php echo $RidAct."% Active";?></span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="vehicles.php">
							<div>
								<p>
									<strong> <?php echo $langage_lbl_admin['LBL_VEHICLE_TXT_ADMIN'];?> </strong>
									<span class="pull-right text-muted"><?php $VehicleAct=floor(($actVehicle /$totalVehicle)*100);
										echo $VehicleAct."% Active";
										?></span>
								</p>
								<div class="progress progress-striped active">
									<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $VehicleAct."%"; ?>">
										<span class="sr-only"><?php echo $VehicleAct."% Active"; ?></span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<!-- <li class="divider"></li>
					<li>
						<a href="#">
							<div>
								<p>
									<strong> Summary </strong>
									<span class="pull-right text-muted">80% Complete</span>
								</p>
								<div class="progress progress-striped active">
									<div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
										<span class="sr-only">80% Complete (danger)</span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a class="text-center" href="#">
							<strong>See All Tasks</strong>
							<i class="icon-angle-right"></i>
						</a>
					</li> -->
				</ul>

			</li>
			<!--END TASK SECTION -->



			<!--ADMIN SETTINGS SECTIONS -->
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="icon-user "></i>&nbsp; <i class="icon-chevron-down "></i>
				</a>
				<ul class="dropdown-menu dropdown-user">
					<li><a href="admin_action.php?id=<?echo $_SESSION['sess_iAdminUserId'];?>"><i class="icon-user"></i> User Profile </a></li>
					<li><a href="general.php"><i class="icon-gear"></i> Settings </a></li>
					<li class="divider"></li>
					<li><a href="logout.php"><i class="icon-signout"></i> Logout </a></li>
				</ul>
			</li>
			<!--END ADMIN SETTINGS -->
		</ul>

	</nav>
</div>
<!-- END HEADER SECTION -->
