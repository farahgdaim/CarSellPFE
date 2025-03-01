<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Evaluation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'evaluations';

    protected $fillable = [
        'ref_id_annonce',      // Identifiant de l'annonce
        'ref_id_demandeur',    // Identifiant de l'utilisateur demandeur
        'ref_id_expert',       // Identifiant du document Expert choisi
        'status',              // "pending", "accepted", "rejected", "rapport_submitted"
        'rapport'              // Contenu du rapport ou chemin vers le fichier (optionnel)
    ];

    protected $attributes = [
        'status' => 'pending'
    ];
}
