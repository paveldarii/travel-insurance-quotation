<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Quotation;

use App\Domain\Quotation\TravelerAgeResolver;
use Carbon\CarbonImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

final class TravelerAgeResolverTest extends TestCase
{
    private TravelerAgeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new TravelerAgeResolver();
    }

    public function test_it_calculates_age_at_trip_start(): void
    {
        $dateOfBirth = CarbonImmutable::parse('1998-05-10');
        $tripStart = CarbonImmutable::parse('2026-10-01');

        $age = $this->resolver->resolve(
            $dateOfBirth,
            $tripStart,
        );

        self::assertSame(28, $age);
    }

    public function test_it_does_not_increment_age_before_birthday(): void
    {
        $dateOfBirth = CarbonImmutable::parse('1998-10-10');
        $tripStart = CarbonImmutable::parse('2026-10-01');

        $age = $this->resolver->resolve(
            $dateOfBirth,
            $tripStart,
        );

        self::assertSame(27, $age);
    }

    public function test_it_increments_age_on_birthday(): void
    {
        $dateOfBirth = CarbonImmutable::parse('1998-10-01');
        $tripStart = CarbonImmutable::parse('2026-10-01');

        $age = $this->resolver->resolve(
            $dateOfBirth,
            $tripStart,
        );

        self::assertSame(28, $age);
    }

    public function test_it_rejects_birth_date_after_trip_start(): void
    {
        $this->expectException(DomainException::class);

        $this->resolver->resolve(
            CarbonImmutable::parse('2030-01-01'),
            CarbonImmutable::parse('2026-10-01'),
        );
    }
}
