<?php

return [

    'private_token' => env('PRIVATE_TOKEN'),

    'var_key_names' => [
        'DESTINATION',
        'SERVER_USER',
        'SERVER_IP',
        'SERVER_PORT',
        'SERVER_PRIVATE_KEY',
        'BRANCH',
        'URL',
    ],

    'set_dev_protected' => env('SET_DEV_PROTECTED', false),
];
