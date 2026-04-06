<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\Cqrs\CommandHandlerMustReturnVoidRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<CommandHandlerMustReturnVoidRule> */
final class CommandHandlerMustReturnVoidRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new CommandHandlerMustReturnVoidRule(
            handlerInterface: \SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\TestCommandHandler::class,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/command-handler.php'], [
            [
                'Command handler "SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\InvalidCommandHandler::__invoke" must return void.',
                16,
                'Command handlers perform side effects and should not return values.',
            ],
        ]);
    }
}
