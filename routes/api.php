<?php

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['prefix' => '/bibc/api'], function () {
    Route::get('/makes/{makeId}/models', 'ModelController@getModelListAsJson');
    /*
     * دریافت لیست رانندگان یک شرکت
     */
    Route::get('/companies/{id}/drivers', 'DriverController@getDriverListAsJson');
    /*
     * بررسی شماره پلاک وسیله نقلیه
     */
    Route::post('/vehicles/checkLicense', 'VehicleController@checkLicenseDuplicate');
    /*
     * دریافت اطلاعات مسافر بوسیله شماره تلفن
     */
    Route::get('passengers/byPhone/{phoneNumber}', 'PassengerController@getPassengerByPhone');
    /*
     * ساخت سریع مسافر
     */
    Route::post('/passengers/quickCreate', 'PassengerController@createPassengerByAjax');
    /*
     * دریافت رزروهای یک مسافر
     */
    Route::get('bookings/byUserId/{userId}', 'BookingController@getBookingByUserId');
    /*
     * محاسبه مسافت و هزینه سفر
     */
    Route::post('/bookings/calculateDistanceAndFare', 'BookingController@calculateDistanceAndFare');
    /*
     * دریافت لیست رانندگان بر اساس موقعیت
     */
    Route::post('/drivers/driverListByLocation', 'DriverController@driverListByLocation');
    /*
     * دریافت لیست رانندگان آنلاین یک شرکت
     *
     * اطلاعات شرکت مورد نظر بوسیله header موجود در api
     * شناسایی میشود.
     */
    Route::get('company/drivers/online', 'DriverController@getOnlineDrivers');
    /*
     * درخواست پرداخت برای رانندگان
     */
    Route::post('/drivers/payments/request', 'PaymentController@requestPayment');
    /*
     * تسویه حساب با رانندگان
     */
    Route::post('companies/trips/settle', 'PaymentController@tripsSettlement');

});