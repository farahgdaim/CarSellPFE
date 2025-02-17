<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Annonce extends Model
{
    use HasFactory;
    protected $connection = 'mongodb'; // Utilisation de MongoDB
    protected $collection = 'annonces'; // Nom de la collection MongoDB

    protected $fillable = [
        'Titre', 'Description', 'DatePub', 'Prix', 'isSponsored',
        'Ref_id_admin', 'Ref_id_user', 'voiture', 'images'
    ];

    // Relation avec Voiture (document imbriqué)
    public function voiture()
    {
        return $this->embedsOne(Voiture::class);
    }

    // Relation avec Images (documents imbriqués)
    public function images()
    {
        return $this->embedsMany(Image::class);
    }
}
