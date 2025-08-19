<?php

namespace App\Services\JwtAuth ;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class JwtGuard implements Guard{

    protected  $provider ;
    protected  $request ;
    protected  $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user() {
        if($this->user) return $this->user ;
        $token = $this->request->bearerToken();
        $payload = JwtService::validateToken($token);
        if(!$payload) return null;
        $this->user = $this->provider->retrieveById($payload->sub);
        return $this->user ;
    }

    public function check() {
        return !is_null($this->user);
    }

    public function guest(){
        return !$this->check();
    }

    public function id(){
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
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);
    }

    // ------------------------
    // Validate AND log in / issue token
    // ------------------------
    public function attempt(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $this->setUser($this->provider->retrieveByCredentials($credentials));

            // Example: generate JWT token
            //$token = $this->generateJwt($this->user);

            $token = JwtService::generateToken($this->user);

            return $token;
        }

        return false;
    }
}