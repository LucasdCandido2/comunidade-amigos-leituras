<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\InteractionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\ExternalSourceController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/assets/allowed-types', [AssetController::class, 'allowedTypes']);
Route::get('/assets/{id}', [AssetController::class, 'show'])->name('assets.show');
Route::get('/assets/{id}/signed', [AssetController::class, 'signedDownload'])->name('assets.signed');
Route::get('/leaderboard', [GamificationController::class, 'leaderboard']);
Route::get('/badges', [GamificationController::class, 'allBadges']);
Route::get('/topics/{id}/pdf', [PdfController::class, 'exportTopic']);

Route::middleware('bearer')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('topics', TopicController::class);
    Route::get('topics/{topic}/interactions', [InteractionController::class, 'index']);
    Route::post('topics/{topic}/interactions', [InteractionController::class, 'store']);
    Route::get('topics/{topic}/assets', [AssetController::class, 'byTopic']);
    Route::get('/works/top', [WorkController::class, 'index']);
    Route::apiResource('works', WorkController::class, ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    Route::apiResource('assets', AssetController::class, ['only' => ['store', 'destroy']]);
    Route::get('/assets/{id}/signed-url', [AssetController::class, 'generateSignedUrl']);
    Route::post('/assets/upload-inline', [AssetController::class, 'uploadInline']);
    
    // Notificações
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Busca
    Route::get('/search', [SearchController::class, 'search']);
    
    // Gamificação
    Route::get('/gamification/stats', [GamificationController::class, 'stats']);
    
    // Votação
    Route::post('/vote/{type}/{id}', [VoteController::class, 'vote']);
    Route::get('/vote/{type}/{id}/status', [VoteController::class, 'getVoteStatus']);
    
    // Fontes Externas
    Route::get('/external-sources', [ExternalSourceController::class, 'index']);
    Route::post('/external-sources', [ExternalSourceController::class, 'store']);
    Route::delete('/external-sources/{id}', [ExternalSourceController::class, 'destroy']);
    
    // Cargos e Permissões (Admin)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::get('/permissions', [RoleController::class, 'permissions']);
    Route::post('/roles/{id}/assign-user', [RoleController::class, 'assignUser']);
    Route::delete('/roles/{roleId}/users/{userId}', [RoleController::class, 'removeUser']);
    
    // Usuários (Admin) - requer autenticação
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::post('/users/assign-role', [UserController::class, 'assignRole']);
    Route::get('/users/roles', [UserController::class, 'listRoles']);
    
    Route::post('/logout', [AuthController::class, 'logout']);
});