<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Commission Rates
    |--------------------------------------------------------------------------
    |
    | Here you can specify the default commission rules that apply when a payment
    | is processed internally via the platform's payment gateway (e.g., ePayco).
    |
    */
    
    'commission' => [
        'type' => env('FINANCE_COMMISSION_TYPE', 'fixed'), // 'fixed' or 'percentage'
        'value' => (int) env('FINANCE_COMMISSION_VALUE', 1500), // Default 1500 COP if fixed
    ],

    /*
    |--------------------------------------------------------------------------
    | ePayco Configuration
    |--------------------------------------------------------------------------
    */
    'epayco' => [
        'p_key' => env('EPAYCO_P_KEY', ''),
        'p_cust_id_cliente' => env('EPAYCO_CLIENT_ID', ''),
        'public_key' => env('EPAYCO_PUBLIC_KEY', ''),
        'private_key' => env('EPAYCO_PRIVATE_KEY', ''),
    ],
];
