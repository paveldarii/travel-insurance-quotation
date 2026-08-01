<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->char('base_currency_code', 3)
                ->default('EUR');

            $table->char('currency_code', 3);

            $table->date('quoted_on');
            $table->date('start_date');
            $table->date('end_date');

            $table->unsignedSmallInteger('trip_days');

            /*
             * Fixed daily rate in base-currency minor units.
             * 300 means EUR 3.00.
             */
            $table->unsignedInteger('fixed_daily_rate_base_minor');

            /*
             * Total before currency conversion.
             */
            $table->unsignedBigInteger('total_base_minor');

            /*
             * Final total in the requested currency.
             */
            $table->unsignedBigInteger('total_minor');

            $table->timestamps();

            $table->index(
                ['user_id', 'created_at'],
                'quotations_user_created_index',
            );

            $table->index(
                ['quoted_on', 'currency_code'],
                'quotations_date_currency_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
