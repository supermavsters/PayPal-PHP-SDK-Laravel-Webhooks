<?php

namespace App\Http\Controllers;

use App\Models\User;
use PayPal\Api\Plan;
use PayPal\Api\Patch;

use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Currency;
use Illuminate\Http\File;
use PayPal\Api\Agreement;

// use to process billing agreements
use PayPal\Rest\ApiContext;
use Illuminate\Http\Request;
use PayPal\Api\PatchRequest;
use PayPal\Api\RedirectUrls;

use PayPal\Api\WebhookEvent;
use PayPal\Common\PayPalModel;
use PayPal\Api\ShippingAddress;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\MerchantPreferences;
use App\Http\Controllers\Controller;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\VerifyWebhookSignature;
use App\Http\Controllers\BaseController;

class PayPalController extends BaseController
{
    // Variables
    private $apiContext;
    private $mode;
    private $client_id;
    private $plan_id;
    private $secret;
    private $webhooks_id;
    private $base_url;

    // Create a new instance with our paypal credentials
    public function __construct()
    {
        // Set Url - Callback
        $this->base_url = config('paypal.base_url');
        if (self::isEmptyOrNull($this->base_url)) {
            self::initVariables();
        } else {
            self::sendError('The base URL not exist');
        }
    }

    protected function initVariables()
    {
        // Detect if we are running in live mode or sandbox
        if (config('paypal.settings.mode') === 'live') {
            $this->client_id = config('paypal.live_client_id');
            $this->secret = config('paypal.live_secret');
            $this->plan_id = config('paypal.live_plan_id');
        } else {
            $this->client_id = config('paypal.sandbox_client_id');
            $this->secret = config('paypal.sandbox_secret');
            $this->plan_id = config('paypal.sandbox_plan_id');
        }
        $this->webhooks_id = config('paypal.webhooks.payment_sale_completed');

        // Set the Paypal API Context/Credentials
        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->client_id, $this->secret));
        // $this->apiContext->setConfig(config('paypal.settings'));
        $this->apiContext->setConfig(config('paypal'));

        // $redirectUrls = new RedirectUrls();
        // $redirectUrls->setReturnUrl(self::getUrlBase() . '/api/getPaymentStatus?invoice_id=' . $request->invoice_id)
        //     ->setCancelUrl(self::getUrlBase() . '/api/express_checkout_cancelled?invoice_id=' . $request->invoice_id);
    }

    public function index()
    {
        // Show View
        return view('paywithpaypal');
    }


    public function paypalRedirect()
    {
        // Create new agreement
        $agreement = new Agreement();
        $agreement->setName('App Name Monthly Subscription Agreement')
            ->setDescription('Basic Subscription')
            ->setStartDate(\Carbon\Carbon::now()->addMinutes(5)->toIso8601String());

        // Set plan id
        $plan = new Plan();
        $plan->setId($this->plan_id);
        $agreement->setPlan($plan);

        // Add payer type
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            // Create agreement
            $agreement = $agreement->create(self::getApiContext());

            // Extract approval URL to redirect user
            $approvalUrl = $agreement->getApprovalLink();

            return redirect($approvalUrl);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            echo $ex->getCode();
            echo $ex->getData();
            die($ex);
        } catch (Exception $ex) {
            die($ex);
        }
    }

    public function paypalReturn(Request $request)
    {

        $token = $request->token;
        $agreement = new \PayPal\Api\Agreement();

        try {
            // Execute agreement
            $result = $agreement->execute($token, self::getApiContext());
            dd($result);
            // $user = Auth::user();
            // $user->role = 'subscriber';
            // $user->paypal = 1;
            if (isset($result->id)) {
                // $user->paypal_agreement_id = $result->id;
                echo 'id: ' . $result->id;
            }
            // $user->save();

            // echo "\n" . 'New Subscriber Created and Billed';
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            echo 'You have either cancelled the request or your session has expired';
        }
    }



    public function create_plan()
    {

        // Create a new billing plan
        $plan = new Plan();
        $plan->setName('App Name Monthly Billing')
            ->setDescription('Monthly Subscription to the App Name')
            ->setType('infinite');

        // Set billing plan definitions
        $paymentDefinition = new PaymentDefinition();

        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency('Month')
            ->setFrequencyInterval('1')
            ->setCycles('0')
            ->setAmount(new Currency(array('value' => 9, 'currency' => 'USD')));

        // Set merchant preferences
        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl(self::getUrlBase() . '/subscribe/paypal/return')
            ->setCancelUrl(self::getUrlBase() . '/subscribe/paypal/return')
            ->setAutoBillAmount('yes')
            ->setInitialFailAmountAction('CONTINUE')
            ->setMaxFailAttempts('0');

        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        //create the plan
        try {
            $createdPlan = $plan->create($this->apiContext);

            try {
                $patch = new Patch();
                $value = new PayPalModel('{"state":"ACTIVE"}');
                $patch->setOp('replace')
                    ->setPath('/')
                    ->setValue($value);
                $patchRequest = new PatchRequest();
                $patchRequest->addPatch($patch);
                $createdPlan->update($patchRequest, $this->apiContext);
                $plan = Plan::get($createdPlan->getId(), $this->apiContext);

                // Output plan id
                echo 'Plan ID:' . $plan->getId();
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                echo $ex->getCode();
                echo $ex->getData();
                die($ex);
            } catch (Exception $ex) {
                die($ex);
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            echo $ex->getCode();
            echo $ex->getData();
            die($ex);
        } catch (Exception $ex) {
            die($ex);
        }
    }



    public function checkout(Request $request)
    {

        // validate input
        $rules = [
            'paymentID' => 'required',
        ];

        $credentials = $request->only(
            'paymentID'
        );

        $validator = Validator::make($credentials, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 400);
        }
        // get payment detail and verify
        try {
            $payment = Payment::get($request->get('paymentID'), self::getApiContext());
        } catch (PayPalConnectionException $e) {
            $error_json = json_decode($e->getData(), 1);
            print_r($error_json);
            exit(1);
        }

        // generate and email pdf

    }

    /**
     * Gets
     */
    public function getApiContext()
    {
        return $this->apiContext;
    }

    public function getUrlBase()
    {
        return $this->base_url;
    }
    public function getWebHookID()
    {
        return $this->webhooks_id;
    }
}
