<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Binance authentication
    |--------------------------------------------------------------------------
    |
    | Authentication key and secret for Binance API.
    |
     */

    'auth' => [
        'key'    => env('BINANCE_KEY', ''),
        'secret' => env('BINANCE_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Api URLS
    |--------------------------------------------------------------------------
    |
    | Binance API endpoints
    |
     */

    'urls' => [
        'api'  => 'https://api.binance.com/api/',
        'wapi'  => 'https://api.binance.com/wapi/'
    ],

];
