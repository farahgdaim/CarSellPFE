<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Image;
class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Image::create([
            'chemin' => 'D:\MON_PFE\diagClassePfe.drawio.png',
            'voiture_id' => '1'
        ]);
    }
}
