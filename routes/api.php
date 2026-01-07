<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WeddingPageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;

// Health check route (for testing)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is working',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google OAuth
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Wedding Pages
    Route::get('/wedding-pages', [WeddingPageController::class, 'index']);
    Route::post('/wedding-pages', [WeddingPageController::class, 'store']);
    Route::get('/wedding-pages/{slug}', [WeddingPageController::class, 'show']);
    Route::put('/wedding-pages/{id}', [WeddingPageController::class, 'update']);
    Route::put('/wedding-pages/{id}/design', [WeddingPageController::class, 'updateDesign']);
    Route::post('/wedding-pages/{id}/revisions', [WeddingPageController::class, 'requestRevision']);

    // Payments
    Route::post('/wedding-pages/{id}/payment', [PaymentController::class, 'generatePaymentLink']);

    // AI
    Route::post('/ai/generate-image', [AIController::class, 'generateImage']);
    Route::post('/ai/generate-text', [AIController::class, 'generateText']);
});

// Public page view (for live pages)
Route::get('/wedding-pages/{slug}/public', [WeddingPageController::class, 'show']);

// Payment webhook (public, but verified by signature)
Route::post('/payments/webhook', [PaymentController::class, 'handleWebhook']);

