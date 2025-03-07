<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Utilisateur extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'mongodb';
    protected $collection = 'utilisateurs';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'dateInscription',
        'notifications',
        'paiements',
        // Facultatif : référence à l'admin (exemple d'usage)
        'ref_id_admin'
    ];

    protected $hidden = [
        'password'
    ];


    // ================== JWT Auth Requirements ==================

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public function conversations (){
        return $this->hasMany(Conversation::class,'id_conversation','_id');
    }
}
