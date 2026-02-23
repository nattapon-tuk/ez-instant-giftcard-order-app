<?php

/** Other configs / parameters of EZ API */
return [

    'base_url' => env('EZ_BASE_URL', 'https://sandboxapi.ezcards.io'),
    'api_key' => env('EZ_API_KEY'),
    'access_token' => env('EZ_ACCESS_TOKEN'),
    'ez_sku' => env('EZ_SKU', '8PX-UF-Y5U'),

    'order_fulfillment_timeout' => env('ORDER_FULFILLMENT_TIMEOUT_SECONDS', 60),
    'poll_interval_seconds' => env('POLL_INTERVAL_SECONDS', 2),
    'prefixTransactionRef' => env('PREFIX_TRANSACTION_REF', 'TXNTEXT-1'),
];
