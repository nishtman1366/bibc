<?php


namespace App\Controllers;


class panelController extends Controller
{
    public function index()
    {
        $user = $this->user;
        return view('pages.frontend.panel.index', compact('user'));
    }
}