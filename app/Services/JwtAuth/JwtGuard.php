<?php

namespace App\Services\JwtAuth;

use App\Services\JwtAuth\users\enums\UserAuthStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\isEmpty;

class JwtGuard implements Guard
{

    protected  $provider;
    protected  $request;
    protected  $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user()
    {
        if ($this->user) return $this->user;
        $token = $this->request->bearerToken();
        if(!$token) return null;
        $payload = $this->validateTokens(["access"=>$token])["access"];
        if (!$payload) return null;
        $this->user = $this->provider->retrieveById($payload->sub);
        if(!$this->user || $this->user->authRevoke) 
            return null;
        return $this->user;
    }

    public function check()
    {
        return !is_null($this->user);
    }

    public function guest()
    {
        return !$this->check();
    }

    public function id()
    {
        return $this->user() ? $this->user()->getAuthIdentifier() : null;
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function hasUser()
    {
        return !!$this->user;
    }

    public function validate(array $credentials = [])
    {
        if (isset($credentials["tokens"]))
            return $this->validateTokens($credentials["tokens"]);


        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);
    }

    public function validateTokens(array $tokens): array
    {
        $validated = [];

        foreach (['access', 'refresh', 'csrf'] as $type) {
            if (!empty($tokens[$type])) {
                $validated[$type] = JwtService::validateToken($tokens[$type], $type);
            }
        }

        return $validated;
    }


    // ------------------------
    // Validate AND log in / issue token
    // ------------------------
    public function attempt(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $user = $this->provider->retrieveByCredentials($credentials);
            $authRevocation = $user->authRevoke ;
            if($authRevocation){
                switch($authRevocation->status){
                    case UserAuthStatus::DENIED->value :
                        return false;
                    case UserAuthStatus::REVOKED->value :
                        $user->authRevoke->delete();

                }
            }
            $this->setUser($user);
            return $this->generateTokens();
        }

        return false;
    }

    public function getProvider() {
        return $this->provider;
    }

    public function generateTokens()
    {
        $refresh_token = JwtService::generateToken(["email" => $this->user->email], "refresh");

        $csrf_token = JwtService::generateToken(["email" => $this->user->email], "csrf");

        $access_token = JwtService::generateToken([
            "sub" => $this->user->id,
            "email" => $this->user->email,
            "art" => Hash::make($refresh_token)
        ]);

        return compact('access_token', 'refresh_token', 'csrf_token');
    }

        public function refreshTokens()
    {
        $accessToken = $this->request->bearerToken();
        $refreshToken = $this->request->refresh_token;

        if(!$accessToken || !$refreshToken)
            return null;

        $validatedTokens = $this->validate(["tokens" => ["refresh" => $refreshToken]]);

        $refreshPayload = $validatedTokens["refresh"];


        if(!$refreshPayload)
            return null;

        $expiredAccessPayload = JwtService::decodeJwt($accessToken);


        $hashedRefreshToken = $expiredAccessPayload["art"] ?? null ;


        if(!$hashedRefreshToken || !Hash::check($refreshToken, $hashedRefreshToken))
            return null;

        $user = $this->provider->retrieveByCredentials(["email"=>$refreshPayload->email]) ;

        $this->setUser($user);

        $tokens = $this->generateTokens();

        return $tokens;

    }
}
