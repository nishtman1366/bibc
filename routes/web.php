<?php

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['middleware' => \App\Middlewares\Dashboard::class, 'prefix' => 'bibc'], function () {
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', 'UserController@index')->name('users');
        Route::get('/new', 'UserController@form')->name('users.new');
        Route::post('', 'UserController@create')->name('users.create');
        Route::get('/{id}', 'UserController@form')->name('users.edit');
        Route::post('/{id}', 'UserController@update')->name('users.update');
        Route::delete('/{id}', 'UserController@delete')->name('users.delete');
    });

    Route::group(['prefix' => 'companies'], function () {
        Route::get('/', 'CompanyController@index')->name('companies');
        Route::get('/new', 'CompanyController@form')->name('companies.new');
        Route::get('/documents', 'CompanyController@form')->name('companies.new');
        Route::post('', 'CompanyController@create')->name('companies.create');
        Route::get('/{id}', 'CompanyController@form')->name('companies.edit');
        Route::post('/{id}', 'CompanyController@update')->name('companies.update');
        Route::delete('/{id}', 'CompanyController@delete')->name('companies.delete');
    });

    Route::group(['prefix' => 'documents'], function () {
        Route::get('/{model}/{modelId}', 'DocumentController@index')->name('documents');
    });

    Route::group(['prefix' => 'areas'], function () {
        Route::get('/', 'AreaController@index')->name('areas');
        Route::get('/new', 'AreaController@form')->name('areas.new');
        Route::get('/documents', 'AreaController@form')->name('areas.new');
        Route::post('', 'AreaController@create')->name('areas.create');
        Route::get('/{id}', 'AreaController@form')->name('areas.edit');
        Route::post('/{id}', 'AreaController@update')->name('areas.update');
        Route::delete('/{id}', 'AreaController@delete')->name('areas.delete');
    });

    Route::group(['prefix' => 'drivers'], function () {
        Route::get('/{companyId?}', 'DriverController@index')->name('drivers');
        Route::get('/new', 'DriverController@form')->name('drivers.new');
        Route::post('', 'DriverController@create')->name('drivers.create');
        Route::get('/{id}', 'DriverController@form')->name('drivers.edit');
        Route::put('/{id}', 'DriverController@update')->name('drivers.update');
        Route::delete('/{id}', 'DriverController@delete')->name('drivers.delete');
    });


    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', 'DashboardController@index');
    });
});
