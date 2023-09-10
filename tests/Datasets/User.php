<?php

declare(strict_types=1);

namespace Tests\Datasets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use MohamedGaber\SanctumRefreshToken\Traits\HasApiTokens;
use Tests\Datasets\Factory\UserFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [
        'id',
    ];

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
