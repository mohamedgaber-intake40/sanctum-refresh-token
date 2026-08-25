<?php

declare(strict_types=1);

namespace MohamedGaber\SanctumRefreshToken;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class SanctumRefreshTokenServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        if (! app()->configurationIsCached()) {
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
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'sanctum-refresh-token-migrations');

        $this->publishes([
            __DIR__ . '/../config/sanctum-refresh-token.php' => config_path('sanctum-refresh-token.php'),
        ], 'sanctum-refresh-token-config');

        if ($this->app->runningUnitTests()) {
            $this->loadMigrationsFrom(__DIR__ . '/../vendor/laravel/sanctum/database/migrations');
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
        Sanctum::authenticateAccessTokensUsing(fn ($token, $isValid) => $isValid && $this->isTokenAbilityValid($token));
    }

    private function isTokenAbilityValid(PersonalAccessToken $token): bool
    {
        /** @var array<string>|string $routeNames */
        $routeNames = config('sanctum-refresh-token.refresh_route_names');
        if (is_string($routeNames)) {
            $routeNames = [$routeNames];
        }

        // @phpstan-ignore-next-line
        return collect($routeNames)->contains(Route::currentRouteName()) ?
            $this->isRefreshTokenValid($token) :
            $this->isAuthTokenValid($token);
    }

    private function isAuthTokenValid(PersonalAccessToken $token): bool
    {
        return ! $this->marksARefreshToken($token);
    }

    private function isRefreshTokenValid(PersonalAccessToken $token): bool
    {
        return $this->marksARefreshToken($token);
    }

    /**
     * Read the abilities array directly rather than going through can().
     *
     * can() treats '*' as granting every ability, which is correct for
     * authorisation and wrong here: a standard Sanctum token holds ['*'], so
     * can('auth') and can('refresh') were both true and the token was rejected
     * as neither.
     */
    private function marksARefreshToken(PersonalAccessToken $token): bool
    {
        return in_array('refresh', $token->abilities ?? [], true);
    }
}
