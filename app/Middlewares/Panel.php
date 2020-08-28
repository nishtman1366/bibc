<?php

namespace App\Middlewares;


use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class Panel implements IMiddleware
{

    public function handle(Request $request): void
    {
        global $blade;
        $user = [];
        if (key_exists('user', $_SESSION)) {
            $user = $_SESSION['user'];
        }
        $blade->share('user', $user);
    }
}