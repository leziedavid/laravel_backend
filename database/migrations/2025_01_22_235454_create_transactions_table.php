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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('libelle')->nullable();
            $table->decimal('sortie_caisse', 15, 2)->nullable();
            $table->decimal('sortie_banque', 15, 2)->nullable();
            $table->decimal('entree_caisse', 15, 2)->nullable();
            $table->decimal('entree_banque', 15, 2)->nullable();
            $table->string('type_operation')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
