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
        "ttl" => 604800
    ],
    "csrf" => [
        "secret" => env("CSRF_SECRET"),
        "algorithm" => "HS256",
        "ttl" => 86400
    ]
];
