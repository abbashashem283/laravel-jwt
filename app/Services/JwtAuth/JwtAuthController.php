<?php
namespace App\Services\JwtAuth ;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JwtAuthController extends Controller{

    public function login(Request $request) {
        $credentials = $request->only('email','password');
        $token = auth()->attempt($credentials);
        if(!$token)
            return response()->json(['error' => 'Invalid credentials'], 401);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            // optionally include refresh token
        ]);
    }

    public function greet() {
        return response()->json(["message"=>"Hello and Welcome!"]);
    }
}