<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'currency_exchange_rates',
            function (Blueprint $table): void {
                $table->id();

                $table->char('base_currency_code', 3);
                $table->char('quote_currency_code', 3);

                $table->date('rate_date');

                /*
                 * One unit of base currency equals this number
                 * of quote-currency units.
                 *
                 * Example:
                 * 1 EUR = 1.08 USD
                 */
                $table->decimal('rate', 20, 10);

                $table->string('source', 100);
                $table->timestamp('retrieved_at')->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'base_currency_code',
                        'quote_currency_code',
                        'rate_date',
                    ],
                    'currency_rates_pair_date_unique',
                );

                $table->index(
                    ['rate_date', 'quote_currency_code'],
                    'currency_rates_date_quote_index',
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_exchange_rates');
    }
};
