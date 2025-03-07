<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DemandeEvaluation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'demandeEvaluations';

    protected $fillable = [
        'ref_id_annonce',     // Identifiant de l'annonce
        'ref_id_demandeur',   // Identifiant du demandeur (utilisateur)
        'ref_id_expert',      // Identifiant de l'expert choisi (correspond à l'ID dans la collection experts)
        'status'              // 'pending', 'accepted' ou 'rejected'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];
}
