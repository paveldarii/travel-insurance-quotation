<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Quotation;

use App\Domain\Quotation\AgeLoadResolver;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AgeLoadResolverTest extends TestCase
{
    private AgeLoadResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new AgeLoadResolver();
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function validAges(): array
    {
        return [
            'minimum first band' => [18, 6_000],
            'maximum first band' => [30, 6_000],

            'minimum second band' => [31, 7_000],
            'maximum second band' => [40, 7_000],

            'minimum third band' => [41, 8_000],
            'maximum third band' => [50, 8_000],

            'minimum fourth band' => [51, 9_000],
            'maximum fourth band' => [60, 9_000],

            'minimum fifth band' => [61, 10_000],
            'maximum fifth band' => [70, 10_000],
        ];
    }

    #[DataProvider('validAges')]
    public function test_it_resolves_age_load_basis_points(
        int $age,
        int $expectedBasisPoints,
    ): void {
        $actual = $this->resolver->resolveBasisPoints($age);

        self::assertSame($expectedBasisPoints, $actual);
    }

    #[DataProvider('invalidAges')]
    public function test_it_rejects_unsupported_ages(int $age): void
    {
        $this->expectException(DomainException::class);

        $this->resolver->resolveBasisPoints($age);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidAges(): array
    {
        return [
            'below minimum' => [17],
            'negative' => [-1],
            'above maximum' => [71],
        ];
    }
}
