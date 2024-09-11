<?php

return [
    'validation' => [
        'number' => [
            'required' => ':attribute އަކީ ކޮންމެހެން ފުރިހަމަ ކުރަންޖެހޭ ބައެކެވެ.',
            'invalid' => ':attribute ރަނގަޅެއް ނޫނެވެ.',
            'invalid_length' => ':attribute ގައި 7 ނަންބަރު ހުންނަންވާނެއެވެ.',
            'exists' => ':attribute ވަނީ ކުރިން ބޭނުންކުރެވިފައެވެ.',
            'doesnt-exist' => ':attribute ވުޖޫދުގައި ނެތެވެ.',
            'locked' => 'ގިނަ ފަހަރު މަތިން މަސައްކަތްކުރުމުގެ ސަބަބުން ނަންބަރު ވަނީ ބަންދުކުރެވިފައެވެ. :time ފަހުން އަލުން މަސައްކަތްކުރެއްވުން އެދެމެވެ.',
            'recently_sent' => 'ކޯޑު ވަނީ ދާދިފަހުން މި ނަންބަރަށް ފޮނުވިފައެވެ. :time ފަހުން އަލުން މަސައްކަތްކުރެއްވުން އެދެމެވެ.',
            'soft_deleted' => ':attribute އާ ގުޅިފައިވާ ޔޫޒާ ވަނީ ވަގުތީގޮތުން ފޮހެލެވިފައެވެ.',
        ],
        'country_code' => [
            'invalid' => 'ގައުމުގެ ކޯޑު ރަނގަޅެއް ނޫނެވެ.'
        ],
        'verification_code' => [
            'invalid' => 'ޓޯކަން ރަނގަޅެއް ނޫނެވެ.',
            'expired' => 'ޓޯކަންގެ މުއްދަތު ހަމަވެއްޖެއެވެ.',
            'locked' => 'ގިނަ ފަހަރު މަތިން މަސައްކަތްކުރުމުގެ ސަބަބުން ނަންބަރު ވަނީ ބަންދުކުރެވިފައެވެ. :time ފަހުން އަލުން މަސައްކަތްކުރެއްވުން އެދެމެވެ.'
        ],
        'messages' => [
            'verification_code_verified' => "ތިޔަ ފަރާތުގެ މޯބައިލް ނަންބަރު ވަނީ ކާމިޔާބުކަމާއެކު ވެރިފައިކުރެވިފައެވެ.",
            'verification_code_verified_title' => 'ވެރިފައިކުރުން ކާމިޔާބު',
            'mobile_number_updated' => 'ތިޔަ ފަރާތުގެ މޯބައިލް ނަންބަރު ވަނީ ކާމިޔާބުކަމާއެކު :mobile_number އަށް އަޕްޑޭޓްކުރެވިފައެވެ.',
            'mobile_number_updated_title' => 'މޯބައިލް ނަންބަރު އަޕްޑޭޓްކުރެވިއްޖެ',
        ]
    ]
];
