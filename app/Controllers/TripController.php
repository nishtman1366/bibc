<?php


namespace App\Controllers;


use App\Models\Trip;

class TripController extends Controller
{
    public $driver = false;

    /**
     * VehicleController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->driver = false;
        if (count($this->user) !== 0 && url()->contains('driver')) {
            $this->driver = $_SESSION['user']['driver']->data;
        }
    }

    public function index()
    {
        $driver = $this->driver;
        $trips = Trip::with('passenger')
            ->with('driver')
            ->with('driver.company')
            ->with('vehicle')
            ->with('vehicleType')
            ->where(function ($query) use ($driver) {
                if ($driver) {
                    $query->where('iDriverId', $driver['iDriverId']);
                }
            })
            ->orderBy('iTripId', 'DESC')
            ->get();
        if ($driver) {
            return view('pages.frontend.panel.driver.trips.list', compact('trips'));
        }
        return view('pages.trips.list', compact('trips'));
    }
}