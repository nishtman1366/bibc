<?
include_once('common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
$script="Trips";
$tbl_name 	= 'register_driver';
$generalobj->check_member_login();
$abc = 'admin,company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);

$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$password=(isset($_REQUEST['exp_password'])?$_REQUEST['exp_password']:'');

$name = '';
$sep = ',';


if($password != '09112805688')
{
    die("Wrong Export Password...");
}

if(isset($_GET['trips']))
{
    $name = 'trips';
    $ssql='';
    $startDate  = $_REQUEST['startDate'];
    $endDate    = $_REQUEST['endDate'];
    $gstartDate = savar_request_date_to_gregorian($_REQUEST['startDate']);
    $gendDate   = savar_request_date_to_gregorian($_REQUEST['endDate']);

    if($gstartDate!=''){
        $ssql.=" AND Date(t.tEndDate) >='".$gstartDate."'";
    }
    if($gendDate!=''){
        $ssql.=" AND Date(t.tEndDate) <='".$gendDate."'";
    }
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

    $counter = isset($_REQUEST['counter']) ? 'yes' : '';
    $iActive = isset($_REQUEST['iActive']) ? $_REQUEST['iActive'] : '';

    #print_r($counter);die();

    $groupby = "";
    $counterStart = "";
    if($counter == 'yes')
    {
        $groupby = " GROUP BY t.iDriverId ";
        $counterStart = " , COUNT(*) as tripCount";
    }


    $whereActive = '';

    if($iActive == 'Finished')
    {
        $whereActive = " AND t.iActive = 'Finished'  ";
    }
    else if($iActive == 'Canceled')
    {
        $whereActive = " AND t.iActive = 'Canceled'  ";
    }
    else if($iActive == 'DriverCanceled')
    {
        $whereActive = " AND t.iActive = 'Canceled' AND vCancelReason != '' ";
    }
    else if($iActive == 'RiderCanceled')
    {
        $whereActive = " AND t.iActive = 'Canceled' AND vCancelReason = '' ";
    }

//    $sql = "SELECT u.vName, u.vLastName,t.tEndDate, d.vAvgRating,d.iCompanyId,t.vRideNo, t.iFare, d.iDriverId,t.iActive, t.tSaddress, t.tDaddress,t.eType, d.vName AS name, d.vLastName AS lname,t.eCarType,t.iTripId,t.fTripGenerateFare,vt.vVehicleType_".$_SESSION['sess_lang']." as vVehicleType , t.vCancelReason , t.vCancelComment 
//    {$counterStart}
//    FROM register_driver d
//    RIGHT JOIN trips t ON d.iDriverId = t.iDriverId
//    LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId
//    LEFT JOIN  register_user u ON t.iUserId = u.iUserId
//    WHERE d.iCompanyId IN (".$comp_list.")".$ssql. $whereActive . $groupby . " ORDER BY t.iTripId DESC";

    $sql = 
    "SELECT t.iTripId,t.vRideNo
    ,CONCAT(d.vName,' ', d.vLastName)as DriverName, d.iDriverId as DriverID,d.vPhone as DriveerPhone, d.vAvgRating as AverageRating,d.iCompanyId as CompanyId
    ,CONCAT(u.vName,' ', u.vLastName)as PassengerName,u.vPhone as PassengerPhone
    ,t.tSaddress,t.tDaddress,t.fTripGenerateFare as Fare,t.iFare as Cash,t.fCommision as Commision,t.fDiscount as Discount,t.fWalletDebit as Wallet,t.iActive as TripStatus,t.eType,vt.vVehicleType_".$_SESSION['sess_lang']." as VehicleType , t.vCancelReason , t.vCancelComment 
    {$counterStart}
    FROM register_driver d 
    RIGHT JOIN trips t ON d.iDriverId = t.iDriverId 
    LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId 
    LEFT JOIN register_user u ON t.iUserId = u.iUserId 
    WHERE d.iCompanyId IN (".$comp_list.")".$ssql. $whereActive . $groupby . " ORDER BY t.iTripId DESC";
    
    if($ssql == '')
        $sql .= " LIMIT 1000";

    //die($sql);

    $db_data = $obj->MySQLSelect($sql);

}

$len = count($db_data);

if($len == 0)
    die("List Id Empty...");

header('Content-Encoding: UTF-8');
header('Content-type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=Export_'.$name.'_'.date('Y-m-d_H.i.s').'.csv');
echo "\xEF\xBB\xBF"; // UTF-8 BOM

echo implode($sep,array_keys($db_data[0]))."\n";

for($i = 0 ; $i<$len ; $i++)
{
    echo '"'.implode('"'.$sep.'"',array_values($db_data[$i])).'"'."\n";
}
