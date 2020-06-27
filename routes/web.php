<?php

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['middleware' => \App\Middlewares\Dashboard::class, 'prefix' => 'bibc'], function () {
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::get('/users', 'UserController@index')->name('users');
    Route::get('/users/new', 'UserController@form')->name('users.new');
    Route::post('/users', 'UserController@create')->name('users.create');
    Route::get('/users/{id}', 'UserController@form')->name('users.edit');
    Route::put('/users/{id}', 'UserController@update')->name('users.update');
    Route::delete('/users/{id}', 'UserController@delete')->name('users.delete');


    Route::get('/companies', 'CompanyController@index')->name('companies');


    Route::get('/areas', 'AreaController@index')->name('areas');


    Route::get('/drivers', 'DriverController@index')->name('drivers');


    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', 'DashboardController@index');
    });
});
