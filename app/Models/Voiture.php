<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Voiture extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $fillable = [
        'Marque', 'Modèle', 'Puissance', 'Année',
        'DateDeMiseEnCirculation', 'Kilométrage',
        'Couleur', 'Energie'
    ];
}
