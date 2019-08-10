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
    # Make Webhook
    Route::post('/', ['uses' => 'WebHookPaypal@store'])->name('webhooks.store');
    # Show all Webhook
    Route::get('/', ['uses' => 'WebHookPaypal@getAllWebHooks'])->name('webhooks.index');
    # Delet Specific Webhook
    Route::delete('{webhook_id}', ['uses' => 'WebHookPaypal@deleteWebHook'])->name('webhooks.delete');
    # Update Specific WebHook TODO: Revisar // https: //api.sandbox.paypal.com/v1/notifications/webhooks/9KW74522UU6559937
    Route::patch('{webhook_id}', ['uses' => 'WebHookPaypal@updateWebHook'])->name('webhooks.update');
    # Show Specific webhook
    Route::get('{webhook_id}', ['uses' => 'WebHookPaypal@showWebHook'])->name('webhooks.show');
    Route::get('show/{webhook_id}', ['uses' => 'WebHookPaypal@show'])->name('webhooks.shows');
    # Get Event Types of specific Webhook
    Route::get('{webhook_id}/event-types', ['uses' => 'WebHookPaypal@showEventTypesWebHook'])->name('webhooks.showeventtype');
});

# verify-webhook-signature
Route::post('verify-webhook-signature', ['uses' => 'WebHookPaypal@verifySignature'])->name('webhooks.verifysignature');

# verify-webhook-signature
Route::get('webhooks-event-types', ['uses' => 'WebHookPaypal@eventSignature'])->name('webhooks.eventsignature');
Route::group(['prefix' => 'webhooks-events'], function () {
    # Webhooks - events
    Route::get('/', ['uses' => 'WebHookPaypal@getAllWebHooksEvents'])->name('webhooks.allwebhooksevents');
    # Webhook sepecific events
    Route::get('{event_id}', ['uses' => 'WebHookPaypal@getWebHookEvent'])->name('webhooks.webhooksevent');
    # Webhook sepecific events
    Route::post('{event_id}/resend', ['uses' => 'WebHookPaypal@resendWebHookEvent'])->name('webhooks.resendwebhooksevent');
});
# verify-webhook-signature
Route::post('simulate-event', ['uses' => 'WebHookPaypal@simulateEvent'])->name('webhooks.simulateEvent');

# Special: Delete All
Route::delete('paypal/delete/all-webhooks', ['uses' => 'WebHookPaypal@deleteAllWebHooks'])->name('webhooks.deleteall');

Route::post('paypal/payment-sale-completed', ['uses' => 'PayPalController@webhooksPaymentSaleCompleted']);

Route::post('paypal/checkout', ['uses' => 'PayPalController@checkout']);
