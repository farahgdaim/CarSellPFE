<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\SponsoringController;



use App\Http\Controllers\AuthController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ContratController;
use App\http\Controllers\ConversationController;
use App\Http\Controllers\SponsorshipController;
use App\Models\Contrat;


/*
|--------------------------------------------------------------------------
| Routes for Admins
|--------------------------------------------------------------------------
|
| Admin login/logout and management routes.
|
*/

Route::prefix('admin')->group(function () {
    Route::post('register', [AdminController::class, 'store']);
    Route::post('login', [AdminController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminController::class, 'logout']);
        Route::get('me', [AdminController::class, 'me']);
        Route::get('/reportedAnnonces', [AdminController::class, 'getReportedAnnonces']);
        
        Route::post('/validateAnnonce/{annonceId}', [AdminController::class, 'validateAnnonce']);
        //Pas encore testé 
        Route::delete('/deleteReportedAnnonce/{annonceId}', [AdminController::class, 'deleteReportedAnnonce']);
        Route::post('/warnUser/{userId}', [AdminController::class, 'warnUser']);
        // Routes spécifiques avant les routes génériques
        Route::get('expert-requests', [AdminController::class, 'listPendingExpertRequests']);
        Route::post('expert-request/{requestId}/accept', [AdminController::class, 'acceptExpertRequest']);
        Route::post('expert-request/{requestId}/reject', [AdminController::class, 'rejectExpertRequest']);

        // Routes génériques à la fin
        Route::get('/', [AdminController::class, 'index']);
        Route::get('{id}', [AdminController::class, 'show']);
        Route::delete('{id}', [AdminController::class, 'destroy']);
        
    });
});




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
    Route::put('profile', [UtilisateurController::class, 'updateProfile']); // New route for profile update

    // Annonces
    Route::get('/annonces', [AnnonceController::class, 'getAnnonce']);
    Route::post('/annonces', [AnnonceController::class, 'create']);
    Route::get('/annonces/{id}', [AnnonceController::class, 'getAnnonceById']);
    Route::delete('/annonces/{id}', [AnnonceController::class, 'destroy']);
    Route::put('/annonces/{id}', [AnnonceController::class, 'update']);
    Route::post('/reportAnnonce/{id}', [AnnonceController::class, 'reportAnnonce']);

    // Sponsoring
    Route::get('/sponsoring', [SponsoringController::class, 'getSponsoring']);
    Route::post('/sponsoring', [SponsoringController::class, 'create']);
    Route::get('/sponsoring/{id}', [SponsoringController::class, 'getSponsoringById']);
    Route::delete('/sponsoring/{id}', [SponsoringController::class, 'destroy']);
    Route::put('/sponsoring/{id}', [SponsoringController::class, 'update']);

    // Contrat
    Route::get('/contrats', [ContratController::class, 'getContrat']);
    Route::post('/contrats', [ContratController::class, 'createContrat']);
    Route::get('/contrats/{id}', [ContratController::class, 'getContratById']);
    Route::delete('/contrats/{id}', [ContratController::class, 'deleteContrat']);
    Route::put('/contrats/{id}', [ContratController::class, 'updateContrat']);

    // Sponsorship
    Route::get('/sponsorships', [SponsorshipController::class, 'getSponsorship']);
    Route::post('/sponsorships', [SponsorshipController::class, 'createSponsorship']);
    Route::get('/sponsorships/{id}', [SponsorshipController::class, 'getSponsorshipById']);
    Route::delete('/sponsorships/{id}', [SponsorshipController::class, 'deleteSponsorship']);
    Route::put('/sponsorships/{id}', [SponsorshipController::class, 'updateSponsorship']);

    // Conversation
    Route::get('/conversations', [ConversationController::class, 'getAllConversation']);
    Route::get('/conversations/{conversationId}', [ConversationController::class, 'getConversationById']);
    Route::post('/conversations', [ConversationController::class, 'createConversation']);
    Route::delete('/conversations/{conversationId}', [ConversationController::class, 'deleteConversation']);
    Route::post('/conversations/{conversationId}/message', [ConversationController::class, 'addmessage']);
    //Route::put('/conversations/{conversationId}/message/{messageId}',[ConversationController::class,'updateMessage']);
    //Route::delete('/conversations/{conversationId}/message/{messageId}',[ConversationController::class,'deleteMessage']);
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
