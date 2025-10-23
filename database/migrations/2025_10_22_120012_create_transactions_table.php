<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('compte_id');
            $table->enum('type', ['depot', 'retrait', 'virement', 'frais']);
            $table->bigInteger('montant');
            $table->string('devise');
            $table->string('description')->nullable();
            $table->dateTime('dateTransaction');
            $table->enum('statut', ['en_attente', 'validee', 'annulee']);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
