<?php

namespace App\Services\JwtAuth;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{

    public static function generateToken($user, $type = "access", $customPayload = null)
    {
        $config = config("jwt-auth.$type");

        $token_time = [
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addMinutes($config['ttl'])->timestamp,
        ];

        $payload = $customPayload ?? [
            'sub' => $user->id,
            'email' => $user->email
        ];

        $payload = array_merge($payload, $token_time) ;

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
}
