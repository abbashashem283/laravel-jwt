<?php
namespace App\Services\JwtAuth;

use Closure;
use Exception;
use Illuminate\Http\Request;


class JwtMiddleware {
    public function handle(Request $request, Closure $next ) {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try{
            $payload = JwtService::validateToken($token);
        }catch(Exception $e){
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        return $next($request);
    }
}