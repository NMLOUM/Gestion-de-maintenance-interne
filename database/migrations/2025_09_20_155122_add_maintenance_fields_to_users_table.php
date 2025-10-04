<?php
// database/migrations/2024_01_01_000003_add_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['employe', 'technicien', 'responsable_it', 'direction'])->default('employe');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'service_id', 'role', 'is_active', 'last_login_at']);
        });
    }
};