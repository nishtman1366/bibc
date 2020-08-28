<?php


namespace App\Controllers;


use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.frontend.home');
    }
}