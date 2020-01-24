<?php
/**
 * @package     IdnPlay\Vock88\Config
 * @author      singkek
 * @copyright   Copyright(c) 2019
 * @version     1
 * @created     2020-01-24
 * @updated     2020-01-24
 **/

return [
    'voucher88' => [
        'host'          => env('VOCK88_HOST', 'YOUR-CLIENT-ID'),
        'client_id'     => env('VOCK88_CLIENT_ID', 'YOUR-CLIENT-ID'),
        'client_secret' => env('VOCK88_CLIENT_SECRET', 'YOUR-CLIENT-SECRET'),
        'storage'       => env('VOCK88_STORAGE', 'file'), // allow for file,redis,db
        'storage_key'   => env('VOCK88_STORAGE_KEY', 'z_vock88_token')
    ],
];
