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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'PaypalController@index');
Route::get('/subscribe/paypal', 'PaypalController@paypalRedirect')->name('paypal.redirect');
Route::get('/subscribe/paypal/return', 'PaypalController@paypalReturn')->name('paypal.return');

// Route::get('create_paypal_plan', 'PaypalController@create_plan');


// 
// // route for processing payment
// Route::post('paypal', 'PaymentController@payWithpaypal');
// // route for check status of the payment
// Route::get('status', 'PaymentController@getPaymentStatus');
Route::group(['prefix' => 'webhooks'], function () {
    Route::get('/', ['uses' => 'WebHookPaypal@index'])->name('webhooks');
    Route::get('/{webhookID}', ['uses' => 'WebHookPaypal@getSpecificWebHookByID']);
    Route::get('/list-events', ['uses' => 'WebHookPaypal@getListAllWebhookEventType']);
    Route::get('/delete/all', ['uses' => 'WebHookPaypal@deleteAllWebHooks']);

    // Make Web Hook
    Route::post('/create', ['uses' => 'WebHookPaypal@store'])->name('webhooks.create');
    // Route::post('/create', ['uses' => 'WebHookPaypal@storeWebHook'])->name('webhooks.create');

    Route::get('/validate/WebHook', ['uses' => 'WebHookPaypal@validateWebHook']);
    Route::post('/validate/WebHook', ['uses' => 'WebHookPaypal@validateWebHook']);

    # API
});
