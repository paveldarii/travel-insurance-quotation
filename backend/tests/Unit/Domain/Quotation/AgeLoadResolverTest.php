<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Quotation;

use App\Domain\Quotation\AgeLoadResolver;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AgeLoadResolverTest extends TestCase
{
    private AgeLoadResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('quotation.age_loads', [
            [
                'minimum_age' => 18,
                'maximum_age' => 30,
                'basis_points' => 6000,
            ],
            [
                'minimum_age' => 31,
                'maximum_age' => 40,
                'basis_points' => 7000,
            ],
            [
                'minimum_age' => 41,
                'maximum_age' => 50,
                'basis_points' => 8000,
            ],
            [
                'minimum_age' => 51,
                'maximum_age' => 60,
                'basis_points' => 9000,
            ],
            [
                'minimum_age' => 61,
                'maximum_age' => 70,
                'basis_points' => 10000,
            ],
        ]);

        $this->resolver = new AgeLoadResolver();
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function validAges(): array
    {
        return [
            'minimum first band' => [18, 6000],
            'maximum first band' => [30, 6000],

            'minimum second band' => [31, 7000],
            'maximum second band' => [40, 7000],

            'minimum third band' => [41, 8000],
            'maximum third band' => [50, 8000],

            'minimum fourth band' => [51, 9000],
            'maximum fourth band' => [60, 9000],

            'minimum fifth band' => [61, 10000],
            'maximum fifth band' => [70, 10000],
        ];
    }

    #[DataProvider('validAges')]
    public function test_it_resolves_age_load_basis_points(
        int $age,
        int $expectedBasisPoints,
    ): void {
        $actual = $this->resolver->resolveBasisPoints($age);

        self::assertSame(
            $expectedBasisPoints,
            $actual,
        );
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

    #[DataProvider('invalidAges')]
    public function test_it_rejects_unsupported_ages(
        int $age,
    ): void {
        $this->expectException(DomainException::class);

        $this->resolver->resolveBasisPoints($age);
    }
}
