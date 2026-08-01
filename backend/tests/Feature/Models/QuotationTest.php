<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class QuotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotation_generates_public_id(): void
    {
        $quotation = Quotation::factory()->create();

        self::assertMatchesRegularExpression(
            '/^[A-Z0-9]{8}$/',
            $quotation->public_id,
        );
    }

    public function test_each_quotation_receives_different_public_id(): void
    {
        $first = Quotation::factory()->create();
        $second = Quotation::factory()->create();

        self::assertNotSame(
            $first->public_id,
            $second->public_id,
        );
    }

    public function test_public_id_has_database_unique_constraint(): void
    {
        $first = Quotation::factory()->create();

        $this->expectException(
            \Illuminate\Database\QueryException::class,
        );

        DB::table('quotations')->insert([
            'public_id' => $first->public_id,
            'user_id' => $first->user_id,
            'base_currency_code' => 'EUR',
            'currency_code' => 'EUR',
            'quoted_on' => '2026-08-01',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-30',
            'trip_days' => 30,
            'fixed_daily_rate_base_minor' => 300,
            'total_base_minor' => 11_700,
            'total_minor' => 11_700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_route_key_uses_public_id(): void
    {
        $quotation = new Quotation();

        self::assertSame(
            'public_id',
            $quotation->getRouteKeyName(),
        );
    }
}
