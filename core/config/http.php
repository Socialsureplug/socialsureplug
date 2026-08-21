<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Client CA Bundle
    |--------------------------------------------------------------------------
    |
    | Path to the CA certificate bundle used to verify SSL connections. When
    | set and the file exists, outbound HTTPS requests (e.g. to number-provider
    | APIs) use this bundle instead of the system default (which may be wrong
    | or missing on some Windows/PHP setups). Run `php artisan http:install-ca-cert`
    | to download the bundle into storage/app/cacert.pem.
    |
    */

    'ca_bundle' => env('CURL_CA_BUNDLE', storage_path('app/cacert.pem')),

];
