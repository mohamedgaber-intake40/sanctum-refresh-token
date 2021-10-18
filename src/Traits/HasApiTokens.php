<?php

namespace MohamedGaber\SanctumRefreshToken\Traits;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens as SanctumHasApiTokens;
use Laravel\Sanctum\NewAccessToken;

trait HasApiTokens
{
    use SanctumHasApiTokens;

    public function createToken(string $name, $expireMinutes = null, array $abilities = ['*'])
    {
        $token = $this->tokens()
                      ->create([
                          'name'       => $name,
                          'token'      => hash('sha256', $plainTextToken = Str::random(40)),
                          'abilities'  => $abilities,
                          'expired_at' => $expireMinutes ? now()->addMinutes($expireMinutes) : null
                      ]);

        return new NewAccessToken($token, $plainTextToken);
    }

    public function createAuthToken(string $name, $expireMinutes = null, array $abilities = [])
    {
        return $this->createToken($name, $expireMinutes ?? config('sanctum-refresh-token.auth_token_expiration'), array_merge($abilities, ['auth']));
    }

    public function createRefreshToken($name, $expireMinutes = null)
    {
        return $this->createToken($name, $expireMinutes ?? config('sanctum-refresh-token.refresh_token_expiration'), ['refresh']);
    }
}
