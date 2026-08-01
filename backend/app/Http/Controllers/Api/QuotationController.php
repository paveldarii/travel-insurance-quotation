<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\User;
use App\Services\CreateQuotationService;
use Illuminate\Http\Request;
use RuntimeException;

final class QuotationController extends Controller
{
    public function store(
        StoreQuotationRequest $request,
        CreateQuotationService $createQuotation,
    ): QuotationResource {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new RuntimeException(
                'Authenticated user could not be resolved.',
            );
        }

        $quotation = $createQuotation->execute(
            user: $user,
            data: $request->validated(),
        );

        $quotation->load([
            'travelers',
            'exchangeRate',
        ]);

        return new QuotationResource(
            $quotation,
        );
    }

    public function show(
        Request $request,
        string $quotationId,
    ): QuotationResource {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new RuntimeException(
                'Authenticated user could not be resolved.',
            );
        }

        /*
         * Query through the authenticated user to prevent
         * one user from viewing another user's quotation.
         *
         * firstOrFail() intentionally returns 404 whether
         * the quotation does not exist or belongs to
         * another user.
         */
        $quotation = $user
            ->quotations()
            ->with([
                'travelers',
                'exchangeRate',
            ])
            ->where(
                'public_id',
                strtoupper($quotationId),
            )
            ->firstOrFail();

        return new QuotationResource(
            $quotation,
        );
    }
}
