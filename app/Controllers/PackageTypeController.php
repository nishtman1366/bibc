<?php


namespace App\Controllers;


use App\Models\PackageType;

class PackageTypeController extends Controller
{
    public function index()
    {
        $packageTypes = PackageType::orderBy('iPackageTypeId', 'ASC')->get();
        return view('pages.packageTypes.list', compact('packageTypes'));
    }
}