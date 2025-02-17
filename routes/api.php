<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnonceController;
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
Route::get('/annonces', [AnnonceController::class, 'getAnnonce']);
Route::post('/annonces', [AnnonceController::class, 'store']);
Route::get('/annonces/{id}', [AnnonceController::class, 'getAnnonceById']);
Route::delete('/annonces/{id}', [AnnonceController::class, 'destroy']);
Route::put('/annonces/{id}', [AnnonceController::class, 'update']);