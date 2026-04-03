<?php

declare(strict_types=1);

namespace SolidFrame\Core\Identity;

use SolidFrame\Core\Exception\InvalidArgumentException;

abstract class AbstractIdentity implements IdentityInterface
{
    public function __construct(
        private readonly string $id,
    ) {
        ($id !== '') or throw InvalidArgumentException::emptyIdentity(static::class);
    }

    public function value(): string
    {
        return $this->id;
    }

    public function equals(IdentityInterface $other): bool
    {
        return $other instanceof static && $this->id === $other->value();
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
