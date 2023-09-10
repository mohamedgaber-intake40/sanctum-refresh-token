<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\SanctumServiceProvider;
use MohamedGaber\SanctumRefreshToken\SanctumRefreshTokenServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->foreignId('country_id')->nullable()->constrained();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            SanctumServiceProvider::class,
            SanctumRefreshTokenServiceProvider::class,
        ];
    }
}
