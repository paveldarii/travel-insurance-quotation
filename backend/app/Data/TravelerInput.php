<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class TravelerInput
{
    public function __construct(
        public string $fullName,
        public CarbonImmutable $dateOfBirth,
    ) {}
}
