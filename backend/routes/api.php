<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\QuotationHistoryController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [
        AuthController::class,
        'register',
    ]);

    Route::post('/login', [
        AuthController::class,
        'login',
    ]);

    Route::middleware('auth:api')->group(function (): void {
        Route::get('/me', function (Request $request): JsonResponse {
            $user = $request->user();

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user?->id,
                        'name' => $user?->name,
                        'email' => $user?->email,
                    ],
                ],
            ]);
        });
    });
});

Route::middleware('auth:api')->group(function (): void {
    Route::post('/quotation', [
        QuotationController::class,
        'store',
    ]);
    Route::get(
        '/quotations',
        [QuotationHistoryController::class, 'index'],
    );
});
