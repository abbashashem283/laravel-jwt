<?php
//ttl is in minutes
return [
    "access" => [
        "secret" => env("JWT_SECRET"),
        "algorithm" => "HS256",
        "ttl" => 60
    ],
    "refresh" => [
        "secret" => env("REFRESH_SECRET"),
        "algorithm" => "HS256",
        "ttl" => 10080
    ],
    "csrf" => [
        "secret" => env("CSRF_SECRET"),
        "algorithm" => "HS256",
        "ttl" => 1440
    ],
    "email_verification" => [
        "ttl" => 10
    ],
    "reset_password" => [
        "ttl" => 10
    ],
    "mail_service_views" => [
        "emailVerification"=>"auth.email_verification",
        "passwordReset"=>"auth.password_reset"
    ]
];
