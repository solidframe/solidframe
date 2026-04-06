<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\Cqrs\HandlerMustBeInvokableRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<HandlerMustBeInvokableRule> */
final class HandlerMustBeInvokableRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new HandlerMustBeInvokableRule(
            handlerInterfaces: [\SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\TestHandler::class],
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/invokable-handler.php'], [
            [
                'Handler "SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\InvalidMultiMethodHandler" must have only one public method (__invoke), found additional public methods.',
                16,
            ],
            [
                'Handler "SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\InvalidNonInvokableHandler" must implement __invoke() method.',
                23,
            ],
        ]);
    }
}
