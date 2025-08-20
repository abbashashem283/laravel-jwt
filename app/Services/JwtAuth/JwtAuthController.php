<?php

namespace App\Services\JwtAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JwtAuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $tokens = auth()->attempt($credentials);
        if (!$tokens)
            return response()->json(['error' => 'Invalid credentials'], 401);
        return response()->json($tokens);
    }

    public function refresh()
    {
        $tokens = auth()->refreshTokens();
        if(!$tokens)
            return response('Unauthorized', 401);
        return response()->json($tokens);
    }

    public function greet(Request $request)
    {   
        $at = $request->query("at");
        return auth()->validate(["tokens" => ["access" => $at]]);
    }
}
