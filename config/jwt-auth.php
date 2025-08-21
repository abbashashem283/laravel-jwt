<?php

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
    ]
];
