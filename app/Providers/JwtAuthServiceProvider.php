<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Services\JwtAuth\JwtGuard;
use App\Services\JwtAuth\JwtService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;



class JwtAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            $request = $app['request'];
            $jwtService = $app->make(JwtService::class);
            return new JwtGuard($provider, $request, $jwtService);
        });
    }
}
