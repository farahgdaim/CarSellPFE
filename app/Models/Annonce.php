<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Annonce extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'annonces';

    protected $fillable = [
        'titre',
        'description',
        'datePub',
        'prix',
        'isSponsored', // Boolean
        'vendeur_id'
    ];

    public function vendeur()
    {
        return $this->belongsTo(Utilisateur::class, 'vendeur_id');
    }
}
