# Procédure Générique de Création d'un Système Laravel

## Contexte
Ce document décrit la procédure complète pour créer un système Laravel avec authentification, gestion des utilisateurs, et API RESTful. Utilise spécifiquement l'exemple des comptes bancaires pour illustrer la création d'endpoints CRUD complets. Le système utilise des UUID, des validations personnalisées, et suit les bonnes pratiques Laravel.

## Exemple Pratique: Système Bancaire
Nous utiliserons l'exemple concret d'un système bancaire pour démontrer chaque étape, mais les principes s'appliquent à tout type de projet Laravel.

## Prérequis
- Laravel 11.x
- PHP 8.2+
- Composer
- Node.js & npm
- Base de données (MySQL/PostgreSQL)

## Étape 0: Installation et Authentification Laravel

### 0.1 Installation Laravel
```bash
composer create-project laravel/laravel nom-du-projet
cd nom-du-projet
php artisan serve
```

### 0.2 Configuration Authentification
```bash
composer require laravel/ui
php artisan ui bootstrap --auth
npm install && npm run build
```

Ou pour Laravel 11 avec Breeze:
```bash
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run build
php artisan migrate
```

### 0.3 Configuration Base de Données
Modifier `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_base
DB_USERNAME=root
DB_PASSWORD=
```

## Étape 1: Création des Migrations

### 1.1 Migration Générique avec UUID
```bash
php artisan make:migration create_table_name_table
```

Structure de base pour toute entité:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nom_table', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Vos champs ici
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nom_table');
    }
};
```

### 1.2 Exemple Pratique: Migration Comptes
```bash
php artisan make:migration create_comptes_table
```

Contenu spécifique aux comptes:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->string('numeroCompte')->unique();
            $table->string('titulaire');
            $table->enum('type', ['epargne', 'cheque'])->index();
            $table->bigInteger('solde');
            $table->string('devise')->index();
            $table->date('dateCreation')->index();
            $table->enum('statut', ['actif', 'bloque', 'ferme'])->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
```

## Étape 2: Création des Modèles

### 2.1 Modèle Client
```bash
php artisan make:model Client
```

Contenu:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'telephone',
        'cni', 'sexe', 'adresse', 'statut', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'email_verified_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            if (!empty($model->password)) {
                $model->password = bcrypt($model->password);
            }
        });
    }

    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
```

### 2.2 Modèle Compte
```bash
php artisan make:model Compte
```

Contenu:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'numeroCompte', 'titulaire', 'type', 'solde',
        'devise', 'dateCreation', 'statut', 'metadata', 'client_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'dateCreation' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
```

### 2.3 Modèle Transaction
```bash
php artisan make:model Transaction
```

Contenu:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'compte_id', 'type', 'montant', 'devise',
        'description', 'dateTransaction', 'statut', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'dateTransaction' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
```

## Étape 3: Création des Factories

### 3.1 Factory Client
```bash
php artisan make:factory ClientFactory
```

Contenu:
```php
<?php
namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $sexe = $this->faker->randomElement(['M', 'F']);
        $cniPrefix = $sexe === 'M' ? '1' : '2';
        $cniRest = $this->faker->numerify('############');
        $cni = $cniPrefix . $cniRest;

        $telPrefix = $this->faker->randomElement(['70', '75', '76', '77', '78']);
        $telephone = $telPrefix . $this->faker->numerify('#######');

        $username = strtolower($this->faker->unique()->userName());
        $email = $username . '@gmail.com';

        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName($sexe === 'M' ? 'male' : 'female'),
            'sexe' => $sexe,
            'email' => $email,
            'password' => 'password',
            'telephone' => $telephone,
            'cni' => $cni,
            'adresse' => $this->faker->address(),
            'statut' => $this->faker->randomElement(['actif', 'inactif']),
            'metadata' => [
                'derniereModification' => $this->faker->dateTime(),
                'version' => 1
            ],
        ];
    }
}
```

### 3.2 Factory Compte
```bash
php artisan make:factory CompteFactory
```

Contenu:
```php
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
        $devises = ['XOF', 'USD', 'EUR'];

        return [
            'client_id' => Client::factory(),
            'numeroCompte' => $this->faker->unique()->bankAccountNumber(),
            'titulaire' => $this->faker->name(),
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
```

### 3.3 Factory Transaction
```bash
php artisan make:factory TransactionFactory
```

Contenu:
```php
<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Transaction;
use App\Models\Compte;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $types = ['depot', 'retrait', 'virement', 'frais'];
        $statuts = ['en_attente', 'validee', 'annulee'];
        $devises = ['XOF', 'USD', 'EUR'];

        return [
            'compte_id' => Compte::factory(),
            'type' => $this->faker->randomElement($types),
            'montant' => $this->faker->numberBetween(100, 100000),
            'devise' => $this->faker->randomElement($devises),
            'description' => $this->faker->sentence(),
            'dateTransaction' => $this->faker->dateTime(),
            'statut' => $this->faker->randomElement($statuts),
            'metadata' => [
                'derniereModification' => $this->faker->dateTime(),
                'version' => 1
            ],
        ];
    }
}
```

## Étape 4: Création des Seeders

### 4.1 Seeder Client
```bash
php artisan make:seeder ClientSeeder
```

Contenu:
```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->count(10)->create();
    }
}
```

### 4.2 Seeder Compte
```bash
php artisan make:seeder CompteSeeder
```

Contenu:
```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;

