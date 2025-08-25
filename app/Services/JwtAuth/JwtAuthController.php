<?php

namespace App\Services\JwtAuth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\ResetPasswordToken;
use App\Models\User;
use App\Services\JwtAuth\mail\AuthMail;
use App\Services\JwtAuth\users\enums\UserAuthStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class JwtAuthController extends Controller
{

    protected $model;

    public function __construct()
    {
        $this->model = config("auth.providers.users.model");
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $tokens = auth()->attempt($credentials);
        if (!$tokens)
            return response()->json(['error' => 'Could not log in'], 401);
        return response()->json($tokens);
    }

    public function logout(Request $request)
    {
        $invalidate = auth()->invalidate(UserAuthStatus::REVOKED->value);
        if (!$invalidate)
            return response("attempt failed", 409);
        return response("ok", 200);
    }

    public function refresh()
    {
        $tokens = auth()->refreshTokens();
        if (!$tokens)
            return response('Unauthorized', 401);
        return response()->json($tokens);
    }

    public function user()
    {
        $user = auth()->user();
        return response()->json(compact('user'));
    }

    public function greet(Request $request)
    {
        $at = $request->query("at");
        return auth()->validate(["tokens" => ["access" => $at]]);
    }

    public function register(Request $request)
    {

        $validatedData = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8|max:255"
        ]);


        // Hash the password before creating the user
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = $this->model::create($validatedData);

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

        EmailVerificationToken::where("user_email", $user->email)
            ->delete();

        // Create the token record and link it to the user
        EmailVerificationToken::create([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'token' => $hashedToken,
            'exp' => $expirationTime,
        ]);

        // Send the email with the plain-text token
        // Example: Mail::to($user->email)->send(new VerifyEmail($plainTextToken));
        $emailVerificationView = config("jwt-auth.mail_service_views.emailVerification");
        Mail::to($user->email)
            ->send(new AuthMail($emailVerificationView, [
                "subject" => "Email Confirmation",
                "props" => [
                    "link" => route("auth.verify", [
                        "email" => $user->email,
                        "token" => $plainTextToken
                    ])
                ]
            ]));



        return response()->json(["message" => "User registered. Email verification sent to $request->email"]);
        //return response("hi");
    }

    public function verify(Request $request)
    {
        $token = $request->query("token");
        $email = $request->query("email");
        if (!$token)
            return response("Unauthorized", 403);
        $storedToken = EmailVerificationToken::where("user_email", $email)->first();
        if (!$storedToken || !Hash::check($token, $storedToken->token))
            return response("Unauthorized", 403);
        $tokenIsValid = Carbon::now()->timestamp < $storedToken->exp;
        if (!$tokenIsValid)
            return response("Expired Link", 403);
        $storedUser = $this->model::where("email", $email)->first();
        if (!$storedUser)
            return response("Invalid User", 403);
        $storedUser->update([
            "email_verified_at" => Carbon::now()
        ]);
        $storedToken->delete();
        return response()->json(["message" => "email verified successfully"]);
    }

    public function checkPasswordCode(Request $request)
    {
        $validatedData = $request->validate([
            "email" => "required|string|email",
            "code" => "required|string|size:6"
        ]);
        $storedUser = $this->model::where("email", $validatedData["email"])->first();
        if (!$storedUser)
            return response("User not found", 404);
        return $this->checkCode($storedUser, $validatedData["code"]);
    }


    private function checkCode($user, $code)
    {
        $resetPasswordToken = $user->resetPasswordToken;
        if (!$resetPasswordToken)
            return response("Unauthorized", 403);

        $tokenExpired = Carbon::now()->timestamp > $resetPasswordToken->exp;
        if ($tokenExpired)
            return response("Expired Link", 403);
        $tokenIsValid = Hash::check($code, $resetPasswordToken->token);
        if (!$tokenIsValid)
            return response("Unauthorized", 403);
        return response()->json(["message" => "code is valid"]);
    }

    public function resetPassword(Request $request)
    {
        $validatedData = $request->validate([
            "email" => "required|string|email",
            "code" => "required|size:6",
            "password" => "required|string|min:8"
        ]);
        $storedUser = $this->model::where("email", $validatedData["email"])->first();
        if (!$storedUser)
            return response("User not found", 404);

        $checkCode = $this->checkCode($storedUser, $validatedData["code"]);
        if ($checkCode->getStatusCode() != 200)
            return $checkCode;
        $newPassword = Hash::make($validatedData["password"]);
        $storedUser->update([
            "password" => $newPassword
        ]);
        $storedUser->resetPasswordToken?->delete();
        return response()->json(["message" => "password updated!"]);
    }

    public function forgotPassword(Request $request)
    {
        $validatedData = $request->validate([
            "email" => "required|string|email"
        ]);
        $email = $validatedData["email"];
        $storedUser = $this->model::where("email", $email)->first();
        if (!$storedUser)
            return response("user not found", 404);

        $token = Str::random(6);
        $hashedToken = Hash::make($token);

        $tokenTTL = config('jwt-auth.reset_password.ttl') * 60;
        $exp = Carbon::now()->timestamp + $tokenTTL;

        $storedUser->resetPasswordToken?->delete();

        ResetPasswordToken::create([
            'user_id' => $storedUser->id,
            'user_email' => $storedUser->email,
            'token' => $hashedToken,
            'exp' => $exp
        ]);

        $passwordResetView = config("jwt-auth.mail_service_views.passwordReset");
        Mail::to($email)
            ->send(
                new AuthMail($passwordResetView, [
                    "subject" => "Password Reset",
                    "props" => [
                        "code" => $token
                    ]
                ])
            );

        return response()->json(["message" => "code sent to $email"]);
    }
}
