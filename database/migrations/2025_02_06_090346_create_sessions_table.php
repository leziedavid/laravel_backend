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
        // Création de la table 'sessions'
        Schema::create('sessions', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Création de la clé étrangère sur 'user_id'
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index(); // Ajout d'un index pour 'last_activity'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
