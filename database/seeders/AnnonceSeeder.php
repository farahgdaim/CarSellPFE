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
            'status' => 'disponible',
            'Ref_id_admin' => 1,
            'Ref_id_user' => 2,
            'vehicule' => [ 
                'Categorie'=>'voiture', // Embedding directement
                'Marque' => 'Peugeot',
                'Modèle' => '208',
                'TypeCarburant'=>'Essence',
                'Puissance' => 120,
                'DateDeMiseEnCirculation' => '2019-06-15',
                'Cylindre' => '3 Cylindres',
                'Kilométrage' => 30000,
                'nbPortes'=>'4',
                'boiteVitesse'=>'automatique',
                'etat'=>'neuf',
                'Equipement' => 'Jantes Aluminium',
                
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
