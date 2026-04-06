<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\Cqrs\MessageMustNotExtendRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<MessageMustNotExtendRule> */
final class MessageMustNotExtendRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new MessageMustNotExtendRule(
            messageInterfaces: [\SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\TestMessage::class],
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/message-extend.php'], [
            [
                'Message "SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\InvalidExtendingMessage" must not extend another class. Use composition instead of inheritance.',
                13,
            ],
        ]);
    }
}
