<?php

namespace App\Controllers;

use App\Controllers\Controller as BaseController;

class CompanyController extends BaseController
{
    public function index()
    {
        $companiesQuery = "SELECT * FROM `company` ORDER BY `tRegistrationDate` DESC";
        $companyResult = $this->dbConnection->query($companiesQuery);
        $companies = [];
        if ($companyResult->num_rows > 0) {
//            $companiesList = $companyResult->fetch_assoc();
//            echo '<pre>';
//            print_r($companiesList);
//            exit();
            while ($company = $companyResult->fetch_assoc()) {

//            }
//            foreach ($companyResult->fetch_assoc() as $company) {
                $driversCountQuery = "SELECT count(`iDriverId`) AS `count` FROM `register_driver` WHERE `iCompanyId` = '" . $company['iCompanyId'] . "'";
                $driversCountResult = $this->dbConnection->query($driversCountQuery);
                $driversCountRow = 0;
                if ($driversCountResult->num_rows > 0) $driversCountRow = $driversCountResult->fetch_assoc();
                $company['driversCount'] = $driversCountRow['count'];

                $areaNameQuery = "SELECT * FROM `savar_area` WHERE `aId` = '" . $company['iAreaId'] . "'";
                $areaNameResult = $this->dbConnection->query($areaNameQuery);
                $areaRow = 0;
                if ($areaNameResult->num_rows > 0) $areaRow = $areaNameResult->fetch_assoc();
                $company['areaName'] = $areaRow['sAreaNamePersian'];
                $companies[] = $company;
            }
        }

        return view('pages.companies.list', compact('companies'));
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