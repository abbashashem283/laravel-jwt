<?php

namespace App\Services\JwtAuth;

use App\Http\Controllers\Controller;
use App\Services\JwtAuth\users\enums\UserAuthStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JwtAuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $tokens = auth()->attempt($credentials);
        if (!$tokens)
            return response()->json(['error' => 'Could not log in'], 401);
        return response()->json($tokens);
    }

    public function logout(Request $request){
        auth()->user();
        $invalidate = auth()->invalidate(UserAuthStatus::REVOKED->value);
        if(!$invalidate)
            return response("attempt failed", 409);
        return response("ok", 200);
    }

    public function refresh()
    {
        $tokens = auth()->refreshTokens();
        if(!$tokens)
            return response('Unauthorized', 401);
        return response()->json($tokens);
    }

    public function user(){
        $user = auth()->user();
        return response()->json(compact('user'));
    }

    public function greet(Request $request)
    {   
        $at = $request->query("at");
        return auth()->validate(["tokens" => ["access" => $at]]);
    }
}
