<?php

declare(strict_types=1);

namespace SolidFrame\Core\Identity;

use SolidFrame\Core\Exception\InvalidArgumentException;

class UuidIdentity extends AbstractIdentity
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    final public function __construct(string $id)
    {
        (preg_match(self::UUID_PATTERN, $id) === 1) or throw InvalidArgumentException::invalidUuid($id);

        parent::__construct($id);
    }

    public static function generate(): static
    {
        return new static(self::createUuidV4());
    }

    private static function createUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(substr($bytes, 0, 4)),
            bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)),
            bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6)),
        );
    }
}
