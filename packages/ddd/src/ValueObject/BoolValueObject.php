<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\ValueObject;

abstract readonly class BoolValueObject implements ValueObjectInterface
{
    final protected function __construct(
        private bool $value,
    ) {}

    public static function from(bool $value): static
    {
        return new static($value);
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof static && $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }
}
