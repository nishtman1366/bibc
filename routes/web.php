<?php

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['middleware' => \App\Middlewares\Dashboard::class, 'prefix' => 'bibc'], function () {
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::get('/users', 'UserController@index')->name('users');


    Route::get('/companies', 'CompanyController@index')->name('companies');


    Route::get('/areas', 'AreaController@index')->name('areas');


    Route::get('/drivers', 'DriverController@index')->name('drivers');


    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', 'DashboardController@index');
    });
});
