<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

class ArchViolationException extends RuntimeException implements SolidFrameException
{
    /** @param list<string> $violations */
    public function __construct(
        string $message,
        private readonly array $violations = [],
    ) {
        parent::__construct($message);
    }

    /** @param list<string> $violations */
    public static function forViolations(array $violations): self
    {
        return new self(
            sprintf("Architecture violation(s) found:\n  - %s", implode("\n  - ", $violations)),
            $violations,
        );
    }

    /** @return list<string> */
    public function violations(): array
    {
        return $this->violations;
    }
}
