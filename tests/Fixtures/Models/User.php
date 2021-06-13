<?php


namespace JWTAuth\Tests\Fixtures\Models;

use JWTAuth\Contracts\WithJwtToken;
use JWTAuth\Eloquent\HasJwtToken;

class User extends \Illuminate\Foundation\Auth\User implements WithJwtToken
{
    protected $guarded = [];

    use HasJwtToken;
}
