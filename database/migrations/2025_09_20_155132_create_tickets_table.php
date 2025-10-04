<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Numéro auto-généré
            $table->string('title');
            $table->text('description');
            
            // Relations
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            // Statuts et priorité
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'validated', 'closed', 'cancelled'])
                  ->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            
            // Dates importantes
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Localisation
            $table->string('location')->nullable(); // Bureau, salle, étage
            
            // Estimation
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['status', 'priority']);
            $table->index(['assigned_to', 'status']);
            $table->index(['service_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};