<?php


namespace App\Controllers;

use App\Models\VehicleType;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = VehicleType::with('area')->orderBy('iVehicleTypeId', 'ASC')->get();
        return view('pages.vehicleTypes.list', compact('vehicleTypes'));
    }
}