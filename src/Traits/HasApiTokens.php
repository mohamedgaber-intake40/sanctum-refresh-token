<?php

declare(strict_types=1);

namespace MohamedGaber\SanctumRefreshToken\Traits;

use DateTimeInterface;
use Laravel\Sanctum\HasApiTokens as SanctumHasApiTokens;

trait HasApiTokens
{
    use SanctumHasApiTokens;

    public function createAuthToken(string $name, ?DateTimeInterface $expiresAt = null, array $abilities = [])
    {
        return $this->createToken($name, array_merge($abilities, ['auth']), $this->getTokenExpiresAt('auth', $expiresAt));
    }

    public function createRefreshToken(string $name, ?DateTimeInterface $expiresAt = null)
    {
        return $this->createToken($name, ['refresh'], $this->getTokenExpiresAt('refresh', $expiresAt));
    }

    private function getTokenExpiresAt($tokenType, ?DateTimeInterface $expiresAt = null)
    {
        $configTokenExpiration = config("sanctum-refresh-token.{$tokenType}_token_expiration");

        return $expiresAt ?? ($configTokenExpiration ? now()->addMinutes($configTokenExpiration) : null);
    }
}
