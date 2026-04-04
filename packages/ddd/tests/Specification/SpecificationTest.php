<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Tests\Specification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Ddd\Specification\AbstractSpecification;

final class SpecificationTest extends TestCase
{
    #[Test]
    public function satisfiesWhenConditionIsTrue(): void
    {
        $spec = $this->isGreaterThan(5);

        self::assertTrue($spec->isSatisfiedBy(10));
        self::assertFalse($spec->isSatisfiedBy(3));
    }

    #[Test]
    public function andRequiresBothToBeTrue(): void
    {
        $spec = $this->isGreaterThan(5)->and($this->isLessThan(10));

        self::assertTrue($spec->isSatisfiedBy(7));
        self::assertFalse($spec->isSatisfiedBy(3));
        self::assertFalse($spec->isSatisfiedBy(12));
    }

    #[Test]
    public function orRequiresEitherToBeTrue(): void
    {
        $spec = $this->isGreaterThan(10)->or($this->isLessThan(3));

        self::assertTrue($spec->isSatisfiedBy(15));
        self::assertTrue($spec->isSatisfiedBy(1));
        self::assertFalse($spec->isSatisfiedBy(5));
    }

    #[Test]
    public function notInvertsResult(): void
    {
        $spec = $this->isGreaterThan(5)->not();

        self::assertTrue($spec->isSatisfiedBy(3));
        self::assertFalse($spec->isSatisfiedBy(10));
    }

    #[Test]
    public function composesComplexSpecification(): void
    {
        $between5And10 = $this->isGreaterThan(5)->and($this->isLessThan(10));
        $greaterThan20 = $this->isGreaterThan(20);
        $spec = $between5And10->or($greaterThan20);

        self::assertTrue($spec->isSatisfiedBy(7));
        self::assertTrue($spec->isSatisfiedBy(25));
        self::assertFalse($spec->isSatisfiedBy(3));
        self::assertFalse($spec->isSatisfiedBy(15));
    }

    #[Test]
    public function compositeSpecificationsAreChainable(): void
    {
        $spec = $this->isGreaterThan(5)
            ->and($this->isLessThan(20))
            ->not()
            ->or($this->isGreaterThan(100));

        self::assertTrue($spec->isSatisfiedBy(3));
        self::assertTrue($spec->isSatisfiedBy(25));
        self::assertTrue($spec->isSatisfiedBy(150));
        self::assertFalse($spec->isSatisfiedBy(10));
    }

    private function isGreaterThan(int $threshold): AbstractSpecification
    {
        return new class ($threshold) extends AbstractSpecification {
            public function __construct(private readonly int $threshold) {}

            public function isSatisfiedBy(mixed $candidate): bool
            {
                return $candidate > $this->threshold;
            }
        };
    }

    private function isLessThan(int $threshold): AbstractSpecification
    {
        return new class ($threshold) extends AbstractSpecification {
            public function __construct(private readonly int $threshold) {}

            public function isSatisfiedBy(mixed $candidate): bool
            {
                return $candidate < $this->threshold;
            }
        };
    }
}
