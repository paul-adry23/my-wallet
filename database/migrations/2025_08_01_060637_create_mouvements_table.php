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
        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->string('etiquette');
            $table->text('description')->nullable();
            $table->decimal('montant', 15, 2);
            $table->enum ('nature' ['depense', 'revnu']);
            $table->foreingId('utilisateur_id')->constraint('utilisateurs')->onDelete('cascade');
            $table->foreingId('categorie_id_id')->constraint('categories')->onDelete('cascade');
            $table->foreingId('compte_id_id')->constraint('comptes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvements');
    }
};
