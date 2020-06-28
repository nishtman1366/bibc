<?php

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['middleware' => \App\Middlewares\Dashboard::class, 'prefix' => 'bibc'], function () {
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', 'UserController@index')->name('users');
        Route::get('/new', 'UserController@form')->name('users.new');
        Route::post('', 'UserController@create')->name('users.create');
        Route::get('/{id}', 'UserController@form')->name('users.edit');
        Route::put('/{id}', 'UserController@update')->name('users.update');
        Route::delete('/{id}', 'UserController@delete')->name('users.delete');
    });

    Route::group(['prefix' => 'companies'], function () {
        Route::get('/', 'CompanyController@index')->name('companies');
        Route::get('/new', 'CompanyController@form')->name('companies.new');
        Route::post('', 'CompanyController@create')->name('companies.create');
        Route::get('/{id}', 'CompanyController@form')->name('companies.edit');
        Route::put('/{id}', 'CompanyController@update')->name('companies.update');
        Route::delete('/{id}', 'CompanyController@delete')->name('companies.delete');
    });

    Route::get('/areas', 'AreaController@index')->name('areas');


    Route::get('/drivers', 'DriverController@index')->name('drivers');


    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', 'DashboardController@index');
    });
});
