<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Annonce;
use App\Models\Sponsoring;
class SponsoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $annonce = Annonce::first(); // Prend la première annonce existante

        if ($annonce) {
            Sponsoring::create([
                'durée' => 30,
                'montantPaye' => 100,
                'Ref_id_annonce' => $annonce->_id // Référence vers l'annonce
            ]);
        }
    }
    }

