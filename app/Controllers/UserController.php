<?php

namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Models\AdminGroup;
use App\Models\Area;
use App\Models\User;

class UserController extends BaseController
{
    public function index()
    {
        $users = User::with('adminGroups')
            ->orderBy('iAdminId', 'ASC')
            ->get();
        return view('pages.users.list', compact('users'));
    }

    public function form(int $id = null)
    {
        $areas = Area::where('sActive', 'Yes')->orderBy('sAreaNamePersian', 'ASC')->get();
        $groups = AdminGroup::orderBy('iGroupId', 'ASC')->get();
        $user = null;
        if (!is_null($id)) {
            $user = User::where('iAdminId', $id)->get()->first();
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