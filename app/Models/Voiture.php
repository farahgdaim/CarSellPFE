<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Voiture extends Model
{
    protected $connection = 'mongodb';
    protected $collection ='voitures';
    
    use HasFactory;
    protected $fillable = [
        'Marque',
        'Modèle',
        'Puissance',
        'Couleur',
        'Année',
        'Kilometrage',
        'DateDeMiseEnCirculation',
        'Energie'
    ];
    
    
}
