<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompteController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Routes d'authentification
Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('logout', [App\Http\Controllers\AuthController::class, 'logout']);

// Routes Clients (protégées)
Route::middleware('auth:sanctum')->apiResource('clients', ClientController::class);

// Routes Comptes
Route::get('comptes', [CompteController::class, 'index']);
Route::post('comptes', [CompteController::class, 'store']);
Route::get('comptes/{compte}', [CompteController::class, 'show']);
Route::put('comptes/{compte}', [CompteController::class, 'update']);
Route::delete('comptes/{compte}', [CompteController::class, 'destroy']);