<?php

return [
    'validation' => [
        'number' => [
            'required' => 'The :attribute field is required.',
            'invalid' => 'The :attribute must start with 7 or 9',
            'invalid_length' => 'The :attribute must be 7 digits long.',
            'exists' => 'The :attribute has already been taken.',
            'doesnt-exist' => 'The :attribute does not exist.',
            'locked' => 'The number is locked due to too many attempts.',
            'recently_sent' => 'The code was recently sent to this number.'
        ],
        'country_code' => [
            'invalid' => 'The country code is invalid.'
        ],
        'token' => [
            'invalid' => 'The token is invalid.',
            'expired' => 'The token has expired.',
            'locked' => 'The number is locked due to too many attempts.'
        ]
    ]
];
