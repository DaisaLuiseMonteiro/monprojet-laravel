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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('client_id');
            $table->string('numeroCompte');
            $table->string('titulaire');
            $table->enum('type', ['epargne', 'cheque']);
            $table->bigInteger('solde');
            $table->string('devise');
            $table->date('dateCreation');
            $table->enum('statut', ['actif', 'bloque', 'ferme']);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
