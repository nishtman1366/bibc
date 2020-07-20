<?php


namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Models\Company;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Language;

class DriverController extends BaseController
{
    public function index(int $id = null)
    {
        $driversQuery = Driver::orderBy('vLastName', 'ASC');
        if (!is_null($id)) $driversQuery->where('iCompanyId', $id);
        $drivers = $driversQuery->get();

        return view('pages.drivers.list', compact('drivers'));
    }

    public function form(int $id = null)
    {
        $countries = Country::where('eStatus', 'active')->orderBy('vCountry', 'ASC')->get();
        $companies = Company::where('eStatus', 'active')->orderBy('vCompany', 'ASC')->get();
        $languages = Language::where('eStatus', 'active')->orderBy('vTitle', 'ASC')->get();
        $currencies = Currency::where('eStatus', 'active')->orderBy('vName', 'ASC')->get();

        $driver = null;
        if (!is_null($id)) {
            $driver = Driver::find($id);
        }

        return view('pages.drivers.form',
            [
                'driver' => $driver,
                'countries' => $countries,
                'companies' => $companies,
                'languages' => $languages,
                'currencies' => $currencies,
            ]);
    }

    public function create()
    {
        //TODO validation for create driver
        Driver::create(input()->all());
        return redirect(url('drivers'));
    }


    public function update(int $id)
    {
        //TODO validation for edit driver
        $driver = Driver::find($id);
        $driver->fill(input()->all());
        $driver->save();
        return redirect(url('drivers'));
    }

    public function delete(int $id)
    {
        if (!is_null($id)) {
//            User::destroy($id);
            Driver::find($id)->update(['eStatus' => 'Deleted']);
        }
        return redirect(url('drivers'));
    }

    public function reset(int $id)
    {
        $driver = Driver::find($id);
        $driver->update([
            'iTripId' => '0',
            'vTripStatus' => 'NONE'
        ]);
        $driver->save();
        return redirect(url('drivers'));
    }

    public function getDriverListAsJson(int $id = null)
    {
        $driversQuery = Driver::orderBy('vLastName', 'ASC');
        if (!is_null($id)) {
            $driversQuery->where('iCompanyId', $id)
                ->where('eStatus', '!=', 'Deleted');
        }
        $drivers = $driversQuery->get();

        return response()->json($drivers);
    }
}