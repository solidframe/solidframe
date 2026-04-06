<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\EventSourcing;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\EventSourcing\EventMustBeFinalReadonlyRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<EventMustBeFinalReadonlyRule> */
final class EventMustBeFinalReadonlyRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new EventMustBeFinalReadonlyRule(
            eventInterface: \SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures\TestDomainEvent::class,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/event-final-readonly.php'], [
            [
                'Domain event "SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures\InvalidNonFinalEvent" must be declared final.',
                14,
            ],
            [
                'Domain event "SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures\InvalidNonFinalEvent" must be declared readonly.',
                14,
                'Events are immutable data structures. Declare the class as readonly.',
            ],
        ]);
    }
}
