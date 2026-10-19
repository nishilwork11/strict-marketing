<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Password
    |--------------------------------------------------------------------------
    |
    | The admin panel at /admin has one shared password. Without it the panel
    | stays locked. Changing it signs everyone out.
    |
    */

    'password' => env('ADMIN_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Admin Session
    |--------------------------------------------------------------------------
    |
    | Signing in sets this cookie, scoped to /admin, for the given number of
    | seconds (a week), so you stay signed in on that browser.
    |
    */

    'cookie' => 'admin_session',

    'max_age' => 60 * 60 * 24 * 7,

];
