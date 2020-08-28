<?php

namespace App\Middlewares;


use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class Dashboard implements IMiddleware
{

    public function handle(Request $request): void
    {
        global $blade;
        $items = [
            ['name' => 'dashboard', 'title' => 'داشبورد', 'href' => url('dashboard'), 'icon' => 'fa-dashboard'],
            ['name' => 'users', 'title' => 'مدیران', 'href' => url('users'), 'icon' => 'fa-user'],
            ['name' => 'companies', 'title' => 'شرکت ها', 'href' => url('companies'), 'icon' => 'fa-building'],
            ['name' => 'areas', 'title' => 'مناطق', 'href' => url('areas'), 'icon' => 'fa-flag'],
            ['name' => 'drivers', 'title' => 'رانندگان', 'href' => url('drivers'), 'icon' => 'fa-group'],
            ['name' => 'vehicles', 'title' => 'وسایل نقلیه', 'href' => url('vehicles'), 'icon' => 'fa-car'],
            ['name' => 'vehicleTypes', 'title' => 'انواع خودرو', 'href' => url('vehicleTypes'), 'icon' => 'fa-tumblr'],
            ['name' => 'feeSettings', 'title' => 'تنظیمات نرخ', 'href' => url('feeSettings'), 'icon' => 'fa-money'],
            ['name' => 'packageTypes', 'title' => 'انواع بسته', 'href' => url('packageTypes'), 'icon' => 'fa-shopping-bag'],
            ['name' => 'passengers', 'title' => 'مسافران', 'href' => url('passengers'), 'icon' => 'fa-car'],
            ['name' => 'bookings', 'title' => 'لیست رزروها', 'href' => url('bookings'), 'icon' => 'fa-book'],
            ['name' => 'trips', 'title' => 'سفرها', 'href' => url('trips'), 'icon' => 'fa-map-marked'],
            ['name' => 'heatmap', 'title' => 'تراکم درخواست ها', 'href' => url('heatmap'), 'icon' => 'fa-map'],
        ];
        $sidebar['menu']['items'] = $items;
        $blade->share('sidebar', $sidebar);
    }
}