<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;

class CompteSeeder extends Seeder
{
    public function run(): void
    {
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            \App\Models\Compte::factory()->create([
                'client_id' => $client->id,
            ]);
        }
    }
}
