<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\QuotationHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', static function () {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::prefix('auth')->group(
    function (): void {
        Route::post(
            '/register',
            [AuthController::class, 'register'],
        );

        Route::post(
            '/login',
            [AuthController::class, 'login'],
        );

        Route::middleware('auth:api')->group(
            function (): void {
                Route::get(
                    '/me',
                    [AuthController::class, 'me'],
                );
            },
        );
    },
);

Route::middleware('auth:api')->group(
    function (): void {
        Route::post(
            '/quotation',
            [QuotationController::class, 'store'],
        );

        Route::get(
            '/quotations',
            [QuotationHistoryController::class, 'index'],
        );

        Route::get(
            '/quotations/{quotationId}',
            [QuotationController::class, 'show'],
        )->where(
            'quotationId',
            '[A-Za-z0-9]{8}',
        );
    },
);
