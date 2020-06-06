<?php
include_once('../common.php');

$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId'])?$_SESSION['sess_iAdminUserId']:'';
$sess_iGroupId = isset($_SESSION['sess_iGroupId'])?$_SESSION['sess_iGroupId']:'';
if($sess_iAdminUserId == "") {
    echo json_encode(array('status'=>'error','data' => 'permission denide'));
    die();
}
/*
 * Results array
 */
$results = array();


$userType = isset($_REQUEST['type']) ? $_REQUEST['type'] : ''; 
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : ''; 
$iCompanyId = isset($_REQUEST['companyid']) ? $_REQUEST['companyid'] : ''; 

if($search === '')
{
    echo json_encode(array('status'=>'error','data' => 'search invalid'));
    die();
}

$tbl = '';
$idField = '';

if(strtolower($userType) == 'rider')
{
    $tbl = 'register_user';
    $idField = 'iUserId';
    
}
else if(strtolower($userType) == 'driver')
{
    $tbl = 'register_driver';
    $idField = 'iDriverId';
}
else
{
    echo json_encode(array('status'=>'error','data' => 'type invalid'));
    die();
}


$where = '';

if(preg_match('/^[0-9]+$/',$search))
{
    $where = " `{$idField}` = {$search} OR `vPhone` LIKE '%$search' ";
}
else
{
    $where = " `vEmail` LIKE '%{$search}%' OR `vName` like '%$search%'  OR `vLastName` like '%$search%' ";
}

$sql = "SELECT vName,vLastName,vEmail,vPhone,{$idField} as id FROM `{$tbl}` ";  

if($where != '')
    $sql .= ' WHERE ' . $where;

$sql .= ' LIMIT 20';

$users = $obj->MySQLSelect($sql);

/*
 * Output results
 */
echo json_encode(array('status'=>'ok','data' => $users));

