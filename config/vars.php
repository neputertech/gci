<?php

return [

    'private_token' => env('PRIVATE_TOKEN'),

    'var_key_names' => [
        'DESTINATION',
        'SERVER_USER',
        'SERVER_IP',
        'SERVER_PORT',
        'SERVER_PRIVATE_KEY',
        'SERVER_DEPLOYMENT_SCRIPT',
        'BRANCH',
        'URL',
    ],

    'set_dev_protected' => env('SET_DEV_PROTECTED', false),
    'set_qa_protected' => env('SET_QA_PROTECTED', true),
];
