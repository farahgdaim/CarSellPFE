<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\EvaluationController;



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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées pour les utilisateurs (guard "api")
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead']);

    // Paiements
    Route::get('/paiements', [PaiementController::class, 'index']);
    Route::post('/paiements', [PaiementController::class, 'store']);
    Route::post('/paiements/update', [PaiementController::class, 'updateStatus']);

    // Demande de passage en expert
    Route::post('/expert/request', [ExpertController::class, 'requestExpertRole']);
    // Action réservée aux experts (exemple d'acceptation d'évaluation)
    Route::post('/expert/accept-evaluation', [ExpertController::class, 'acceptEvaluation']);

    // Evaluation endpoints
    Route::post('/evaluation/request', [EvaluationController::class, 'requestEvaluation']);
    Route::get('/evaluation/pending', [EvaluationController::class, 'listPendingEvaluations']);
    Route::post('/evaluation/accept/{evaluationId}', [EvaluationController::class, 'acceptEvaluation']);
    Route::post('/evaluation/reject/{evaluationId}', [EvaluationController::class, 'rejectEvaluation']);
    Route::post('/evaluation/submit-rapport/{evaluationId}', [EvaluationController::class, 'submitRapport']);
});

// Routes pour l'administration (guard "admin")
Route::prefix('admin')->group(function () {
    Route::post('/create', [AdminController::class, 'store']);
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/me', [AdminController::class, 'me']);
    Route::get('/list', [AdminController::class, 'index']);
    Route::get('/{id}', [AdminController::class, 'show']);
    Route::delete('/{id}', [AdminController::class, 'destroy']);

    // Actions d'administration sur les demandes d'expertise
    Route::get('/pending-experts', [ExpertController::class, 'listPendingExpertRequests']);
    Route::post('/accept-expert/{expertId}', [ExpertController::class, 'acceptExpertRole']);
    Route::post('/reject-expert/{expertId}', [ExpertController::class, 'rejectExpertRole']);
});
