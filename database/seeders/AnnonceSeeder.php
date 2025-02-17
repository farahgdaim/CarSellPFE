<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Annonce;

class AnnonceSeeder extends Seeder
{
    public function run()
    {
        $annonce = new Annonce([
            'Titre' => 'Voiture d’occasion à vendre',
            'Description' => 'Très bon état, faible kilométrage.',
            'DatePub' => now(),
            'Prix' => 20000,
            'isSponsored' => false,
            'Ref_id_admin' => 1,
            'Ref_id_user' => 2,
            'voiture' => [  // Embedding directement
                'Marque' => 'Peugeot',
                'Modèle' => '208',
                'Puissance' => 120,
                'Année' => 2019,
                'DateDeMiseEnCirculation' => '2019-06-15',
                'Kilométrage' => 30000,
                'Couleur' => 'Rouge',
                'Energie' => 'Essence'
            ],
            'images' => [  // Embedding directement les images
                [
                    'chemin' => '/images/voiture1.jpg',
                    'format' => 'jpg',
                    'taille' => '400KB'
                ],
                [
                    'chemin' => '/images/voiture2.jpg',
                    'format' => 'jpg',
                    'taille' => '500KB'
                ]
            ]
        ]);

        $annonce->save();  // Sauvegarde l'annonce avec ses documents imbriqués
    }
}
