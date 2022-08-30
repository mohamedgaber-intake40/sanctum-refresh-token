<?php

namespace MohamedGaber\SanctumRefreshToken\Traits;

use DateTimeInterface;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens as SanctumHasApiTokens;
use Laravel\Sanctum\NewAccessToken;

trait HasApiTokens
{
    use SanctumHasApiTokens;

    public function createAuthToken(string $name,  DateTimeInterface $expiresAt = null, array $abilities = [])
    {
        return $this->createToken($name, array_merge($abilities, ['auth']),$expiresAt ?? now()->addMinutes(config('sanctum-refresh-token.auth_token_expiration')));
    }

    public function createRefreshToken(string $name, DateTimeInterface $expiresAt = null)
    {
        return $this->createToken($name, ['refresh'],$expiresAt ?? now()->addMinutes(config('sanctum-refresh-token.refresh_token_expiration')));
    }
}
