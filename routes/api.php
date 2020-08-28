<?php

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['prefix' => '/bibc/api'], function () {
    Route::get('/makes/{makeId}/models', 'ModelController@getModelListAsJson');
    Route::get('/companies/{id}/drivers', 'DriverController@getDriverListAsJson');


    Route::post('/vehicles/checkLicense', 'VehicleController@checkLicenseDuplicate');

    Route::get('passengers/byPhone/{phoneNumber}', 'PassengerController@getPassengerByPhone');
    Route::post('/passengers/quickCreate', 'PassengerController@createPassengerByAjax');

    Route::get('bookings/byUserId/{userId}', 'BookingController@getBookingByUserId');

    Route::post('/bookings/calculateDistanceAndFare', 'BookingController@calculateDistanceAndFare');

    Route::post('/drivers/driverListByLocation', 'DriverController@driverListByLocation');

    /*
     * request payment by driver
     */
    Route::post('/drivers/payments/request', 'PaymentController@requestPayment');

});