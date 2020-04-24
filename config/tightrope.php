<?php

use Carbon\Carbon;
use ShabuShabu\Tightrope\Requests\{EmailPasswordRequest, ResetPasswordRequest};

return [
    'client_name'            => 'Tightrope Password Client',
    'refresh_cookie_name'    => 'x-refresh-token',
    'auth_guard'             => 'web',
    'refresh_token_validity' => Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY * 10,
    'requests'               => [
        'send_password'  => EmailPasswordRequest::class,
        'reset_password' => ResetPasswordRequest::class,
    ],
];
