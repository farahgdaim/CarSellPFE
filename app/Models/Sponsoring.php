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
        'Ref_id_annonce' 
    ];

    // Relation avec Annonce
    public function annonce()
    {
        return $this->belongsTo(Annonce::class, 'Ref_id_annonce','_id');
    }

    public function sponsorships()
    {
        return $this->hasMany(Sponsorship::class, 'id_sponsoring');
    }

}
