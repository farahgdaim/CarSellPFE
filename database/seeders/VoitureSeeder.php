<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Voiture;
class VoitureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Voiture::create([
            'Marque' => 'Tesla',
           'Modèle' => 'Model S',
           'Puissance'=>'1000',
           'Couleur' => 'Noir',
           'Année' =>'2001',
           'kilometrage' => '2000',
           'DateDeMiseEnCirculation' => '2024-01-01',
           'Energie' => 'GPL'

        ]);
    }
}
