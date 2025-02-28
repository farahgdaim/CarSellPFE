<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExpertController;



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

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Les routes protégées par JWT
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

    // L’utilisateur fait sa demande
    Route::post('/expert/request', [ExpertController::class, 'requestExpertRole']);
    // Action réservée aux experts
    Route::post('/expert/accept-evaluation', [ExpertController::class, 'acceptEvaluation']);

});


Route::prefix('admin')->group(function () {
    Route::post('/create', [AdminController::class, 'store']);
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/me', [AdminController::class, 'me']);
    Route::get('/list', [AdminController::class, 'index']);
    Route::get('/{id}', [AdminController::class, 'show']);
    Route::delete('/{id}', [AdminController::class, 'destroy']);
    Route::post('/accept-expert/{userId}', [ExpertController::class, 'acceptExpertRole']);
    Route::post('/reject-expert/{userId}', [ExpertController::class, 'rejectExpertRole']);
});