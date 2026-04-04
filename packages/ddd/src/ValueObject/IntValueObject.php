<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\ValueObject;

abstract readonly class IntValueObject implements ValueObjectInterface
{
    final protected function __construct(
        private int $value,
    ) {}

    public static function from(int $value): static
    {
        return new static($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof static && $this->value === $other->value();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
