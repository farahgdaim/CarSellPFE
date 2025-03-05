<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaiementController;

/*
|--------------------------------------------------------------------------
| Routes for Admins
|--------------------------------------------------------------------------
|
| Admin login/logout and management routes.
|
*/
Route::prefix('admin')->group(function () {
    // Public route for admin creation
    Route::post('register', [AdminController::class, 'store']);
    
    // Public login route for admin
    Route::post('login', [AdminController::class, 'login']);
    
    // Protected routes for admin actions
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminController::class, 'logout']);
        Route::get('me', [AdminController::class, 'me']);
        Route::get('/', [AdminController::class, 'index']);
        Route::get('{id}', [AdminController::class, 'show']);
        Route::delete('{id}', [AdminController::class, 'destroy']);
        // Route to list all pending expert requests
        Route::get('expert-requests', [AdminController::class, 'listPendingExpertRequests']);
        Route::post('expert-request/{requestId}/accept', [AdminController::class, 'acceptExpertRequest']);
        Route::post('expert-request/{requestId}/reject', [AdminController::class, 'rejectExpertRequest']);        
    });
});

/*
|--------------------------------------------------------------------------
| Routes for Utilisateurs (Authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| Routes for Utilisateur Actions
|--------------------------------------------------------------------------
*/
Route::prefix('utilisateur')->middleware('auth')->group(function () {
    Route::post('expert-request', [UtilisateurController::class, 'requestExpertRole']);
    Route::post('evaluation-request', [UtilisateurController::class, 'requestEvaluation']);
});

/*
|--------------------------------------------------------------------------
| Routes for Experts
|--------------------------------------------------------------------------
*/
Route::prefix('expert')->middleware('auth')->group(function () {
    Route::get('evaluations/pending', [ExpertController::class, 'listPendingEvaluations']);
    Route::post('evaluation/{demandeId}/accept', [ExpertController::class, 'acceptEvaluation']);
    Route::post('evaluation/{demandeId}/reject', [ExpertController::class, 'rejectEvaluation']);
    Route::post('evaluation/{demandeId}/rapport', [ExpertController::class, 'submitRapport']);
});

/*
|--------------------------------------------------------------------------
| Routes for Notifications
|--------------------------------------------------------------------------
*/
Route::prefix('notifications')->middleware('auth')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::post('read', [NotificationController::class, 'markAsRead']);
});

/*
|--------------------------------------------------------------------------
| Routes for Paiements
|--------------------------------------------------------------------------
*/
Route::prefix('paiements')->middleware('auth')->group(function () {
    Route::get('/', [PaiementController::class, 'index']);
    Route::post('/', [PaiementController::class, 'store']);
    Route::put('status', [PaiementController::class, 'updateStatus']);
});
