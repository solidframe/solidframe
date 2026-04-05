<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Tests\Module;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Modular\Module\AbstractModule;

final class AbstractModuleTest extends TestCase
{
    #[Test]
    public function returnsModuleName(): void
    {
        $module = new class ('billing') extends AbstractModule {};

        self::assertSame('billing', $module->name());
    }

    #[Test]
    public function returnsEmptyDependenciesByDefault(): void
    {
        $module = new class ('billing') extends AbstractModule {};

        self::assertSame([], $module->dependsOn());
    }

    #[Test]
    public function returnsDeclaredDependencies(): void
    {
        $module = new class ('billing', ['core', 'payment']) extends AbstractModule {};

        self::assertSame(['core', 'payment'], $module->dependsOn());
    }
}
