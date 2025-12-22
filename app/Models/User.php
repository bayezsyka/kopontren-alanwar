<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public const ROLE_OWNER = 'owner';
    public const ROLE_KASIR = 'kasir';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'ui_mode', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];
}
