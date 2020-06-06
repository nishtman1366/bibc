<?php

function countries()
{
    global $obj;
    $sql = "select * from `country`";
    return $obj->MySQLSelect($sql);
}

function companies(int $areaId = null)
{
    global $obj;
    $sql = "select * from company WHERE 1=1";
    if (!is_null($areaId) && $areaId != -1) {
        $sql .= " AND iAreaId=" . $areaId;
    }
    $sql .= " order by vCompany";
    return $obj->MySQLSelect($sql);
}

function areas()
{
    global $obj;
    $sql = "SELECT * FROM `savar_area` WHERE `sSpecialArea` != 'Yes'";
    return $obj->MySQLSelect($sql);
}

function currencies()
{
    global $obj;
    $sql = "select * from  currency where eStatus='Active'";
    return $obj->MySQLSelect($sql);
}

function languages()
{
    global $obj;
    $sql = "select * from language_master where eStatus = 'Active'";
    return $obj->MySQLSelect($sql);
}