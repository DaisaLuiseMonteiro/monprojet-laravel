<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Compte;
use App\Models\Client;

class CompteFactory extends Factory
{
    protected $model = Compte::class;

    public function definition(): array
    {
        $types = ['epargne', 'cheque'];
        $statuts = ['actif', 'bloque', 'ferme'];
        $devises = ['FCFA', 'USD', 'EUR']; // Changé XOF en FCFA

        // Noms sénégalais courants pour les titulaires
        $prenomsHomme = ['Mamadou', 'Ibrahima', 'Moussa', 'Abdoulaye', 'Ousmane', 'Cheikh', 'Modou', 'Amadou', 'Samba', 'Babacar'];
        $prenomsFemme = ['Fatou', 'Aminata', 'Mariama', 'Aïssatou', 'Khadija', 'Ndeye', 'Adama', 'Seynabou', 'Astou', 'Diarra'];
        $nomsFamille = ['Diop', 'Ndiaye', 'Sarr', 'Fall', 'Ba', 'Gaye', 'Sow', 'Sy', 'Diallo', 'Thiam'];

        $sexe = $this->faker->randomElement(['M', 'F']);
        $prenom = $sexe === 'M' ? $this->faker->randomElement($prenomsHomme) : $this->faker->randomElement($prenomsFemme);
        $nom = $this->faker->randomElement($nomsFamille);
        $titulaire = $prenom . ' ' . $nom;

        return [
            'client_id' => Client::inRandomOrder()->first()->id,
            'numeroCompte' => $this->faker->unique()->bankAccountNumber(),
            'titulaire' => $titulaire,
            'type' => $this->faker->randomElement($types),
            'solde' => $this->faker->numberBetween(1000, 1000000),
            'devise' => $this->faker->randomElement($devises),
            'dateCreation' => $this->faker->date(),
            'statut' => $this->faker->randomElement($statuts),
            'metadata' => [
                'derniereModification' => $this->faker->dateTime(),
                'version' => 1
            ],
        ];
    }
}
