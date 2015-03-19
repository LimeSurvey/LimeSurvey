<?php

return [
    'layout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
    'elements' => [
        'username' => [
            'type' => 'text'
        ],
        'password' => [
            'type' => 'password'
        ]
    ],
    'buttons' => [
        'submit' => [
            'type' => 'submit',
            'label' => 'Log in',
            'color' => 'primary'
        ]
    ]
    
];