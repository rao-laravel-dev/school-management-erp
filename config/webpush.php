<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Push (VAPID)
    |--------------------------------------------------------------------------
    | Keys are generated once (see docs) and read from .env. env() is used
    | only here; the rest of the app reads config('webpush.*').
    */

    'public_key'  => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject'     => env('VAPID_SUBJECT', 'mailto:admin@example.com'),

];
