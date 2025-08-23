<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationTokens;
use App\Models\User;
use App\Services\JwtAuth\JwtAuthController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\JwtAuth\mail\AuthMail;
use Illuminate\Support\Str;

class AuthController extends JwtAuthController
{
    public function register(Request $request)
    {

        //dd($request);
        $validatedData = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8|max:255"
        ]);


        // Hash the password before creating the user
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        if (!$user) {
            return response("Could not create user", 500);
        }

        // Generate and hash the email verification token
        $plainTextToken = Str::random(60);
        $hashedToken = Hash::make($plainTextToken);

        // Get timestamps
        $currentTime = Carbon::now()->timestamp;
        $tokenTTL = config('jwt-auth.email_verification.ttl') * 60;
        $expirationTime = $currentTime + $tokenTTL;

        // Create the token record and link it to the user
        $emailVerificationToken = EmailVerificationTokens::create([
            'user_id' => $user->id,
            'user_email'=>$user->email,
            'token' => $hashedToken,
            'iat' => $currentTime,
            'exp' => $expirationTime,
        ]);

        // Send the email with the plain-text token
        // Example: Mail::to($user->email)->send(new VerifyEmail($plainTextToken));
        Mail::to($user->email)
            ->send(new AuthMail("auth.email_verification", [
                "subject" => "Email Confirmation", 
                "props" => [
                    "link" => route("auth.verify", [
                        "email"=>$user->email,
                        "token"=>$plainTextToken
                    ])
                ]
            ]));

           
            EmailVerificationTokens::where("user_email", $user->email)
                ->delete();

        return response()->json(["message" => "User registered. Email verification sent to $request->email"]);
        //return response("hi");
    }

    public function verify(Request $request){
        $token = $request->query("token");
        $email = $request->query("email");
        if(!$token)
            return response("Unauthorized",403);
        $storedToken = EmailVerificationTokens::where("user_email", $email)->first();
        if(!$storedToken || !Hash::check($token, $storedToken->token))
            return response("Unauthorized",403);
        $tokenIsValid = Carbon::now()->timestamp < $storedToken->exp;
        if(!$tokenIsValid)
            return response("Expired Link",403);
        $storedUser = User::where("email", $email)->first();
        if(!$storedUser)
            return response("Invalid User",403);
        $storedUser->update([
            "email_verified_at"=>Carbon::now()
        ]);
        $storedToken->delete();
        return response()->json(["message"=>"email verified successfully"]);
    }
}
