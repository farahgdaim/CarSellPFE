<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Admin extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'mongodb';
    protected $collection = 'admins'; // Utilisation de la collection dédiée aux admins

    protected $fillable = ['nom', 'prenom', 'email', 'password'];

    protected $hidden = ['password'];

    // ================== JWT Auth Requirements ==================
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function supervisedAnnonces()
    {
        return $this->hasMany(Annonce::class, 'supervise_par');
    }

}
