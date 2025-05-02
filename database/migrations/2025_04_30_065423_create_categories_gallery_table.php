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
        Schema::create('categories_gallery', function (Blueprint $table) {
            $table->bigIncrements('idcategories_gallery'); // Clé primaire personnalisée
            $table->string('libelle');                     // Libellé de la catégorie
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories_gallery');
    }
};
