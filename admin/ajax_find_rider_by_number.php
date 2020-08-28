<?
include_once("../common.php");
$phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
$vehicleId = isset($_REQUEST['vehicleId']) ? $_REQUEST['vehicleId'] : '';
$AreaId = isset($_REQUEST['iAreaId']) ? $_REQUEST['iAreaId'] : '';
if ($phone != '') {
    $sql = "select * from register_user where vPhone = '" . $phone . "' LIMIT 1";
    $db_model = $obj->MySQLSelect($sql);
    $cont = '';
    for ($i = 0; $i < count($db_model); $i++) {
        $cont .= $db_model[$i]['vName'] . ":";
        $cont .= $db_model[$i]['vLastName'] . ":";
        $cont .= $db_model[$i]['vEmail'] . ":";
        $cont .= $db_model[$i]['iUserId'] . ":";
        $cont .= $db_model[$i]['vAddress'] . ":";
        $cont .= $db_model[$i]['vDescription'] . ":";
        $iduser = $db_model[$i]['iUserId'] . ":";
    }


    $sql = "select * from cab_booking where iUserId = '" . $iduser . "' ORDER BY dBooking_date DESC LIMIT 1";
    $db_model2 = $obj->MySQLSelect($sql);

    for ($i = 0; $i < count($db_model2); $i++) {
        $cont .= $db_model2[$i]['vSourceAddresss'] . ":";
        $cont .= $db_model2[$i]['tDestAddress'] . ":";
        $cont .= "(" . $db_model2[$i]['vSourceLatitude'] . "," . $db_model2[$i]['vSourceLongitude'] . "):";
        $cont .= $db_model2[$i]['vSourceLatitude'] . ":";
        $cont .= $db_model2[$i]['vSourceLongitude'] . ":";
        $cont .= "(" . $db_model2[$i]['vDestLatitude'] . "," . $db_model2[$i]['vDestLongitude'] . "):";
        $cont .= $db_model2[$i]['vDestLatitude'] . ":";
        $cont .= $db_model2[$i]['vDestLongitude'] . ":";
        $cont .= $db_model2[$i]['tTripComment'];
    }

    echo $cont;
    exit;
}

if ($vehicleId != '') {
    $sql = "select * from vehicle_type where iVehicleTypeId = '" . $vehicleId . "' LIMIT 1";
    $db_model = $obj->MySQLSelect($sql);
    $cont = '';
    for ($i = 0; $i < count($db_model); $i++) {
        $cont .= $db_model[$i]['iBaseFare'] . ":";
        $cont .= $db_model[$i]['fPricePerKM'] . ":";
        $cont .= $db_model[$i]['fPricePerMin'] . ":";
        $cont .= $db_model[$i]['iMinFare'];
    }
    echo $cont;
    exit;
}


if ($vehicleId != '' && $AreaId != '') {
    $sql = "select * from vehicle_type where vSavarArea = '" . $AreaId . "' AND iVehicleTypeId = '" . $vehicleId . "' LIMIT 1";
    $db_model = $obj->MySQLSelect($sql);
    $cont = '';
    for ($i = 0; $i < count($db_model); $i++) {
        $cont .= $db_model[$i]['iBaseFare'] . ":";
        $cont .= $db_model[$i]['fPricePerKM'] . ":";
        $cont .= $db_model[$i]['fPricePerMin'] . ":";
        $cont .= $db_model[$i]['iMinFare'];
    }
    echo $cont;
    exit;
}
?>
