<?php

namespace MohamedGaber\SanctumRefreshToken;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use MohamedGaber\SanctumRefreshToken\Models\PersonalAccessToken;

class SanctumRefreshTokenProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/config/sanctum-refresh-token.php', 'sanctum-refresh-token');
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        Sanctum::ignoreMigrations();
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Sanctum::authenticateAccessTokensUsing(function ($token, $isValid) {
            if ($token->expired_at)
                $isValid = $isValid && $token->expired_at->gte(now());

            return $isValid &&
                   Route::currentRouteName() == config('sanctum-refresh-token.refresh_route_name') ?
                $token->can('refresh') && $token->cant('auth') :
                $isValid && $token->can('auth') && $token->cant('refresh');
        });
    }
}
