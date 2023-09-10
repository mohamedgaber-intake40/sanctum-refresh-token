<?php

declare(strict_types=1);

use Tests\Datasets\User;

it('auth token can be generated with expires time from config', function () {
    $this->freezeTime();
    $authToken = $this->user->createAuthToken('auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken['expires_at']->timestamp)
        ->toBe(now()->addMinutes(config('sanctum-refresh-token.auth_token_expiration'))->timestamp);
});

it('auth token can be generated with custom expires time', function () {
    $this->freezeTime();
    $tokenExpiresMinutes = fake()->numberBetween(1, 50);
    $authToken = $this->user->createAuthToken('auth', now()->addMinutes($tokenExpiresMinutes));
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken['expires_at']->timestamp)
        ->toBe(now()->addMinutes($tokenExpiresMinutes)->timestamp);
});

it('auth token can be generated with abilities', function () {
    $authToken = $this->user->createAuthToken(name: 'auth', abilities: ['create-user', 'update-user']);
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken->abilities)->toHaveCount(3);
    expect($authToken->accessToken->can('auth'))
        ->toBe(true)
        ->and($authToken->accessToken->can('create-user'))
        ->toBe(true)
        ->and($authToken->accessToken->can('update-user'))
        ->toBe(true);
});

it('auth token can\'t access refresh route', function () {
    $authToken = $this->user->createAuthToken(name: 'auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($authToken->plainTextToken);
    $response = $this->postJson(route('api.token.refresh'));
    $response->assertStatus(401);
});

it('auth token can access any other route except refresh route', function () {
    $authToken = $this->user->createAuthToken(name: 'auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($authToken->plainTextToken);
    $this->postJson(route('api.token.refresh'))->assertStatus(401);
    $this->postJson(route('api.other-route'))->assertStatus(200);
});

it('auth token never expire can be generated ', function () {
    config(['sanctum-refresh-token.auth_token_expiration' => null]);
    $authToken = $this->user->createAuthToken('auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken['expires_at'])->toBe(null);
});
