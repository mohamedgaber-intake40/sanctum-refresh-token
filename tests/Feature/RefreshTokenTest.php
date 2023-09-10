<?php

declare(strict_types=1);

use Tests\Datasets\User;

it('refresh token can be generated with expires time from config', function () {
    $this->freezeTime();
    $refreshToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($refreshToken->accessToken['expires_at']->timestamp)
        ->toBe(now()->addMinutes(config('sanctum-refresh-token.refresh_token_expiration'))->timestamp);
});

it('refresh token can be generated with custom expires time', function () {
    $this->freezeTime();
    $tokenExpiresMinutes = fake()->numberBetween(1, 50);
    $refreshToken = $this->user->createRefreshToken('refresh', now()->addMinutes($tokenExpiresMinutes));
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($refreshToken->accessToken['expires_at']->timestamp)
        ->toBe(now()->addMinutes($tokenExpiresMinutes)->timestamp);
});

it('refresh token can access refresh route', function () {
    $refreshToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($refreshToken->plainTextToken);
    $response = $this->postJson(route('api.token.refresh'));
    $response->assertStatus(200);
});

it("auth token can't access any other route ", function () {
    $refreshToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($refreshToken->plainTextToken);
    $response = $this->postJson(route('api.other-route'));
    $response->assertStatus(401);
});

it('refresh token can access multiple refresh routes 1', function () {
    config(['sanctum-refresh-token.refresh_route_names' => [
        'api.token.refresh1',
        'api.token.refresh2',
    ]]);
    $refreshToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($refreshToken->plainTextToken);
    $this->postJson(route('api.token.refresh1'))->assertStatus(200);
});

it('refresh token can access multiple refresh routes 2', function () {
    config(['sanctum-refresh-token.refresh_route_names' => [
        'api.token.refresh1',
        'api.token.refresh2',
    ]]);
    $refreshToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($refreshToken->plainTextToken);
    $this->postJson(route('api.token.refresh2'))->assertStatus(200);
});

it('refresh token can access refresh route when refresh route names is array in config', function () {
    config(['sanctum-refresh-token.refresh_route_names' => [
        'api.token.refresh1',
    ]]);
    $refreshToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($refreshToken->plainTextToken);
    $this->postJson(route('api.token.refresh1'))->assertStatus(200);
});

it('refresh token can\'t access other routes when refresh route names is array in config', function () {
    config(['sanctum-refresh-token.refresh_route_names' => [
        'api.token.refresh1',
    ]]);
    $refreshToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($refreshToken->plainTextToken);
    $this->postJson(route('api.other-route'))->assertStatus(401);
});

it('refresh token never expire can be generated  ', function () {
    config(['sanctum-refresh-token.refresh_token_expiration' => null]);
    $authToken = $this->user->createRefreshToken('refresh');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken['expires_at'])->toBe(null);
});
