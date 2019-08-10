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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'webhooks'], function () {

    Route::post('/', ['uses' => 'WebHookPaypal@store'])->name('webhooks.store');
    Route::get('/', ['uses' => 'WebHookPaypal@getAllWebHooks'])->name('webhooks.allwebhooks');
    Route::get('/{webhookId}', ['uses' => 'WebHookPaypal@showWebHook'])->name('webhooks.show');
    Route::patch('/{webhookId}', ['uses' => 'WebHookPaypal@updateWebHook'])->name('webhooks.update');
    Route::delete('/{webhookId}', ['uses' => 'WebHookPaypal@deleteWebHook'])->name('webhooks.delete');
    Route::get('/{webhookId}/event-types', ['uses' => 'WebHookPaypal@showEventTypesWebHook'])->name('webhooks.showeventtype');

    Route::post('/verify-webhook-signature', ['uses' => 'WebHookPaypal@verifySignature'])->name('webhooks.verifysignature');
    Route::get('/list-events', ['uses' => 'WebHookPaypal@getListAllWebhookEventType']);
    Route::delete('/delete/all', ['uses' => 'WebHookPaypal@deleteAllWebHooks'])->name('webhooks.deleteall');


    Route::post('paypal/payment-sale-completed', ['uses' => 'PayPalController@webhooksPaymentSaleCompleted']);
});
Route::post('paypal/checkout', ['uses' => 'PayPalController@checkout']);
