<?php


namespace App\Controllers;

use App\Models\Company;
use App\Models\Make;
use App\Models\Model;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

class VehicleController extends Controller
{
    public $driver = false;

    /**
     * VehicleController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->driver = false;
        if (isset($_SESSION) && key_exists('user', $_SESSION) && url()->contains('driver')) {
            $this->driver = $_SESSION['user']['driver']->data;
        }
    }

    public function index()
    {
        $driver = $this->driver;
        $vehicles = Vehicle::with('driver')
            ->with('company')
            ->with('make')
            ->with('model')
            ->where(function ($query) use ($driver) {
                if ($driver) {
                    $query->where('iDriverId', $driver['iDriverId']);
                }
            })
            ->orderBy('iDriverVehicleId', 'ASC')
            ->get();
        if ($driver) {
            return view('pages.frontend.panel.driver.vehicles.list', compact('vehicles'));
        }
        return view('pages.vehicles.list', compact('vehicles'));
    }

    public function form(int $id = null)
    {
        $driver = $this->driver;
        $makes = Make::orderBy('iMakeId', 'ASC')->get();
        $vehicleTypes = VehicleType::orderBy('vVehicleType', 'ASC')->get();
        $vehicle = null;
        $models = null;
        if (!is_null($id)) {
            $vehicle = Vehicle::find($id);
            if ($driver && $vehicle->iDriverId !== $driver['iDriverId']) {
                throw new NotFoundHttpException('No access');
            }
            $models = Model::where('iMakeId', $vehicle->iMakeId)->where('eStatus', 'Active')->get();
        }
        if ($driver) {
            return view('pages.frontend.panel.driver.vehicles.form', compact('vehicle', 'makes', 'vehicleTypes', 'models'));
        }
        $companies = Company::orderBy('iCompanyId', 'ASC')->get();
        return view('pages.vehicles.form', compact('vehicle', 'makes', 'companies', 'vehicleTypes', 'models'));
    }

    public function create()
    {
        //TODO validation for create company
        $driver = $this->driver;
        $input = input()->all();
        $redirect = url('vehicles');
        if ($driver) {
            $input['iCompanyId'] = $driver['iCompanyId'];
            $input['iDriverId'] = $driver['iDriverId'];
            $redirect = url('driver.vehicles');
        }
        Vehicle::create($input);
        return redirect($redirect);
    }

    public function update(int $id)
    {
        //TODO validation for edit vehicle
        $driver = $this->driver;
        $input = input()->all();
        $redirect = url('vehicles');
        if ($driver) {
            $input['iCompanyId'] = $driver['iCompanyId'];
            $input['iDriverId'] = $driver['iDriverId'];
            $redirect = url('driver.vehicles');
        }
        $vehicle = Vehicle::find($id);
        $vehicle->fill($input);
        $vehicle->save();
        return redirect($redirect);
    }

    public function delete(int $id)
    {
        $driver = $this->driver;
        $redirect = url('vehicles');
        if ($driver) {
            $input['iCompanyId'] = $driver['iCompanyId'];
            $input['iDriverId'] = $driver['iDriverId'];
            $redirect = url('driver.vehicles');
        }
        if (!is_null($id)) {
            $vehicle = Vehicle::find($id);
            if ($driver && $vehicle->iDriverId !== $driver['iDriverId']) {
                throw new NotFoundHttpException('No access');
            }
            $vehicle->update(['eStatus' => 'Deleted']);
            return redirect($redirect);
        }
        return redirect($redirect);
    }

    public function checkLicenseDuplicate()
    {
        $number = input('number');
        $vehicle = Vehicle::where('vLicencePlate', $number)->where('eStatus', '!=', 'Deleted')->exists();
        if ($vehicle) return response()->httpCode(422)->json(['message' => 'شماره پلاک وارد شده قبلا در سیستم ثبت شده است.']);
        return response()->json([]);
    }
}