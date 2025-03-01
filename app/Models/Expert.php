<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Expert extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'experts';

    protected $fillable = [
         'ref_id_utilisateur',   // Référence vers l'utilisateur (document de la collection "utilisateurs")
         'certifications',       // Chemin (ou tableau de chemins) vers le fichier PDF de certification
         'domaineExpertise',
         'anneesExperience',
         'status',               // 'pending', 'approved', 'rejected'
         'ref_id_admin'          // Référence vers l'admin qui a validé/rejeté la demande (optionnel)
    ];

    protected $attributes = [
         'status' => 'pending'
    ];
}
