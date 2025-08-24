<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->controller(AuthController::class)->group(
    function () {
        Route::post("/login","login")->name("auth.login");
        Route::post("/logout","logout")->name("auth.logout");
        Route::post("/refresh","refresh")->name("auth.refresh");
        Route::post("/register","register")->name("auth.register");
        Route::post("/password/forgot-password", "forgotPassword")->name("auth.password.forgot");
        Route::post("/password/check-code", "checkPasswordCode")->name("auth.password.code");
        Route::post("/password/reset", "resetPassword")->name("auth.password.reset");
        Route::get("/verify","verify")->name("auth.verify");
        Route::get("/hi", "greet");
        Route::get("/user", "user")->name("auth.user");
    }

   
);


