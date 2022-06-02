Sanctum Refresh Token
====

<p align="center">
<a href="https://packagist.org/packages/mohamedgaber-intake40/sanctum-refresh-token"><img src="https://img.shields.io/packagist/dt/mohamedgaber-intake40/sanctum-refresh-token" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/mohamedgaber-intake40/sanctum-refresh-token"><img src="https://img.shields.io/packagist/v/mohamedgaber-intake40/sanctum-refresh-token" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/mohamedgaber-intake40/sanctum-refresh-token"><img src="https://img.shields.io/packagist/l/mohamedgaber-intake40/sanctum-refresh-token" alt="License"></a>
</p>


Add refresh token feature to laravel sanctum official package.

# Install

```
$ composer require mohamedgaber-intake40/sanctum-refresh-token
```

The Package is auto discovered , but you can register Service Provider in config/app.php

```php
 'providers' => [
    \MohamedGaber\SanctumRefreshToken\SanctumRefreshTokenServiceProvider::class
];
```

* if you want to customize the migrations

```
$ php artisan vendor:publish --tag=sanctum-refresh-token-migrations
```

* if you want to customize the config

```
$ php artisan vendor:publish --tag=sanctum-refresh-token-config
```

* to publish both migrations and config

```
$ php artisan vendor:publish --provider="MohamedGaber\SanctumRefreshToken\SanctumRefreshTokenServiceProvider"

```

# Usage

* in tokenable model use this trait

```php
use MohamedGaber\SanctumRefreshToken\Traits\HasApiTokens;
```

this will add createAuthToken , createRefreshToken methods to this model

for example: to create auth token for user

```php
$user->createAuthToken('web');

//you can add expired minutes as a parameter
$user->createAuthToken('web',20);

//you can add abilities as a parameter like the official package
$user->createAuthToken('web',20,['test:update']);

```

then you need to create refresh token

```php
$user->createRefreshToken('web');

//you can add expired minutes as a parameter
$user->createRefreshToken('web',20);
```
this token will be valid only for refresh route in sanctum-refresh-token.php config file

then you need to use auth:sanctum middleware for both authenticated routes and refresh routes 
_____________


# Notes

- if you do not add expired minutes when creating auth token or refresh token , it will consider expired minutes from sanctum-refresh-token.php config file
- make sure to set expiration to null in sanctum official package config file



