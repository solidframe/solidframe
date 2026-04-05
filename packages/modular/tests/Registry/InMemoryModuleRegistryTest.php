<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Tests\Registry;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Modular\Exception\CircularDependencyException;
use SolidFrame\Modular\Exception\ModuleNotFoundException;
use SolidFrame\Modular\Module\AbstractModule;
use SolidFrame\Modular\Module\ModuleInterface;
use SolidFrame\Modular\Registry\InMemoryModuleRegistry;

final class InMemoryModuleRegistryTest extends TestCase
{
    #[Test]
    public function registersAndRetrievesModule(): void
    {
        $registry = new InMemoryModuleRegistry();
        $module = $this->createModule('billing');

        $registry->register($module);

        self::assertSame($module, $registry->get('billing'));
    }

    #[Test]
    public function throwsWhenModuleNotFound(): void
    {
        $registry = new InMemoryModuleRegistry();

        $this->expectException(ModuleNotFoundException::class);
        $registry->get('unknown');
    }

    #[Test]
    public function checksIfModuleExists(): void
    {
        $registry = new InMemoryModuleRegistry();
        $registry->register($this->createModule('billing'));

        self::assertTrue($registry->has('billing'));
        self::assertFalse($registry->has('unknown'));
    }

    #[Test]
    public function returnsAllModules(): void
    {
        $registry = new InMemoryModuleRegistry();
        $registry->register($this->createModule('billing'));
        $registry->register($this->createModule('payment'));

        self::assertCount(2, $registry->all());
    }

    #[Test]
    public function overwritesModuleWithSameName(): void
    {
        $registry = new InMemoryModuleRegistry();
        $first = $this->createModule('billing');
        $second = $this->createModule('billing');

        $registry->register($first);
        $registry->register($second);

        self::assertSame($second, $registry->get('billing'));
        self::assertCount(1, $registry->all());
    }

    #[Test]
    public function returnsDependencyOrderForIndependentModules(): void
    {
        $registry = new InMemoryModuleRegistry();
        $registry->register($this->createModule('billing'));
        $registry->register($this->createModule('payment'));

        $ordered = $registry->dependencyOrder();

        self::assertCount(2, $ordered);
    }

    #[Test]
    public function returnsDependencyOrderWithDependencies(): void
    {
        $registry = new InMemoryModuleRegistry();
        $registry->register($this->createModule('order', ['billing', 'payment']));
        $registry->register($this->createModule('billing'));
        $registry->register($this->createModule('payment', ['billing']));

        $ordered = $registry->dependencyOrder();
        $names = array_map(fn(ModuleInterface $m): string => $m->name(), $ordered);

        // billing must come before payment and order
        self::assertLessThan(
            array_search('payment', $names, true),
            array_search('billing', $names, true),
        );

        // payment must come before order
        self::assertLessThan(
            array_search('order', $names, true),
            array_search('payment', $names, true),
        );
    }

    #[Test]
    public function throwsOnCircularDependency(): void
    {
        $registry = new InMemoryModuleRegistry();
        $registry->register($this->createModule('a', ['b']));
        $registry->register($this->createModule('b', ['a']));

        $this->expectException(CircularDependencyException::class);
        $registry->dependencyOrder();
    }

    #[Test]
    public function throwsOnTransitiveCircularDependency(): void
    {
        $registry = new InMemoryModuleRegistry();
        $registry->register($this->createModule('a', ['b']));
        $registry->register($this->createModule('b', ['c']));
        $registry->register($this->createModule('c', ['a']));

        $this->expectException(CircularDependencyException::class);
        $registry->dependencyOrder();
    }

    #[Test]
    public function handlesEmptyRegistry(): void
    {
        $registry = new InMemoryModuleRegistry();

        self::assertSame([], $registry->all());
        self::assertSame([], $registry->dependencyOrder());
    }

    private function createModule(string $name, array $dependsOn = []): ModuleInterface
    {
        return new class ($name, $dependsOn) extends AbstractModule {};
    }
}
