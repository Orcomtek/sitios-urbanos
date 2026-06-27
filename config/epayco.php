<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ePayco Credentials
    |--------------------------------------------------------------------------
    | These values are loaded from the .env file. Only `public_key` and
    | `testing` should ever be forwarded to the frontend via Inertia props.
    | The private_key, p_cust_id_cliente, and p_key are strictly server-side.
    */

    'public_key' => env('EPAYCO_PUBLIC_KEY'),
    'private_key' => env('EPAYCO_PRIVATE_KEY'),
    'p_cust_id_cliente' => env('EPAYCO_P_CUST_ID_CLIENTE'),
    'p_key' => env('EPAYCO_P_KEY'),
    'testing' => (bool) env('EPAYCO_TESTING', true),
];
