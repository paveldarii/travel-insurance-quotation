<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\User;
use App\Services\CreateQuotationService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

final class QuotationController extends Controller
{
    public function store(
        StoreQuotationRequest $request,
        CreateQuotationService $service,
    ): JsonResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new RuntimeException(
                'The authenticated user could not be resolved.'
            );
        }

        $quotation = $service->execute(
            user: $user,
            data: $request->validated(),
        );

        return (new QuotationResource($quotation))
            ->response()
            ->setStatusCode(201);
    }
}
