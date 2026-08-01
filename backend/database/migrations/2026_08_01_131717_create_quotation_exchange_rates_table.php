<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'quotation_exchange_rates',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('quotation_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('currency_exchange_rate_id')
                    ->nullable()
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->char('base_currency_code', 3);
                $table->char('quote_currency_code', 3);
                $table->date('rate_date');
                $table->decimal('rate', 20, 10);
                $table->string('source', 100);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_exchange_rates');
    }
};
