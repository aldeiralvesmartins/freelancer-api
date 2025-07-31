<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/projects/byClient', [ProjectController::class, 'getProjectsbyClient']);
    Route::get('/proposals/allProposal', [ProposalController::class, 'allProposal']);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('proposals', ProposalController::class);
    Route::apiResource('messages', MessageController::class);
    Route::apiResource('ratings', RatingController::class);
    Route::apiResource('notifications', NotificationController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('wallets', WalletController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/projects/{project}/proposals', [ProposalController::class, 'store']);
    Route::post('/payments/{id}/release', [PaymentController::class, 'release']);
});
