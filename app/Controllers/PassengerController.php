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
}