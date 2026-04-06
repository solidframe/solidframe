<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Tests\Expectation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;
use SolidFrame\Archtest\Exception\ArchViolationException;

final class ArchExpectationTest extends TestCase
{
    private static string $fixtureDir;

    public static function setUpBeforeClass(): void
    {
        self::$fixtureDir = __DIR__ . '/../Fixtures';
    }

    // --- Structural assertions ---

    #[Test]
    public function areFinalPassesForFinalClasses(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/FinalReadonlyClass.php')
            ->areFinal();
    }

    #[Test]
    public function areFinalFailsForNonFinalClasses(): void
    {
        $this->expectException(ArchViolationException::class);
        $this->expectExceptionMessageMatches('/is not final/');

        Arch::assertThat(self::$fixtureDir . '/NonFinalClass.php')
            ->areFinal();
    }

    #[Test]
    public function areReadonlyPassesForReadonlyClasses(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/FinalReadonlyClass.php')
            ->areReadonly();
    }

    #[Test]
    public function areReadonlyFailsForNonReadonlyClasses(): void
    {
        $this->expectException(ArchViolationException::class);
        $this->expectExceptionMessageMatches('/is not readonly/');

        Arch::assertThat(self::$fixtureDir . '/NonFinalClass.php')
            ->areReadonly();
    }

    #[Test]
    public function areInterfacesPassesForInterfaces(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/SomeInterface.php')
            ->areInterfaces();
    }

    #[Test]
    public function areInterfacesFailsForClasses(): void
    {
        $this->expectException(ArchViolationException::class);
        $this->expectExceptionMessageMatches('/is not an interface/');

        Arch::assertThat(self::$fixtureDir . '/NonFinalClass.php')
            ->areInterfaces();
    }

    #[Test]
    public function areEnumsPassesForEnums(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/SomeEnum.php')
            ->areEnums();
    }

    #[Test]
    public function areAbstractPassesForAbstractClasses(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/AbstractClass.php')
            ->areAbstract();
    }

    // --- Naming assertions ---

    #[Test]
    public function haveSuffixPasses(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/SuffixedController.php')
            ->haveSuffix('Controller');
    }

    #[Test]
    public function haveSuffixFails(): void
    {
        $this->expectException(ArchViolationException::class);
        $this->expectExceptionMessageMatches('/does not have suffix/');

        Arch::assertThat(self::$fixtureDir . '/NonFinalClass.php')
            ->haveSuffix('Controller');
    }

    #[Test]
    public function havePrefixPasses(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/PrefixedAbstractService.php')
            ->havePrefix('Prefixed');
    }

    // --- Inheritance assertions ---

    #[Test]
    public function implementPasses(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/ImplementsStringable.php')
            ->implement('Stringable');
    }

    #[Test]
    public function implementFails(): void
    {
        $this->expectException(ArchViolationException::class);
        $this->expectExceptionMessageMatches('/does not implement/');

        Arch::assertThat(self::$fixtureDir . '/NonFinalClass.php')
            ->implement('Stringable');
    }

    #[Test]
    public function extendPasses(): void
    {
        $this->expectNotToPerformAssertions();

        // Use full directory scan so BaseClass is loaded before ExtendsBase
        Arch::assertThat(self::$fixtureDir);
        // Now test single file
        Arch::assertThat(self::$fixtureDir . '/ExtendsBase.php')
            ->extend(\SolidFrame\Archtest\Tests\Fixtures\BaseClass::class);
    }

    // --- Dependency assertions ---

    #[Test]
    public function doesNotDependOnPasses(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/NonFinalClass.php')
            ->doesNotDependOn('SolidFrame\Core');
    }

    #[Test]
    public function doesNotDependOnFails(): void
    {
        $this->expectException(ArchViolationException::class);
        $this->expectExceptionMessageMatches('/depends on/');

        Arch::assertThat(self::$fixtureDir . '/DependsOnExternal.php')
            ->doesNotDependOn('DateTimeImmutable');
    }

    // --- Chaining ---

    #[Test]
    public function chainsMultipleAssertions(): void
    {
        $this->expectNotToPerformAssertions();

        Arch::assertThat(self::$fixtureDir . '/FinalReadonlyClass.php')
            ->areFinal()
            ->areReadonly();
    }

    // --- Violation details ---

    #[Test]
    public function violationExceptionContainsDetails(): void
    {
        try {
            Arch::assertThat(self::$fixtureDir . '/NonFinalClass.php')
                ->areFinal();
            self::fail('Expected ArchViolationException');
        } catch (ArchViolationException $e) {
            self::assertNotEmpty($e->violations());
            self::assertStringContainsString('NonFinalClass', $e->violations()[0]);
        }
    }
}
