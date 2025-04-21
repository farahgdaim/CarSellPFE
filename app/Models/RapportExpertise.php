<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RapportExpertise extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'rapportExpertises';

    protected $fillable = [
        'contenu',         // Contenu du rapport (texte ou chemin vers fichier)
        'ref_id_expert',   // Identifiant de l'expert ayant rédigé le rapport
        'ref_id_eval',     // Référence vers la demande d'évaluation (DemandeEvaluation)
        'ref_id_admin',
        'reported_by',
        'is_reported',    // Référence à l'admin qui pourra être renseigné en cas de signalement
        'status'
    ];
}
