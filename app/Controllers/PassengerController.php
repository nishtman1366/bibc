<?php


namespace App\Controllers;


use App\Models\Passenger;

class PassengerController extends Controller
{
    public function index()
    {
        $passengers = Passenger::orderBy('iUserId', 'DESC')->get();
        return view('pages.passengers.list', compact('passengers'));
    }

    public function getPassengerByPhone(string $phoneNumber)
    {
        $passenger = Passenger::where('vPhone', $phoneNumber)->get()->first();
        if (is_null($passenger)) return response()->httpCode(404)->json([]);
        return response()->json($passenger);
    }

    public function createPassengerByAjax()
    {
        $passenger = input()->all();
        $passenger = $this->create($passenger);
        return response()->json($passenger);
    }

    public function create(array $passenger)
    {
        return Passenger::create($passenger);
    }
}