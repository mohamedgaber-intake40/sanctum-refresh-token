<?php

declare(strict_types=1);

/*
 * Tokens created with Sanctum's own createToken() must keep working after this
 * package is installed.
 *
 * createToken() defaults to abilities ['*'], and Sanctum's can() returns true
 * for any ability when the token holds '*'. That made both guard checks fail
 * at once: can('auth') was true because of the wildcard, and cant('refresh')
 * was false for the same reason, so the token authenticated nowhere.
 */

it('a standard sanctum token can still access normal routes', function () {
    $token = $this->user->createToken('legacy')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.other-route'))
        ->assertOk();
});

it('a standard sanctum token cannot access the refresh route', function () {
    $token = $this->user->createToken('legacy')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.token.refresh'))
        ->assertUnauthorized();
});

it('a scoped sanctum token can still access normal routes', function () {
    $token = $this->user->createToken('legacy', ['create-user'])->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.other-route'))
        ->assertOk();
});

it('an auth token created with the wildcard ability can access normal routes', function () {
    // createAuthToken() merges its own 'auth' in, so this produces ['*', 'auth']
    // -- a token this package's own API can create and could not use.
    $token = $this->user->createAuthToken(name: 'auth', abilities: ['*'])->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.other-route'))
        ->assertOk();
});

it('an auth token created with the wildcard ability cannot access the refresh route', function () {
    $token = $this->user->createAuthToken(name: 'auth', abilities: ['*'])->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.token.refresh'))
        ->assertUnauthorized();
});
