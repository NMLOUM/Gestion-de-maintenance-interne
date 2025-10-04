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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Employé qui évalue
            $table->integer('rating')->comment('Note de 1 à 5'); // 1 à 5 étoiles
            $table->text('comment')->nullable(); // Commentaire optionnel
            $table->timestamps();

            // Un employé ne peut évaluer qu'une seule fois son ticket
            $table->unique(['ticket_id', 'user_id']);

            // Index pour les performances
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