class CompteSeeder extends Seeder
{
    public function run(): void
    {
        Compte::factory()->count(10)->create();
    }
}
```

### 4.3 Seeder Transaction
```bash
php artisan make:seeder TransactionSeeder
```

Contenu:
```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Transaction::factory()->count(20)->create();
    }
}
```

## Étape 5: Création des Request Classes

### 5.1 ClientRequest
```bash
php artisan make:request ClientRequest
```

Contenu:
```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email|ends_with:@gmail.com',
            'password' => 'required|string|min:6|confirmed',
            'telephone' => [
                'required',
                'unique:clients,telephone',
                'regex:/^(70|75|76|77|78)\d{7}$/'
            ],
            'cni' => [
                'required',
                'unique:clients,cni',
                'size:13',
                'regex:/^[12]\d{12}$/'
            ],
            'sexe' => 'required|in:M,F',
            'adresse' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'email.ends_with' => 'L\'email doit être de type @gmail.com',
            'telephone.regex' => 'Le numéro de téléphone doit commencer par 70,75,76,77 ou 78 et comporter 9 chiffres',
            'cni.regex' => 'Le CNI doit comporter 13 chiffres et commencer par 1 pour un garçon ou 2 pour une fille',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',
        ];
    }
}
```

### 5.2 CompteRequest
```bash
php artisan make:request CompteRequest
```

Contenu:
```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'numeroCompte' => 'required|string|unique:comptes,numeroCompte|max:20',
            'titulaire' => 'required|string|max:255',
            'type' => 'required|in:epargne,cheque',
            'solde' => 'required|numeric|min:0',
            'devise' => 'required|string|max:5',
            'dateCreation' => 'required|date',
            'statut' => 'required|in:actif,bloque,ferme',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'numeroCompte.unique' => 'Ce numéro de compte existe déjà.',
            'type.in' => 'Le type de compte doit être "epargne" ou "cheque".',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'statut.in' => 'Le statut doit être "actif", "bloque" ou "ferme".',
        ];
    }
}
```

## Étape 6: Création des Contrôleurs et Routes

### 6.1 Contrôleur Client
```bash
php artisan make:controller ClientController --resource
```

Contenu:
```php
<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\ClientRequest;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return Client::with('comptes')->paginate(10);
    }

    public function store(ClientRequest $request)
    {
        $client = Client::create($request->validated());
        return response()->json($client, 201);
    }

    public function show(Client $client)
    {
        return $client->load('comptes.transactions');
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return response()->json($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['message' => 'Client supprimé']);
    }
}
```

### 6.2 Contrôleur Compte
```bash
php artisan make:controller CompteController --resource
```

Contenu:
```php
<?php
namespace App\Http\Controllers;

use App\Models\Compte;
use App\Http\Requests\CompteRequest;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    public function index()
    {
        return Compte::with('client')->paginate(10);
    }

    public function store(CompteRequest $request)
    {
        $compte = Compte::create($request->validated());
        return response()->json($compte->load('client'), 201);
    }

    public function show(Compte $compte)
    {
        return $compte->load('client', 'transactions');
    }

    public function update(CompteRequest $request, Compte $compte)
    {
        $compte->update($request->validated());
        return response()->json($compte->load('client'));
    }

    public function destroy(Compte $compte)
    {
        $compte->delete();
        return response()->json(['message' => 'Compte supprimé']);
    }
}
```

### 6.3 Contrôleur Transaction
```bash
php artisan make:controller TransactionController --resource
```

Contenu:
```php
<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::with('compte.client')->paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'compte_id' => 'required|exists:comptes,id',
            'type' => 'required|in:depot,retrait,virement,frais',
            'montant' => 'required|numeric|min:0.01',
            'devise' => 'required|string|max:5',
            'description' => 'nullable|string|max:255',
        ]);

        $transaction = Transaction::create($request->all());
        return response()->json($transaction->load('compte'), 201);
    }

    public function show(Transaction $transaction)
    {
        return $transaction->load('compte.client');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,validee,annulee',
        ]);

        $transaction->update($request->only('statut'));
        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Transaction supprimée']);
    }
}
```

### 6.4 Routes API
Dans `routes/api.php`:
```php
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\TransactionController;

Route::apiResource('clients', ClientController::class);
Route::apiResource('comptes', CompteController::class);
Route::apiResource('transactions', TransactionController::class);
```

## Étape 7: Mise à jour du DatabaseSeeder

Dans `database/seeders/DatabaseSeeder.php`:
```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ClientSeeder::class,
            CompteSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
```

## Étape 8: Exécution des Migrations et Seeders

```bash
php artisan migrate
php artisan db:seed
```

## Étape 9: Tests

Créer des tests pour vérifier le fonctionnement:

```bash
php artisan make:test ClientTest
php artisan make:test CompteTest
php artisan make:test TransactionTest
```

## Fonctionnalités Implémentées

- ✅ Gestion des clients avec validation CNI sénégalaise
- ✅ Gestion des comptes bancaires avec UUID
- ✅ Gestion des transactions financières
- ✅ Relations Eloquent entre modèles
- ✅ Factories pour génération de données de test
- ✅ Seeders pour population de la base
- ✅ Validation des données avec Form Requests
- ✅ API RESTful avec contrôleurs resource
- ✅ Support des métadonnées JSON
- ✅ Hashing automatique des mots de passe
- ✅ Indexes de base de données pour optimisation

## Commandes Utiles

```bash
# Migration et seeding
php artisan migrate:fresh --seed

# Création d'un client
php artisan tinker
>>> App\Models\Client::factory()->create()

# Tests
php artisan test

# Cache clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

Ce guide permet de reproduire exactement ce système bancaire Laravel avec toutes ses fonctionnalités.