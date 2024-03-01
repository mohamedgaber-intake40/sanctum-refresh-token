<?php

declare(strict_types=1);

namespace MohamedGaber\SanctumRefreshToken\Traits;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens as SanctumHasApiTokens;
use Laravel\Sanctum\NewAccessToken;

trait HasApiTokens
{
    use SanctumHasApiTokens;

    public function createAuthToken(string $name, ?DateTimeInterface $expiresAt = null, array $abilities = [])
    {
        return $this->createToken($name, array_merge($abilities, ['auth']), $this->getTokenExpiresAt('auth', $expiresAt));
    }

    public function createRefreshToken(string $name, ?DateTimeInterface $expiresAt = null, ?NewAccessToken $accessToken = null)
    {
        return $this->createToken(
            $name,
            collect(['refresh'])->when((bool) $accessToken, fn (Collection $collection) => $collection->push('access-token-id:' . $accessToken->accessToken->id))->toArray(),
            $this->getTokenExpiresAt('refresh', $expiresAt)
        );
    }

    private function getTokenExpiresAt($tokenType, ?DateTimeInterface $expiresAt = null)
    {
        $configTokenExpiration = config("sanctum-refresh-token.{$tokenType}_token_expiration");

        return $expiresAt ?? ($configTokenExpiration ? now()->addMinutes($configTokenExpiration) : null);
    }
}
