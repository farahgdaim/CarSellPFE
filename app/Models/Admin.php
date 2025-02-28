<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Admin extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;

    protected $collection = 'utilisateurs'; // Utilise la même collection que les utilisateurs
    protected $fillable = ['name', 'email', 'password', 'role'];

    // ================== JWT Auth Requirements ==================

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
