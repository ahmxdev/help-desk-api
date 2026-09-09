<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('permission', function ($user, $permission) {
            return $user->hasPermission($permission);
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            $ip = (string) $request->ip();

            $key = $email . '|' . $ip;

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            $email = (string) $request->input('email');
            $ip = (string) $request->ip();

            $key = $email . '|' . $ip;

            return Limit::perMinute(3)->by($key);
        });

        RateLimiter::for('verification-notification', function (Request $request) {
            $key = $request->user()->id;

            return Limit::perMinute(3)->by($key);
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
