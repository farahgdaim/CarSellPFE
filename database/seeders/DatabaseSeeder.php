<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use App\Models\Annonce;
use App\Models\Message;
use App\Models\Notification;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seed Utilisateurs
        Utilisateur::create([
            'nom' => 'Admin',
            'prenom' => 'Master',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'tel' => '1234567890'
        ]);

        // Seed Annonces
        Annonce::create([
            'titre' => 'Voiture de Luxe',
            'description' => 'Une superbe voiture à vendre.',
            'datePub' => now(),
            'prix' => 50000,
            'isSponsored' => false,
            'vendeur_id' => Utilisateur::first()->id,
        ]);

        // Seed Messages
        Message::create([
            'contenu' => 'Bonjour, est-ce que cette voiture est disponible ?',
            'date' => now(),
            'statut' => 'non lu',
            'sender_id' => Utilisateur::first()->id,
            'receiver_id' => Utilisateur::first()->id,
        ]);

        // Seed Notifications
        Notification::create([
            'contenu' => 'Nouvelle annonce ajoutée',
            'date' => now(),
            'statut' => 'non vue',
            'user_id' => Utilisateur::first()->id,
        ]);
    }
}
