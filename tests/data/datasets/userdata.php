<?php
$aDataSet = [
    "new_user_data" => [
        "users_name"        => "newuser",
        "password"          => '$2y$10$KnJvXyo7AxRbtMCWsx9czuJMbXG1SIlgLAA4MIzJ/IPoruIo0jLR2',
        "full_name"         => "new user",
        "email"             => "email@example.com",
        "clearTextPassword" => "newUserPassword",
    ],
    "user_change_password" => [
        "users_name"          => "newuser",
        "password"            => 'changedNewUserPassword',
        "full_name"           => "new user",
        "email"               => "email@example.com",
    ],
    "user_change_full_name" => [
        "users_name"        => "newuser",
        "password"          => 'newUserPassword',
        "full_name"         => "new user changed",
        "email"             => "email@example.com",
    ],
    "change_admin_user" => [
        "users_name"        => "admin",
        "password"          => 'password',
        "full_name"         => "GOTCHA!",
        "email"             => "email@example.com",
    ]
];
