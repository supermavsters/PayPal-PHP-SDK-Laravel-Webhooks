<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use \PayPal\Api\WebhookEvent;
use PhpParser\Node\Stmt\Return_;
use \PayPal\Api\VerifyWebhookSignature;
use \PayPal\Api\Webhook;

class WebHookPaypal extends PayPalController
{
    private $webhook = null;
    // Init
    public function __construct()
    {
        parent::__construct();
        // Init variables
        self::initVariables();
    }

    protected function initVariables()
    {
        /** @var \PayPal\Api\Webhook $this->webhook */
        $this->webhook = new \PayPal\Api\Webhook();
    }

    public function index()
    {
        $webhooks = self::getAllWebHooks();
        // Return All Web Hooks registers
        return view('paywithpaypal', compact('webhooks'));
    }

    public function getAllWebHooks()
    {
        try {
            $output = \PayPal\Api\Webhook::getAll(parent::getApiContext());
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error get all webhooks', [$ex->getMessage()]);
        }
        return parent::sendResponse($output);
    }

    public function deleteWebHook($webhookId)
    {
        try {
            if (isset($webhookId)) {
                // Delete the webhook
                $webhook = self::getSpecificWebHookByID($webhookId, false);
                $output = $webhook->delete(parent::getApiContext());
            }
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error get or delete the webhooks', [$ex->getMessage()]);
        }
        return parent::sendResponse('The Webhook deleted', 'The webhook has been deleted', true);
    }

    public function deleteAllWebHooks()
    {
        try {
            $webhookList = \PayPal\Api\Webhook::getAll(parent::getApiContext());
            if (isset($webhookList)) {
                // For each webhooks in the system
                foreach ($webhookList->getWebhooks() as $webhook) {
                    // Delete the webhook
                    $webhook->delete(parent::getApiContext());
                }
            }
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error get or delete all webhooks', [$ex->getMessage()]);
        }
        // Redirect to home (Webhooks - Home)
        // return redirect()->route('webhooks');
        return parent::sendResponse('All Webhooks deleted', 'All webhooks has been deleted', true);
    }

    public function getListAllWebhookEventType()
    {
        try {
            $output = \PayPal\Api\WebhookEventType::availableEventTypes(parent::getApiContext());
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error get all Webhook Event Type', [$ex->getMessage()]);
        }
        return parent::sendResponse($output);
    }

    public function getSpecificWebHook($webhook)
    {
        // Check if the Object (Webhock Exist)
        if ($webhook) {
            $webhookId = $webhook->getId();
            if ($webhookId) {
                try {
                    $output = \PayPal\Api\Webhook::get($webhookId, parent::getApiContext());
                } catch (Exception $ex) {
                    return parent::sendErrorMain('There was an error get the data', [$ex->getMessage()]);
                }
                return parent::sendResponse($output);
            } else {
                return parent::sendErrorMain('There was an error get the ID of the Webhook', [$ex->getMessage()]);
            }
        } else {
            return parent::sendErrorMain('There was an error get the Webhook', [$ex->getMessage()]);
        }
    }

    public function getSpecificWebHookByID($webhookID, $json = true)
    {
        // Check if the ID Exist
        if ($webhookID) {
            try {
                $output = \PayPal\Api\Webhook::get($webhookID, parent::getApiContext());
            } catch (Exception $ex) {
                return parent::sendErrorMain('There was an error get the data', [$ex->getMessage()]);
            }
            if ($json) {
                return parent::sendResponse($output);
            } else {
                return ($output);
            }
        } else {
            return parent::sendErrorMain('There was an error get the ID of the Webhook', [$ex->getMessage()]);
        }
    }

    /**
     * Edit - webhook
     */

