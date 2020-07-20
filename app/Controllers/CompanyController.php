<?php

namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Models\Area;
use App\Models\Company;
use App\Models\Country;

class CompanyController extends BaseController
{
    public function index()
    {
        $companies = Company::with('area')
            ->withCount('drivers')
            ->orderBy('tRegistrationDate', 'ASC')
            ->get();
        return view('pages.companies.list', compact('companies'));
    }

    public function form(int $id = null)
    {
        $areas = Area::where('sActive', 'Yes')->orderBy('sAreaNamePersian', 'ASC')->get();
        $companies = Company::orderBy('vCompany', 'ASC')->get();
        $countries = Country::orderBy('vCountry', 'ASC')->get();
        $company = null;
        if (!is_null($id)) {
            $company = Company::find($id);
        }
        return view('pages.companies.form', compact('companies', 'areas', 'countries', 'company'));
    }

    public function create()
    {
        //TODO validation for create company
        Company::create(input()->all());
        return redirect(url('companies'));
    }

    public function update(int $id)
    {
        //TODO validation for edit company
        $company = Company::find($id);
        $company->fill(input()->all());
        $company->save();
        return redirect(url('companies'));
    }

    public function delete(int $id)
    {
        if (!is_null($id)) {
//            Company::destroy($id);
            Company::find($id)->update(['eStatus' => 'Deleted']);
        }
        return redirect(url('companies'));
    }
}