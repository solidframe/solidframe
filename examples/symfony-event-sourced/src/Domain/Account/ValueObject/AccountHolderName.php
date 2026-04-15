<?php

declare(strict_types=1);

namespace App\Domain\Account\ValueObject;

use InvalidArgumentException;

final readonly class AccountHolderName
{
    private function __construct(
        public string $value,
    ) {}

    public static function from(string $value): self
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Account holder name cannot be empty.');
        }

        return new self($trimmed);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
