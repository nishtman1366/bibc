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
        ];
        $sidebar['menu']['items'] = $items;
        $blade->share('sidebar', $sidebar);
    }
}