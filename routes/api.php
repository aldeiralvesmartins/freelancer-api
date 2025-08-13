<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! URL::hasValidSignature($request)) {
        return redirect(config('app.frontend_url') . '/login?verified=invalid');
    }

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect(config('app.frontend_url') . '/login?verified=invalid');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect(config('app.frontend_url') . '/login?verified=already');
    }

    $user->markEmailAsVerified();
    $user->save();

    return redirect(config('app.frontend_url') . '/login?verified=success');
})->name('verification.verify')->middleware('signed');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/projects/byClient', [ProjectController::class, 'getProjectsbyClient']);
    Route::get('/projects/inProgress', [ProjectController::class, 'getProjectsInProgress']);
    Route::get('/proposals/allProposal', [ProposalController::class, 'allProposal']);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('proposals', ProposalController::class);
    Route::apiResource('messages', MessageController::class);
    Route::apiResource('ratings', RatingController::class);
    Route::apiResource('notifications', NotificationController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('wallets', WalletController::class);
    Route::get('/wallet', [WalletController::class, 'me']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/projects/{project}/proposals', [ProposalController::class, 'store']);
    Route::post('/payments/{id}/release', [PaymentController::class, 'release']);
    Route::post('/proposals/{proposal}/accept', [ProposalController::class, 'accept']);
    Route::post('/proposals/{proposal}/reject', [ProposalController::class, 'reject']);
    Route::post('/proposals/deposit', [PaymentController::class, 'depositAndLock']);
});
