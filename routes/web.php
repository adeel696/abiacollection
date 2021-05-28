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


Route::get('/', 'Auth\LoginController@showLoginForm');
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout');

Route::get('/abia_ussd', 'USSDController@index');

Route::get('/upagent', 'UpdateAgentController@upAgent');

Route::group(['prefix' => 'cs', 'middleware' => 'auth'], function() {
	Route::get('/home', 'CS\HomeController@index');
	
	Route::get('/callcs', 'CS\PaymentController@CallCS');
	
	Route::get('/cslog/grid', 'CS\PaymentController@CSLogGrid');
	Route::get('/cslog', 'CS\PaymentController@getCSLog');
	
	Route::get('/paymentatin/grid', 'CS\PaymentController@PaymentAtinGrid');
	Route::get('/paymentatin', 'CS\PaymentController@getPaymentAtin');
	
	Route::get('/import', 'CS\PaymentController@getImportPaymentAtin');
	Route::post('/importpaymentatin', 'CS\PaymentController@importPaymentAtin');
});

Route::group(['middleware' => ['auth']], function() {
	Route::get('/home', 'HomeController@index');
	
	
	Route::get('/newagent', 'AgentController@getNewAgent');
	Route::post('/importnewagent', 'AgentController@importNewAgent');

	Route::get('/agent/grid', 'AgentController@getAgentGrid');
	Route::get('/agent', 'AgentController@getAgent');
	
	Route::post('/payment/recallibris/{payment}', 'PaymentController@RecallIbris');
	Route::get('/payment/grid', 'PaymentController@getPaymentGrid');
	Route::get('/payment', 'PaymentController@getPayment');
	
});
