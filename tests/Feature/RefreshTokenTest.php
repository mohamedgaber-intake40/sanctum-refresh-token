<?php

declare(strict_types=1);

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

it('refresh token can be related to single auth token only', function () {
    $authToken = $this->user->createAuthToken('auth');
    $refreshToken = $this->user->createRefreshToken('refresh', null, $authToken);
    expect(count($refreshToken->accessToken->abilities))->toBe(2)
        ->and($refreshToken->accessToken->can('access-token-id:' . $authToken->accessToken->id))->toBeTrue();
});
it('x-access-token header is required for refresh token related to single auth token to access refresh route', function () {
    $authToken = $this->user->createAuthToken('auth');
    $refreshToken = $this->user->createRefreshToken('refresh', null, $authToken);
    $this->withToken($refreshToken->plainTextToken);
    $response = $this->postJson(route('api.token.refresh'));
    $response->assertStatus(401);
});
it(
    'x-access-token header is required and has a valid value for related auth token for refresh token related to single auth token to access refresh route',
    function () {
        $authToken = $this->user->createAuthToken('auth');
        $refreshToken = $this->user->createRefreshToken('refresh', null, $authToken);
        $this->withToken($refreshToken->plainTextToken);
        $this->withHeader('x-access-token', $authToken->plainTextToken);
        $response = $this->postJson(route('api.token.refresh'));
        $response->assertStatus(200);
    }
);
