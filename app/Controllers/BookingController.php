<?php


namespace App\Controllers;


use App\Models\Booking;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('passenger')
            ->with('company')
            ->with('driver')
            ->with('vehicleType')
            ->with('trip')
            ->orderBy('iCabBookingId', 'DESC')
            ->get();

        return view('pages.bookings.list', compact('bookings'));
    }

    public function form()
    {
        return view('pages.bookings.form');
    }
}