<?php

namespace App\Services\JwtAuth;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{

    public static function generateToken($payload, $type = "access")
    {
        $config = config("jwt-auth.$type");

        $token_time = [
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addMinutes($config['ttl'])->timestamp,
        ];

        $payload = array_merge($token_time, $payload);

        return JWT::encode($payload, $config['secret'], $config['algorithm']);
    }

    public static function validateToken($token, $type = "access")
    {
        $config = config("jwt-auth.$type");

        if (!$token) return null;

        try {
            return JWT::decode($token, new Key($config['secret'], $config['algorithm']));
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function decodeJwt(string $jwt)
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return null;
        }

        $payload = $parts[1];
        $decoded = base64_decode(strtr($payload, '-_', '+/'));

        return json_decode($decoded, true);
    }
}
