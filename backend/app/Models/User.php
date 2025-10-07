<?php

namespace App\Models;

use App\Models\Auth\AuthenticatableBase;

class User extends AuthenticatableBase
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
