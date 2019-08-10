<?php
return [
    'base_url' => env('PAYPAL_BASE_URL', ''),
    'sandbox_client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
    'sandbox_secret' => env('PAYPAL_SANDBOX_SECRET', ''),
    'sandbox_plan_id' => env('PAYPAL_SANDBOX_PLAN_ID', ''),
    'live_client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
    'live_secret' => env('PAYPAL_LIVE_SECRET', ''),
    'live_plan_id' => env('PAYPAL_LIVE_PLAN_ID', ''),


    /**
     * SDK configuration 
     */
    'settings' => [

        /**
         * Available option 'sandbox' or 'live'
         */
        'mode' => env('PAYPAL_MODE', 'sandbox'),

        /**
         * Specify the max request time in seconds
         */
        'http.ConnectionTimeOut' => 30,

        /**
         * Whether want to log to a file
         */
        'log.LogEnabled' => true,

        /**
         * Specify the file that want to write on
         */
        'log.FileName' => storage_path() . '/logs/paypal.log',

        /**
         * Available option 'FINE', 'INFO', 'WARN' or 'ERROR'
         *
         * Logging is most verbose in the 'FINE' level and decreases as you
         * proceed towards ERROR
         */
        'log.LogLevel' => 'FINE'
    ],
    'webhooks' => [
        'payment_sale_completed' => env('PAYPAL_PAYMENT_SALE_COMPLETED_WEBHOOK_ID'),
    ],
];
