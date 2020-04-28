<?php

namespace ShabuShabu\Tightrope\Tests\App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Model;

class User extends Model
{
    use SoftDeletes,
        HasApiTokens;

    protected $fillable = [
        'name',
        'password',
        'email',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
