<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Compte;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $comptes = Compte::all();

        foreach ($comptes as $compte) {
            // Créer 2 transactions par compte : 1 dépôt et 1 retrait
            Transaction::factory()->create([
                'compte_id' => $compte->id,
                'type' => 'depot',
                'montant' => rand(10000, 100000), // Montant aléatoire entre 10k et 100k
            ]);

            Transaction::factory()->create([
                'compte_id' => $compte->id,
                'type' => 'retrait',
                'montant' => rand(5000, 50000), // Montant aléatoire entre 5k et 50k
            ]);
        }
    }
}

