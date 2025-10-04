<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_name'); // Nom original du fichier
            $table->string('file_name'); // Nom sur le serveur
            $table->string('file_path'); // Chemin complet
            $table->string('mime_type');
            $table->integer('file_size'); // En bytes
            $table->timestamps();
            
            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_attachments');
    }
};