<?php

namespace App\Controllers;

use App\Controllers\Controller as BaseController;

class UserController extends BaseController
{
    public function index()
    {
        $sql = "SELECT ad.*,ag.vGroup FROM administrators ad left join admin_groups ag on
			ad.iGroupId=ag.iGroupId
			where ad.eStatus != 'Delete' ORDER BY BINARY ad.vFirstName ASC";
        $users = $this->dbConnection->query($sql);
        return view('pages.users.list', compact('users'));
    }

    public function form(int $id = null)
    {
        $areas = $this->dbConnection->query("select * from `savar_area` WHERE `sActive` = 'Yes'");
        $groups = $this->dbConnection->query("SELECT * FROM `admin_groups` WHERE 1");
        $user = null;
        if (!is_null($id)) {
            $userResult = $this->dbConnection->query(sprintf("SELECT * FROM `administrators` WHERE iAdminId = '%s'", $id));
            $user = $userResult->fetch_assoc();
        }
        return view('pages.users.form', compact('areas', 'groups', 'user'));
    }

    public function create()
    {
        //TODO validation for create user
        $this->dbConnection->query("INSERT INTO `administrators`  SET
              `vFirstName` = '" . input('vFirstName') . "',
              `vLastName` = '" . input('vLastName') . "',
              `vEmail` = '" . input('vEmail') . "',
              `vPassword` = '" . encrypt(input('vPassword')) . "',
              `iGroupId` = '" . input('iGroupId') . "',
              `vAccessOptions` = '" . input('vAccessOptions') . "',
              `area` = '" . input('area') . "',
              `vContactNo` = '" . input('vContactNo') . "',
              `eStatus` = '" . input('eStatus') . "'");

        return redirect(url('users'));
    }

    public function update(int $id)
    {
        //TODO validation for edit user
        $this->dbConnection->query("UPDATE `administrators`  SET
              `vFirstName` = '" . input('vFirstName') . "',
              `vLastName` = '" . input('vLastName') . "',
              `vEmail` = '" . input('vEmail') . "',
              `vPassword` = '" . encrypt(input('vPassword')) . "',
              `iGroupId` = '" . input('iGroupId') . "',
              `vAccessOptions` = '" . input('vAccessOptions') . "',
              `area` = '" . input('area') . "',
              `vContactNo` = '" . input('vContactNo') . "',
              `eStatus` = '" . input('eStatus') . "'
              WHERE `iAdminId` = '" . $id . "'");

        return redirect(url('users'));
    }


    public function delete(int $id)
    {
        if (!is_null($id)) {
            $this->dbConnection->query(sprintf("UPDATE administrators SET eStatus = 'Deleted' WHERE iAdminId = '%s'", $id));
        }
        return redirect(url('users'));
    }
}