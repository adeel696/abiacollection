<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/routes', 'Api\HomeController@allRoutes');
Route::get('/routes/{company_id}', 'Api\HomeController@allRoutesByCompany');

Route::group(['prefix' => 'v1'], function() use ($router) {	
	
	Route::group(['middleware' => 'api.auth'], function() use ($router) {
	
		Route::get('/TerminalStates/{CompanyID}', 'Api\ThirdAPIsController@TerminalStates');
		Route::get('/AllRouteDetails/{CompanyID}', 'Api\ThirdAPIsController@AllRouteDetails');
		Route::get('/GeneralSetting/{CompanyID}', 'Api\ThirdAPIsController@GeneralSetting');
		Route::get('/TerminalsWithState/{CompanyID}', 'Api\ThirdAPIsController@TerminalsWithState');
		Route::get('/AvaiableBusWithSeatPrice/{CompanyID}', 'Api\ThirdAPIsController@AvaiableBusWithSeatPrice');
		Route::post('/SaveBookingOrder/{CompanyID}', 'Api\ThirdAPIsController@SaveBookingOrder');
		Route::get('/BookingStatus/{CompanyID}', 'Api\ThirdAPIsController@BookingStatus');
		Route::get('/ActivateLockSeat/{CompanyID}', 'Api\ThirdAPIsController@ActivateLockSeat');
		Route::get('/ValidatePhone/{CompanyID}', 'Api\ThirdAPIsController@ValidatePhone');
		
		
		Route::get('Company/{CompanyID}', 'Api\HomeController@GetCompany');
		Route::get('Cities/{CompanyID}', 'Api\HomeController@GetCities');
		Route::get('CityTerminals/{CompanyID}', 'Api\HomeController@GetCityTerminals');
		Route::get('FromToCityTerminals/{CompanyID}', 'Api\HomeController@GetFromToCityTerminals');
		Route::get('Deals/{CompanyID}', 'Api\HomeController@GetDeals');
		Route::get('Banks/{CompanyID}', 'Api\HomeController@GetBanks');
		Route::get('Policies/{CompanyID}', 'Api\HomeController@GetPolicies');
		Route::get('Abouts/{CompanyID}', 'Api\HomeController@GetAbouts');
		Route::get('Services/{CompanyID}', 'Api\HomeController@GetServices');
		Route::get('Schedules/{CompanyID}', 'Api\HomeController@GetSchedules');
		Route::post('/SaveBooking/{CompanyID}', 'Api\HomeController@SaveBooking');
		//Route::get('/BookingStatus/{CompanyID}', 'Api\HomeController@BookingStatus');
		Route::get('/LockSeat/{CompanyID}', 'Api\HomeController@LockSeat');
		Route::get('/ValidatePhoneDetail/{CompanyID}', 'Api\HomeController@ValidatePhone');

	});
});


Route::group(['prefix' => 'coralpay'], function() use ($router) {	
	
	Route::group(['middleware' => 'api.coralpay'], function() use ($router) {
	
		Route::post('/GetDetails', 'Api\CoralpayController@GetDetails');
		Route::post('/PaymentNotification', 'Api\CoralpayController@PaymentNotification');

	});
});

Route::group(['prefix' => 'guo'], function() use ($router) {	
	
	Route::group(['middleware' => 'api.guo'], function() use ($router) {
	
		Route::get('/TransactionQuery/{traceId}', 'Api\GUOController@TransactionQuery');

	});
});