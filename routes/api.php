<?php

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['prefix' => '/bibc/api'], function () {
    Route::get('/makes/{makeId}/models', 'ModelController@getModelListAsJson');
    Route::get('/companies/{id}/drivers', 'DriverController@getDriverListAsJson');


    Route::post('/vehicles/checkLicense', 'VehicleController@checkLicenseDuplicate');
});