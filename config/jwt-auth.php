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
        "ttl" => 1
    ],
    "mail_service_views" => [
        "email_verification",
        "password_reset"
    ]
];
