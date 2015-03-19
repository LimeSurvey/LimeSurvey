<?php
return [[
    // Left side   
        [
            'title' => gT('Dashboard home'),
            'url' => ['/moduleplugin'],
            'icon' => 'home'
        ],
    ], [
    // Right side
        [
            'label' => App()->user->name,
            'url' => '#',
            'icon' => 'user'
        ],
    ]];
