<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->controller(AuthController::class)->group(
    function () {
        Route::post("/login","login")->name("auth.login");
        Route::post("/logout","logout")->name("auth.logout");
        Route::post("/refresh","refresh")->name("auth.refresh");
        Route::get("/hi", "greet");
        Route::get("/user", "user")->name("auth.user");
    }

   
);


