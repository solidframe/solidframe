<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures;

interface TestMessage {}

final readonly class ValidNoExtendMessage implements TestMessage {}

class BaseMessage implements TestMessage {}

final readonly class InvalidExtendingMessage extends BaseMessage {}
