<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Sponsoring extends Model
{
    protected $connection = 'mongodb'; 
    protected $collection = 'sponsoring'; 

    protected $fillable = [
        'nomSponsoring',
        'description',
        'prix',
        'durée',
        'id_annonce' 
    ];

    // Relation avec Annonce
    public function annonce()
    {
        return $this->belongsTo(Annonce::class, 'id_annonce','_id');
    }

}
