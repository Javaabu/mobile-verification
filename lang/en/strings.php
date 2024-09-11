<?php

return [
    'validation' => [
        'number' => [
            'required' => 'The :attribute field is required.',
            'invalid' => 'The :attribute must start with 7 or 9',
            'invalid_length' => 'The :attribute must be 7 digits long.',
            'exists' => 'The :attribute has already been taken.',
            'doesnt-exist' => 'The :attribute does not exist.',
            'locked' => 'The number is locked due to too many attempts. You can try again in :time.',
            'recently_sent' => 'The code was recently sent to this number. You can try again in :time.',
            'soft_deleted' => 'The user associated with the :attribute is soft deleted.',
        ],
        'country_code' => [
            'invalid' => 'The country code is invalid.'
        ],
        'verification_code' => [
            'invalid' => 'The token is invalid.',
            'expired' => 'The token has expired.',
            'locked' => 'The number is locked due to too many attempts. You can try again in :time.'
        ],
        'messages' => [
            'verification_code_verified' => "Your mobile number has been successfully verified.",
            'verification_code_verified_title' => 'Verification Successful',
            'mobile_number_updated' => 'Your mobile number has been successfully updated to :mobile_number.',
            'mobile_number_updated_title' => 'Mobile Number Updated',
        ]
    ]
];