    public function updateWebHook(Request $request, $webhookId)
    {
        // Create a stream
        if (isset($webhookId)) {
            if (!$request->getContent()) {
                return parent::sendError('Incorrect Data', ['No exist Content'], 400);
            }
            // // Get Data Diferentes ways to get the object (Webhook)
            // $patchRequest = self::getPatchRequest($request->getContent());
            // // ### Get Webhook #1
            // $url = 'http://public.test/api/webhooks/show/' . $webhookId;
            // $getDataWebHook = self::callGetWebhook($url);
            // // ### Get Webhook #2
            // // $webhook =  \PayPal\Api\Webhook::get($webhookId, parent::getApiContext());
            // // ### Get Webhook #3
            // // $webhook = new \PayPal\Api\Webhook($jsonDataWebHook);
            // // ### Get Webhook #4
            // $webhook = new Webhook();
            // $webhook = $webhook->fromJson($getDataWebHook);

            // // Set Data
            // $webhook = self::setData($webhook, $webhookId, $patchRequest);
            // For Sample Purposes Only.
            try {
                /** @var Array $headers */
                $headers = getallheaders();

                $authorization = ($headers['Authorization']);
                // $authorization = "Bearer A21AAGFc9ljkO1Cz_FDZKzaeZ-ChtvHA_xXp-0mRt9mHNg3w6IlAeTSZoVpbPFrOAApqbT60QX5XvV9JAoT4PIoMXuxZZS6Vg";

                $url = 'https://api.sandbox.paypal.com/v1/notifications/webhooks/' . $webhookId;
                $post = self::makeJSON($request->getContent()); // Array of data with a trigger
                $output = self::jwt_request($url, $authorization, $post); // Send or retrieve data

                // $output = $webhook->update($patchRequest, parent::getApiContext()); //, 'http://public.test/api/webhooks/' . $webhookId);
                // Show values
                // return parent::sendResponse($output);
                return response()->json($output, 200);
            } catch (Exception $ex) {
                return parent::sendErrorMain('There was an error get or update the webhooks', self::PaypalError($ex));
            }
            return parent::sendResponse('The Webhook has been update', 'The webhook has been update', true);
        }
    }

