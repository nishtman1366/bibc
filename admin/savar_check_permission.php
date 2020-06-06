<?php
#include_once('../common.php');
#include_once('savar_check_permission.php');


#1 	Super Administrator 	Active
#2 	Dispatcher Admin 	Active
#3 	Billing Admin 	Active
#4 	Driver Manager 	Active


function checkPermission($section, $action = '', $group_id = -1)
{
    if (isset($_SESSION['sess_iGroupId']) == false)
        return false;
    if ($_SESSION['sess_iGroupId'] == 1)
        return true;
    if ($group_id == -1)
        $group_id = $_SESSION['sess_iGroupId'];


    $perrmisionArray = array(
        1 => array(
            'DASHBOARD' => '',
            'COMPANY' => '',
            'DRIVER' => '',
            'PET_TYPE' => '',
            'VEHICLE' => '',
            'VEHICLE_TYPE' => '',
            'VEHICLE_CATEGORY' => '',
            'PACKAGE_TYPE' => '',
            'RIDER' => '',
            'BOOKING' => '',
            'RIPS' => '',
            'CAB_BOOKING' => '',
            'COUPON' => '',
            'MAP' => '',
            'HEAT_MAP' => '',
            'REPORTS' => '',
            'SETTINGS' => '',
        ),
        2 => array(
            'DASHBOARD' => '',
            'BOOKING' => '',
        ),
        3 => array(
            'DASHBOARD' => '',
            'REPORTS' => '',
        ),
        4 => array(
            'DASHBOARD' => '',
            'DRIVER' => '',
        ),
        5 => array(
            'DASHBOARD' => '',
            'VEHICLE_TYPE' => '',
        ),

    );

    return isset($perrmisionArray[$group_id][$section]);
}