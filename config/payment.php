<?php

return [
    'environment' => env('PAYMENT_ENVIRONMENT','sandbox'),

    'channel'     => env('PAYMENT_CHANNEL','payment'),

    'sandbox' => [
        'api_key_id'          => env('PAYMENT_SANDBOX_API_KEY_ID',''),

        'api_secret'          => env('PAYMENT_SANDBOX_API_SECRET',''),

        'api_end_point'       => env('PAYMENT_SANDBOX_API_END_POINT',''),

        'integrator'          => env('PAYMENT_SANDBOX_INTEGRATOR',''),

        'language_code'       => env('PAYMENT_SANDBOX_LANGUAGE_CODE',''),

        'return_url'          => env('PAYMENT_SANDBOX_RETURN_URL',''),

        'url_domain'          => env('PAYMENT_SANDBOX_URL_DOMAIN',''),

        'merchant_id_one'     => env('PAYMENT_SANDBOX_MERCHANT_ID_ONE',''),

        'merchant_id_two'     => env('PAYMENT_SANDBOX_MERCHANT_ID_TWO',''),

        'merchant_id_three'   => env('PAYMENT_SANDBOX_MERCHANT_ID_THREE',''),

        'show_result_page'    => env('PAYMENT_SANDBOX_SHOW_RESULT_PAGE',''),

        'return_cancel_state' => env('PAYMENT_SANDBOX_RETURN_CANCEL_STATE',''),

        'variant'             => env('PAYMENT_SANDBOX_VARIANT','')
    ],

    'production' => [
        'api_key_id'          => env('PAYMENT_PRODUCTION_API_KEY_ID',''),

        'api_secret'          => env('PAYMENT_PRODUCTION_API_SECRET',''),

        'api_end_point'       => env('PAYMENT_PRODUCTION_API_END_POINT',''),

        'integrator'          => env('PAYMENT_PRODUCTION_INTEGRATOR',''),

        'language_code'       => env('PAYMENT_PRODUCTION_LANGUAGE_CODE',''),

        'return_url'          => env('PAYMENT_PRODUCTION_RETURN_URL',''),

        'url_domain'          => env('PAYMENT_PRODUCTION_URL_DOMAIN',''),

        'merchant_id_one'     => env('PAYMENT_PRODUCTION_MERCHANT_ID_ONE',''),

        'merchant_id_two'     => env('PAYMENT_PRODUCTION_MERCHANT_ID_TWO',''),

        'merchant_id_three'   => env('PAYMENT_SANDBOX_MERCHANT_ID_THREE',''),

        'show_result_page'    => env('PAYMENT_PRODUCTION_SHOW_RESULT_PAGE',''),

        'return_cancel_state' => env('PAYMENT_PRODUCTION_RETURN_CANCEL_STATE',''),

        'variant'             => env('PAYMENT_PRODUCTION_VARIANT','')
    ]
];
