<!-- HEADER SECTION -->
<?php

include_once('../common.php');
//require_once(TPATH_CLASS .'savar/jalali_date.php');


//$host4 = "localhost"; // Host name
//$username4 = "k68ir_DB"; // Mysql username
//$password4 = 'Kamelia.irir*****'; // Mysql password
//$db_name4 = "k68ir_DB"; // Database name
////$tbl_nameh="user"; // Table name
//// Create connection
//$con5 = mysqli_connect("$host4", "$username4", "$password4", "$db_name4");
//
//// Check connection
//if (mysqli_connect_errno($con5)) {
//    echo "Failed to connect to MySQL: " . mysqli_connect_error();
//}
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


//$showtables = mysqli_query($con5, "SHOW TABLES FROM k68ir_DB");
//
//while ($table = mysqli_fetch_array($showtables)) { // go through each row that was returned in $result
//    mysqli_query($con5, "DELETE FROM $table[0] WHERE eStatus = 'Deleted'");    // print the table that was returned on that row.
//}


if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
/*-----------driver data -----------*/
$generalobjAdmin->check_member_login();
$sql = "SELECT * FROM register_driver Where eStatus='Active'";
// disable for limit memory
//$db_driver = $obj->MySQLSelect($sql);
$actDri = count($db_driver);
$sql = "SELECT * FROM register_driver";
// disable for limit memory
//$db_driver_total = $obj->MySQLSelect($sql);
$totalDri = count($db_driver_total);
/*------------*/

/*-----------Company data -----------*/

$sql = "SELECT * FROM company Where eStatus='Active'";
$db_Company = $obj->MySQLSelect($sql);
$actCom = count($db_Company);
$sql = "SELECT * FROM company";
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

$sql = "SELECT * FROM driver_vehicle Where eStatus='Active'";
$db_vehicle = $obj->MySQLSelect($sql);
$actVehicle = count($db_vehicle);
$sql = "SELECT * FROM driver_vehicle";
//$db_vehicle_total = $obj->MySQLSelect($sql);
//$totalVehicle=count($db_vehicle_total);
/*------------*/

/*-------ride status----------*/

$sql = "SELECT * FROM trips t JOIN register_driver rd ON t.iDriverId=rd.iDriverId WHERE iActive='Finished' ORDER BY tEndDate DESC LIMIT 0,4";
$db_finished = $obj->MySQLSelect($sql);

/*------------------*/

$sql = "SELECT *,lf.iDriverId as did,lf.iCompanyId as cid, rd.vName as Driver,c.vName as Company FROM log_file lf LEFT JOIN company c ON lf.iCompanyId=c.iCompanyId LEFT JOIN register_driver rd ON lf.iDriverId=rd.iDriverId ORDER BY tDate DESC LIMIT 0,10";
$db_notification = $obj->MySQLSelect($sql);


if (isset($_REQUEST['allnotification'])) {
    $sql = "SELECT *,rd.vName as Driver,c.vName as Company FROM log_file lf LEFT JOIN company c ON lf.iCompanyId=c.iCompanyId LEFT JOIN register_driver rd ON lf.iDriverId=rd.iDriverId ORDER BY tDate DESC";
    $db_notification = $obj->MySQLSelect($sql);
}

?>
<nav style="position: fixed;width: calc(100% - 200px);z-index: 101" class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">سامانه تاکسی آنلاین - داشبورد مدیریت</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-envelope"></i>
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown" style="width:250px;">
                    <?php for ($i = 0; $i < count($db_finished); $i++) { ?>
                        <a href="invoice.php?iTripId=<?php echo $db_finished[$i]['iTripId'] ?>">
                            <div class="row m-1">
                                <div class="col-6 text-right">
                                    <i class="fa fa-user"></i>
                                    <?php echo $db_finished[$i]['vName'] . " " . $db_finished[$i]['vLastName']; ?></div>
                                <div class="col-6 text-left">
                                    <i class="fa fa-calendar"></i>
                                    <?php
                                    $regDate = $db_finished[$i]['tEndDate'];
                                    $dif = strtotime(Date('Y-m-d H:i:s')) - strtotime($regDate);
                                    if ($dif < 3600) {
                                        $time = floor($dif / (60));
                                        echo $time . " دقیقه قبل";
                                    } else if ($dif < 86400) {
                                        $time = floor($dif / (60 * 60));
                                        echo $time . " ساعت قبل";
                                    } else {
                                        $time = floor($dif / (24 * 60 * 60));
                                        echo $time . " روز پیش";
                                    }
                                    ?>
                                </div>
                                <div class="col-12"><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN'] . " : " . $db_finished[$i]['vRideNo']; ?></div>
                                <div class="col-12"><?php echo "وضعیت سفر: " . $db_finished[$i]['iActive']; ?></div>
                            </div>
                            <div class="dropdown-divider"></div>
                        </a>
                    <?php } ?>
                </div>
            </li>
        </ul>
    </div>
</nav>