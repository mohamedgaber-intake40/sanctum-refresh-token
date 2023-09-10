<?php

declare(strict_types=1);

use Tests\Datasets\User;

it('auth token can be generated with expires time from config', function () {
    $this->freezeTime();
    $user = User::factory()->create();
    $this->assertDatabaseCount('users', 1);
    $authToken = $user->createAuthToken('auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken['expires_at']->timestamp)
        ->toBe(now()->addMinutes(config('sanctum-refresh-token.auth_token_expiration'))->timestamp);
});

it('auth token can be generated with custom expires time', function () {
    $this->freezeTime();
    $user = User::factory()->create();
    $this->assertDatabaseCount('users', 1);
    $tokenExpiresMinutes = fake()->numberBetween(1, 50);
    $authToken = $user->createAuthToken('auth', now()->addMinutes($tokenExpiresMinutes));
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken['expires_at']->timestamp)
        ->toBe(now()->addMinutes($tokenExpiresMinutes)->timestamp);
});

it('auth token can be generated with abilities', function () {
    $user = User::factory()->create();
    $this->assertDatabaseCount('users', 1);
    $authToken = $user->createAuthToken(name: 'auth', abilities: ['create-user', 'update-user']);
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
    Route::post('api/refresh-token', fn () => response(['message' => 'success']))->name('api.token.refresh')->middleware('auth:sanctum');
    $user = User::factory()->create();
    $this->assertDatabaseCount('users', 1);
    $authToken = $user->createAuthToken(name: 'auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($authToken->plainTextToken);
    $response = $this->postJson(route('api.token.refresh'));
    $response->assertStatus(401);
});

it('auth token can access any other route except refresh route', function () {
    Route::post('api/refresh-token', fn () => response(['message' => 'success']))->name('api.token.refresh')->middleware('auth:sanctum');
    Route::post('api/other-route', fn () => response(['message' => 'success']))->name('api.other-route')->middleware('auth:sanctum');
    $user = User::factory()->create();
    $this->assertDatabaseCount('users', 1);
    $authToken = $user->createAuthToken(name: 'auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->withToken($authToken->plainTextToken);
    $this->postJson(route('api.token.refresh'))->assertStatus(401);
    $this->postJson(route('api.other-route'))->assertStatus(200);
});

it('auth token never expire can be generated ', function () {
    config(['sanctum-refresh-token.auth_token_expiration' => null]);
    $user = User::factory()->create();
    $this->assertDatabaseCount('users', 1);
    $authToken = $user->createAuthToken('auth');
    $this->assertDatabaseCount('personal_access_tokens', 1);
    expect($authToken->accessToken['expires_at'])->toBe(null);
});
