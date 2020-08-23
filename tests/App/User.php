<?php

namespace ShabuShabu\Tightrope\Tests\App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Model;
use Laravel\Passport\HasApiTokens;

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
