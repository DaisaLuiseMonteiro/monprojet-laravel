<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Groupe API v1
Route::prefix(config('api.prefix'))->group(function () {

    // Routes d'authentification
    Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->post('logout', [App\Http\Controllers\AuthController::class, 'logout']);

    // Routes Clients (protégées)
    Route::middleware('auth:sanctum')->apiResource('clients', ClientController::class);

    // Routes Comptes (temporairement non protégées pour debug)
    Route::get('comptes', [CompteController::class, 'index']);
    Route::post('comptes', [CompteController::class, 'store']);
    Route::get('comptes/{compte}', [CompteController::class, 'show']);
    Route::put('comptes/{compte}', [CompteController::class, 'update']);
    Route::delete('comptes/{compte}', [CompteController::class, 'destroy']);
});

