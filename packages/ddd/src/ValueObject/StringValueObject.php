<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\ValueObject;

abstract readonly class StringValueObject implements ValueObjectInterface
{
    final protected function __construct(
        private string $value,
    ) {}

    public static function from(string $value): static
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof static && $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
