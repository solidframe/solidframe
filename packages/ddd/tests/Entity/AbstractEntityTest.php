<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Tests\Entity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Identity\AbstractIdentity;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\Ddd\Entity\AbstractEntity;

final class AbstractEntityTest extends TestCase
{
    #[Test]
    public function returnsIdentity(): void
    {
        $id = $this->createIdentity('abc-123');
        $entity = $this->createEntity($id);

        self::assertSame($id, $entity->identity());
    }

    #[Test]
    public function equalsSameTypeAndIdentity(): void
    {
        $id = $this->createIdentity('abc-123');
        $entity1 = $this->createEntity($id);
        $entity2 = $this->createEntity($id);

        self::assertTrue($entity1->equals($entity2));
    }

    #[Test]
    public function doesNotEqualDifferentIdentity(): void
    {
        $entity1 = $this->createEntity($this->createIdentity('abc-123'));
        $entity2 = $this->createEntity($this->createIdentity('xyz-789'));

        self::assertFalse($entity1->equals($entity2));
    }

    private function createIdentity(string $value): IdentityInterface
    {
        return new class ($value) extends AbstractIdentity {};
    }

    private function createEntity(IdentityInterface $identity): AbstractEntity
    {
        return new class ($identity) extends AbstractEntity {};
    }
}
