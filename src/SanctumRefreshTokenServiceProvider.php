<?php

namespace MohamedGaber\SanctumRefreshToken;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use MohamedGaber\SanctumRefreshToken\Models\PersonalAccessToken;

class SanctumRefreshTokenServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/sanctum-refresh-token.php', 'sanctum-refresh-token');
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'sanctum-refresh-token-migrations');

        $this->publishes([
            __DIR__.'/../config/sanctum-refresh-token.php' => config_path('sanctum-refresh-token.php'),
        ], 'sanctum-refresh-token-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Sanctum::ignoreMigrations();
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Sanctum::authenticateAccessTokensUsing(function ($token, $isValid) {
            if ($token->expired_at)
                $isValid = $isValid && $token->expired_at->gte(now());

            return $isValid && $this->isTokenAbilityValid($token);
        });
    }
    
    private function isTokenAbilityValid($token)
    {
        return Route::currentRouteName() == config('sanctum-refresh-token.refresh_route_name') ?
            $this->isRefreshTokenValid($token) :
            $this->isAuthTokenValid($token);
    }


    private function isAuthTokenValid($token)
    {
        return $token->can('auth') && $token->cant('refresh');
    }

    private function isRefreshTokenValid($token)
    {
        return $token->can('refresh') && $token->cant('auth');
    }
}
