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

    // Routes Clients
    Route::apiResource('clients', ClientController::class);

    // Routes Comptes
    Route::get('comptes', [CompteController::class, 'index']);
    Route::post('comptes', [CompteController::class, 'store']);
    Route::get('comptes/{compte}', [CompteController::class, 'show']);
    Route::put('comptes/{compte}', [CompteController::class, 'update']);
    Route::delete('comptes/{compte}', [CompteController::class, 'destroy']);
});
