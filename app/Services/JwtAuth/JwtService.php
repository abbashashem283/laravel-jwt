<?
namespace App\Services;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService  {

    public static function generateToken($user,$type="access", $customPayload=null)
    {
        $config = config("jwt-auth.$type");
        $payload = $customPayload ?? [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addMinutes($config['ttl'])->timestamp,
        ];

        return JWT::encode($payload, $config['secret'], $config['algorithm']); 
    }

      public static function validateToken($token,$type="access")
    {
        $config = config("jwt-auth.$type");
        return JWT::decode($token, new Key($config['secret'], $config['algorithm']));
    }
}