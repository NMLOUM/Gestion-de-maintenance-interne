<?php
// database/migrations/2024_01_01_000007_create_ticket_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // created, assigned, status_changed, comment_added, etc.
            $table->text('description'); // Description lisible de l'action
            $table->json('old_values')->nullable(); // Anciennes valeurs
            $table->json('new_values')->nullable(); // Nouvelles valeurs
            $table->timestamps();
            
            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_histories');
    }
};
