<?php

declare(strict_types=1);

namespace MohamedGaber\SanctumRefreshToken;

use Arr;
use Illuminate\Support\Facades\Request;
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

        if (! app()->configurationIsCached()) { // @phpstan-ignore-line
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

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        Sanctum::authenticateAccessTokensUsing(fn ($token, $isValid) => $isValid && $this->isTokenAbilityValid($token));
    }

    private function isTokenAbilityValid(PersonalAccessToken $token): bool
    {
        // @phpstan-ignore-next-line
        return collect(Arr::wrap(config('sanctum-refresh-token.refresh_route_names')))->contains(Route::currentRouteName()) ?
            $this->isRefreshTokenValid($token) :
            $this->isAuthTokenValid($token);
    }

    private function isAuthTokenValid(PersonalAccessToken $token): bool
    {
        return $token->can('auth') && $token->cant('refresh');
    }

    private function isRefreshTokenValid(PersonalAccessToken $token): bool
    {
        if (! $token->can('refresh') || $token->can('auth')) {
            return false;
        }
        // @phpstan-ignore-next-line
        if (collect($token->abilities)->contains(fn (string $ability) => preg_match('/^access-token-id:[0-9]+$/', $ability))) {
            /** @var string $accessToken */
            $accessToken = Request::header('x-access-token');
            if (! $accessToken || ! $accessToken = PersonalAccessToken::findToken($accessToken)) {
                return false;
            }

            // @phpstan-ignore-next-line
            return $token->can('access-token-id:' . $accessToken->id);
        }

        return true;
    }
}
