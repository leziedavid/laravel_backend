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
        Schema::create('categorie_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->decimal('defautPrice', 10, 2)->default(0);  // Colonne pour le prix par défaut
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorie_transactions');
    }
};
