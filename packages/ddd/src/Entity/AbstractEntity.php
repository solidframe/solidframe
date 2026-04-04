<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Entity;

use SolidFrame\Core\Identity\IdentityInterface;

abstract class AbstractEntity implements EntityInterface
{
    public function __construct(
        private readonly IdentityInterface $identity,
    ) {}

    public function identity(): IdentityInterface
    {
        return $this->identity;
    }

    public function equals(EntityInterface $other): bool
    {
        return $other instanceof static && $this->identity->equals($other->identity());
    }
}
