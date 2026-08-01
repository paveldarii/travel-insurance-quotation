<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuotationSummaryResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use RuntimeException;

final class QuotationHistoryController extends Controller
{
    public function index(
        Request $request,
    ): AnonymousResourceCollection {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new RuntimeException(
                'Authenticated user could not be resolved.',
            );
        }

        $quotations = $user
            ->quotations()
            ->withCount('travelers')
            ->latest('created_at')
            ->paginate(
                perPage: 10,
            );

        return QuotationSummaryResource::collection(
            $quotations,
        );
    }
}
