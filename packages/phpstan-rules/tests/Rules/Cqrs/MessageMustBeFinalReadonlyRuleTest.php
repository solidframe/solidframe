<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\Cqrs\MessageMustBeFinalReadonlyRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<MessageMustBeFinalReadonlyRule> */
final class MessageMustBeFinalReadonlyRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new MessageMustBeFinalReadonlyRule(
            messageInterface: \SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\TestCommand::class,
            label: 'Command',
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/message-final-readonly.php'], [
            [
                'Command "SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\InvalidNonFinalCommand" must be declared final.',
                14,
            ],
            [
                'Command "SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\InvalidNonFinalCommand" must be declared readonly.',
                14,
            ],
        ]);
    }
}
