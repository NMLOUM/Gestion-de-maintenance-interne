<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier la colonne status pour ajouter 'validated'
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved', 'validated', 'closed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'validated' de l'enum
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved', 'closed', 'cancelled') DEFAULT 'pending'");
    }
};
