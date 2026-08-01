<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'quotation_travelers',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('quotation_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('full_name', 255);
                $table->date('date_of_birth');

                /*
                 * Snapshot of age at trip start.
                 */
                $table->unsignedTinyInteger('age_at_trip_start');

                /*
                 * 0.6000 is stored as 6000 basis points.
                 */
                $table->unsignedSmallInteger('age_load_basis_points');

                $table->unsignedBigInteger('subtotal_base_minor');
                $table->unsignedBigInteger('subtotal_minor');

                $table->timestamps();

                $table->index(
                    ['quotation_id', 'date_of_birth'],
                    'quotation_travelers_quote_dob_index',
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_travelers');
    }
};
