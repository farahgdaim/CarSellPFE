<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voitures', function (Blueprint $table) {
            $table->id();
            $table->String('Marque');
            $table->String('Modèle');
            $table->String('Puissance');
            $table->String('Couleur');
            $table->unsignedInteger('Année');
            $table->unsignedInteger('Kilometrage');
            $table->date('DateDeMiseEnCirculation');
            $table->enum('Energie', ['Essence', 'Diesel', 'GPL', 'Electrique']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voitures');
    }
};
