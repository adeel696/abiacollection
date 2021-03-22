<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/login', function () {
    return view('login');
});

Route::get('/', function () {
    return view('login');
});
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout');

Route::get('/abia_ussd', 'USSDController@index');

Route::group(['middleware' => ['auth']], function() {
	Route::get('/home', 'HomeController@index');
	
	Route::resource('/shop', 'ShopController');
	
	Route::get('/newagent', 'AgentController@getNewAgent');
	Route::post('/importnewagent', 'AgentController@importNewAgent');

	Route::get('/agent/grid', 'AgentController@getAgentGrid');
	Route::get('/agent', 'AgentController@getAgent');
	
	Route::get('/payment/grid', 'PaymentController@getPaymentGrid');
	Route::get('/payment', 'PaymentController@getPayment');
});