    function jwt_request($url, $token, $post)
    {

        header('Content-Type: application/json'); // Specify the type of data
        $ch = curl_init($url); // Initialise cURL
        $post = json_encode($post); // Encode the data array into a JSON string
        $authorization = "Authorization: " . $token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization)); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection

        return json_decode($result);
    }



    private function makeJSON($request)
    {
        // Remove Spaces
        $string = trim(preg_replace('/\s\s+/', '', $request));
        // Conver Object
        $WorkingArray = json_decode(json_encode($string), true);
        // Flag Object JSON
        $jsonData = (json_decode($WorkingArray, true));
        return $jsonData;
    }


    private function getPatchRequest($request)
    {

        $jsonData = self::makeJSON($request);

        // Find the Web Hook
        // New URL
        $patch = new \PayPal\Api\Patch();
        $patch->setOp($jsonData[0]['op']) //"replace")
            ->setPath($jsonData[0]['path']) //"/url")
            ->setValue($jsonData[0]['value']);
        // New Events
        $webhookEventTypesTemp = [];
        foreach ($jsonData[1]['value'] as $key => $value) {
            $webhookEventTypesTemp["name"] = $value['name'];
        }
        $WorkingArray = json_decode(json_encode($webhookEventTypesTemp), true);

        $patch2 = new \PayPal\Api\Patch();
        $patch2->setOp($jsonData[1]['op']) //"replace")
            ->setPath($jsonData[1]['path']) //"/url")
            ->setValue($WorkingArray);

        $patchRequest = new \PayPal\Api\PatchRequest();
        $patchRequest->addPatch($patch)->addPatch($patch2);
        return $patchRequest;
    }


    private function callGetWebhook($url)
    {
        $file = file_get_contents($url);
        // Remove Spaces
        $string = trim(preg_replace('/\s\s+/', '', $file));
        // Conver Object
        $WorkingArray = json_decode(json_encode($string), true);
        // Flag Object JSON
        $jsonDataWebHook = json_encode(json_decode($WorkingArray, true));
        return $jsonDataWebHook;
    }

    private function setData($tempWebHook, $webhookId, $patchRequest)
    {
        $tempWebHook->setId($webhookId);
        // dd($patchRequest);
        $tempWebHook->setUrl($patchRequest[0]['value']);
        $webhookEventTypesTemp = [];
        foreach ($patchRequest[1]['value'] as $key => $value) {
            $webhookEventTypesTemp[] = new \PayPal\Api\WebhookEventType('{"name":"' . $value['name'] . '"}');
        }
        $tempWebHook->setEventTypes($webhookEventTypesTemp);
        return $tempWebHook;
    }


    function show(Request $request, $webhookID)
    {
        $_GET['webhookID'] = $webhookID;
        try {
            $output = \PayPal\Api\Webhook::get($webhookID, parent::getApiContext());
            return $output;
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error get the data', [$ex->getMessage()]);
        }
    }
    function showWebHook($webhookId)
    {
        try {
            if (isset($webhookId)) {
                // Delete the webhook
                return self::getSpecificWebHookByID($webhookId);
            }
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error get or delete the webhooks', [$ex->getMessage()]);
        }
        return parent::sendResponse('The Webhook deleted', 'The webhook has been deleted', true);
    }

    function showEventTypesWebHook($webhookId)
    {
        try {
            $output = \PayPal\Api\WebhookEventType::subscribedEventTypes($webhookId, parent::getApiContext());
            return parent::sendResponse($output);
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error get all Webhook Event Type', [$ex->getMessage()]);
        }
    }

    /**
     * Create a new web hook.
     * 
     * @param $urlPostWebHook = 'https://putsreq.com/om11mbzh2dAxXFI0aFYY';
     * The url allows you to receive any web requests to a url given there.
     * 
     * @param $webhookEventTypes = array('PAYMENT.AUTHORIZATION.CREATED','PAYMENT.AUTHORIZATION.VOIDED');
     * Event Types: Event types correspond to what kind of notifications you want to receive on the given URL.
     * 
     * @return WebHook
     */

    function store(Request $request)
    {
        $urlPostWebHook = $request->url;
        $webhookEventTypes = $request->event_types;
        $webhook = self::storeWebHook($urlPostWebHook, $webhookEventTypes);
        if ($request->has('json') && $request->json) {
            return parent::sendResponse($webhook);
        } else {
            dd($webhook);
        }
    }



    function storeWebHook($urlPostWebHook, $webhookEventTypes)
    {
        if ($urlPostWebHook) {
            // Set URL Main
            self::getWebHook()->setUrl($urlPostWebHook);
            // Make Process
            return self::callEventType($webhookEventTypes);
        } else {
            return parent::sendErrorMain('There was an error get the URL_POST of the Webhook', ['Not found $urlPostWebHook']);
        }
    }

    private function callEventType($webhookEventTypes)
    {
        // Temp Data
        $webhookEventTypesTemp = [];
        // Check Data
        if ($webhookEventTypes) {
            if (is_array($webhookEventTypes)) {
                foreach ($webhookEventTypes as $value) {
                    $webhookEventTypesTemp[] = new \PayPal\Api\WebhookEventType('{"name":"' . $value . '"}');
                }
            } else {
                $webhookEventTypesTemp[] = new \PayPal\Api\WebhookEventType('{"name":"' . $webhookEventTypes . '"}');
            }
            // Set Data
            if (self::getWebHook()) {
                self::getWebHook()->setEventTypes($webhookEventTypesTemp);
                // Request 
                return self::makeWeebHook();
            } else {
                return parent::sendErrorMain('There was an error get the Webhook Controller', ['Not found $webhook global']);
            }
        } else {
            return parent::sendErrorMain('There was an error get the Events for the Webhook', ['Not found $webhookEventTypes as array']);
        }
    }

    private function makeWeebHook()
    {
        try {
            $output = self::getWebHook()->create(parent::getApiContext());
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            return parent::sendErrorMain('There was an error of PayPal Connection', [$ex->getMessage()]);
        } catch (Exception $ex) {
            return parent::sendErrorMain('There was an error to make the Webhook', [$ex->getMessage()]);
        }
        return ($output);
    }

    /** 
     * Validate WebHook
     * 
     **/

    function verifySignature(Request $request)
    {

        /** @var String $bodyReceived */
        $bodyReceived = file_get_contents('php://input');

        /** @var Array $headers */
        $headers = getallheaders();

        /**
         * In Documentions https://developer.paypal.com/docs/api/webhooks/#verify-webhook-signature_post
         * All header keys as UPPERCASE, but I recive the header key as the example array, First letter as UPPERCASE
         */

        $headers = array_change_key_case($headers, CASE_UPPER);

        // Process & Show
        return self::verifyHeaders($headers, $bodyReceived);
    }

    function validateWebHook(Request $request)
    {
        /**
         * This is one way to receive the entire body that you received from PayPal webhook. This is one of the way to retrieve that information.
         * Just uncomment the below line to read the data from actual request.
         */

        /** @var String $requestBody */
        $requestBody = '{"id":"WH-9UG43882HX7271132-6E0871324L7949614","event_version":"1.0","create_time":"2016-09-21T22:00:45Z","resource_type":"sale","event_type":"PAYMENT.SALE.COMPLETED","summary":"Payment completed for $ 21.0 USD","resource":{"id":"80F85758S3080410K","state":"completed","amount":{"total":"21.00","currency":"USD","details":{"subtotal":"17.50","tax":"1.30","shipping":"2.20"}},"payment_mode":"INSTANT_TRANSFER","protection_eligibility":"ELIGIBLE","protection_eligibility_type":"ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE","transaction_fee":{"value":"0.91","currency":"USD"},"invoice_number":"57e3028db8d1b","custom":"","parent_payment":"PAY-7F371669SL612941HK7RQFDQ","create_time":"2016-09-21T21:59:02Z","update_time":"2016-09-21T22:00:06Z","links":[{"href":"https://api.sandbox.paypal.com/v1/payments/sale/80F85758S3080410K","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/payments/sale/80F85758S3080410K/refund","rel":"refund","method":"POST"},{"href":"https://api.sandbox.paypal.com/v1/payments/payment/PAY-7F371669SL612941HK7RQFDQ","rel":"parent_payment","method":"GET"}]},"links":[{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-9UG43882HX7271132-6E0871324L7949614","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-9UG43882HX7271132-6E0871324L7949614/resend","rel":"resend","method":"POST"}]}';
        /**
         * Receive the entire body that you received from PayPal webhook.
         * Just uncomment the below line to read the data from actual request.
         */
        /** @var String $bodyReceived */
        $bodyReceived = file_get_contents('php://input');


        $headers = array(
            'Client-Pid' => '14910',
            'Cal-Poolstack' => 'amqunphttpdeliveryd:UNPHTTPDELIVERY*CalThreadId=0*TopLevelTxnStartTime=1579e71daf8*Host=slcsbamqunphttpdeliveryd3001',
            'Correlation-Id' => '958be65120106',
            'Host' => 'shiparound-dev.de',
            'User-Agent' => 'PayPal/AUHD-208.0-25552773',
            'Paypal-Auth-Algo' => 'SHA256withRSA',
            'Paypal-Cert-Url' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-360caa42-fca2a594-a5cafa77',
            'Paypal-Auth-Version' => 'v2',
            'Paypal-Transmission-Sig' => 'eDOnWUj9FXOnr2naQnrdL7bhgejVSTwRbwbJ0kuk5wAtm2ZYkr7w5BSUDO7e5ZOsqLwN3sPn3RV85Jd9pjHuTlpuXDLYk+l5qiViPbaaC0tLV+8C/zbDjg2WCfvtf2NmFT8CHgPPQAByUqiiTY+RJZPPQC5np7j7WuxcegsJLeWStRAofsDLiSKrzYV3CKZYtNoNnRvYmSFMkYp/5vk4xGcQLeYNV1CC2PyqraZj8HGG6Y+KV4trhreV9VZDn+rPtLDZTbzUohie1LpEy31k2dg+1szpWaGYOz+MRb40U04oD7fD69vghCrDTYs5AsuFM2+WZtsMDmYGI0pxLjn2yw==',
            'Paypal-Transmission-Time' => '2016-09-21T22:00:46Z',
            'Paypal-Transmission-Id' => 'd938e770-8046-11e6-8103-6b62a8a99ac4',
            'Accept' => '*/*',
        );

        /**
         * Receive HTTP headers that you received from PayPal webhook.
         * Just uncomment the below line to read the data from actual request.
         */

        /** @var Array $headers */
        $headers = getallheaders();

        /**
         * In Documentions https://developer.paypal.com/docs/api/webhooks/#verify-webhook-signature_post
         * All header keys as UPPERCASE, but I recive the header key as the example array, First letter as UPPERCASE
         */

        $headers = array_change_key_case($headers, CASE_UPPER);

        return self::verifyHeaders($headers, $requestBody);
    }

    private function verifyHeaders($headers, $body)
    {
        $signatureVerification = new VerifyWebhookSignature();
        // get the webhook ID in config file
        $signatureVerification->setWebhookId(parent::getWebHookID()); // Note that the Webhook ID must be a currently valid Webhook that you created with your client ID/secret.

        if (isset($headers['PAYPAL-AUTH-ALGO']))
            $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);

        if (isset($headers['PAYPAL-TRANSMISSION-ID']))
            $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);

        if (isset($headers['PAYPAL-CERT-URL']))
            $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);

        if (isset($headers['PAYPAL-TRANSMISSION-SIG']))
            $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);

        if (isset($headers['PAYPAL-TRANSMISSION-TIME']))
            $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);

        $signatureVerification->setRequestBody($body);
        $req = clone $signatureVerification;

        // for error message, I log it into a file for debug purpose
        $exception_log_file = storage_path('logs/paypal-exception.log');

        try {
            /** @var \PayPal\Api\VerifyWebhookSignatureResponse $output */
            $output = $signatureVerification->post(self::getApiContext());
            return self::showInformationWebHook($output, $body);
        } catch (Exception $ex) {
            file_put_contents($exception_log_file, $ex->getMessage());
            return parent::sendErrorMain('There was an error get the information', [$ex->getMessage()]);
        }
    }

    private function showInformationWebHook($webhook, $body)
    {
        $status = $webhook->getVerificationStatus(); // 'SUCCESS' or 'FAILURE'
        // if the status is not success, then end here
        if (strtoupper($status) !== 'SUCCESS') exit(1);

        $json = json_decode($body, 1);

        // Because PayPal don't let us to add in custom data in JSON form, so I add it to a field 'custom' as encoded string. Now decode to get the data back
        $custom_data = json_decode($json['resource']['custom'], 1);
        $user = User::find($custom_data['user_id']); // to get the User

        return parent::sendResponse($custom_data);
    }

    /** @get element **/
    function getWebHook()
    {
        return $this->webhook;
    }
}
