<?php


namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Company;
use App\Models\Make;
use App\Models\Vehicle;
use App\Models\VehicleType;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('driver')
            ->with('company')
            ->with('make')
            ->with('model')
            ->orderBy('iDriverVehicleId', 'ASC')
            ->get();
        return view('pages.vehicles.list', compact('vehicles'));
    }

    public function form(int $id = null)
    {
        $makes = Make::orderBy('iMakeId', 'ASC')->get();
        $companies = Company::orderBy('iCompanyId', 'ASC')->get();
        $vehicleTypes = VehicleType::orderBy('vVehicleType', 'ASC')->get();
        $vehicle = null;
        if (!is_null($id)) {
            $vehicle = Vehicle::find($id);
        }
        return view('pages.vehicles.form', compact('vehicle', 'makes', 'companies', 'vehicleTypes'));
    }

    public function create()
    {
        dd(input()->all());
        //TODO validation for create company
        Vehicle::create(input()->all());
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

    public function checkLicenseDuplicate()
    {
        $number = input('number');
        $vehicle = Vehicle::where('vLicencePlate', $number)->where('eStatus', '!=', 'Deleted')->exists();
        if ($vehicle) return response()->httpCode(422)->json(['message' => 'شماره پلاک وارد شده قبلا در سیستم ثبت شده است.']);
        return response()->json([]);
    }
}