<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Vehicule extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $fillable = [
        'Categorie','Marque', 'Modèle','TypeCarburant', 'Puissance',
        'DateDeMiseEnCirculation','Cylindre', 'Kilométrage',
        'nbPortes','boiteVitesse','etat','equipements'
        
    ];
}
