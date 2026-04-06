<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Tests\Analyzer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Analyzer\ClassFinder;
use SolidFrame\Archtest\Analyzer\ClassInfo;
use SolidFrame\Archtest\Tests\Fixtures\AbstractClass;
use SolidFrame\Archtest\Tests\Fixtures\FinalReadonlyClass;
use SolidFrame\Archtest\Tests\Fixtures\ImplementsStringable;
use SolidFrame\Archtest\Tests\Fixtures\NonFinalClass;
use SolidFrame\Archtest\Tests\Fixtures\SomeEnum;
use SolidFrame\Archtest\Tests\Fixtures\SomeInterface;

final class ClassInfoTest extends TestCase
{
    #[Test]
    public function detectsFinalReadonlyClass(): void
    {
        $info = ClassInfo::fromFqcn(FinalReadonlyClass::class);

        self::assertTrue($info->isFinal);
        self::assertTrue($info->isReadonly);
        self::assertFalse($info->isAbstract);
        self::assertFalse($info->isInterface);
        self::assertFalse($info->isEnum);
        self::assertSame('FinalReadonlyClass', $info->shortName);
    }

    #[Test]
    public function detectsNonFinalClass(): void
    {
        $info = ClassInfo::fromFqcn(NonFinalClass::class);

        self::assertFalse($info->isFinal);
        self::assertFalse($info->isReadonly);
    }

    #[Test]
    public function detectsAbstractClass(): void
    {
        $info = ClassInfo::fromFqcn(AbstractClass::class);

        self::assertTrue($info->isAbstract);
        self::assertFalse($info->isInterface);
    }

    #[Test]
    public function detectsInterface(): void
    {
        $info = ClassInfo::fromFqcn(SomeInterface::class);

        self::assertTrue($info->isInterface);
        self::assertFalse($info->isAbstract);
    }

    #[Test]
    public function detectsEnum(): void
    {
        $info = ClassInfo::fromFqcn(SomeEnum::class);

        self::assertTrue($info->isEnum);
    }

    #[Test]
    public function detectsParentClass(): void
    {
        // ExtendsBase requires BaseClass — load via ClassFinder which handles dependencies
        ClassFinder::inDirectory(__DIR__ . '/../Fixtures');

        $info = ClassInfo::fromFqcn(\SolidFrame\Archtest\Tests\Fixtures\ExtendsBase::class);

        self::assertSame(\SolidFrame\Archtest\Tests\Fixtures\BaseClass::class, $info->parentClass);
    }

    #[Test]
    public function detectsImplementedInterfaces(): void
    {
        $info = ClassInfo::fromFqcn(ImplementsStringable::class);

        self::assertContains('Stringable', $info->interfaces);
    }

    #[Test]
    public function extractsDependencies(): void
    {
        $info = ClassInfo::fromFqcn(\SolidFrame\Archtest\Tests\Fixtures\DependsOnExternal::class);

        self::assertContains('DateTimeImmutable', $info->dependencies);
    }
}
