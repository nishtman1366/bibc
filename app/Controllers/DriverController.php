<?php


namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Models\Company;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Language;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;

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

    public function driverListByLocation()
    {
        $input = input()->all();
        $limitOnlineTime = Carbon::now()->subMinutes(50)->format('Y-m-d H:i:s');

        $sql = sprintf("SELECT ROUND(( 3959 * acos( cos( radians(%s) ) * cos( radians( vLatitude ) ) * cos( radians( vLongitude ) - radians(%s) ) + sin( radians(%s) ) * sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver` WHERE (vLatitude != '' AND vLongitude != '' AND vAvailability = 'Available' AND vTripStatus != 'Active' AND eStatus='active' AND tLastOnline > '%s') HAVING distance < %s ORDER BY `register_driver`.`tLastOnline` ASC",
            $input['lat'], $input['lng'], $input['lat'], $limitOnlineTime, $input['radius']);

        $drivers = Manager::select($sql);
        $list = [];
        foreach ($drivers as $driver) {
            $list[] = [
                'id' => $driver->iDriverId,
                'name' => $driver->vName.' '.$driver->vLastName,
                'location' => ['lat' => $driver->vLatitude, 'lng' => $driver->vLongitude],
                'address' => $driver->vCaddress
            ];
        }
        return response()->json($list);
    }
}