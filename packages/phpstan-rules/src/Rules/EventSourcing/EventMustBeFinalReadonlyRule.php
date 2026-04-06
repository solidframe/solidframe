<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\EventSourcing;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<InClassNode> */
final readonly class EventMustBeFinalReadonlyRule implements Rule
{
    public function __construct(
        private string $eventInterface,
    ) {}

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if ($classReflection->isAnonymous() || $classReflection->isInterface()) {
            return [];
        }

        if (!$classReflection->implementsInterface($this->eventInterface)) {
            return [];
        }

        $errors = [];
        $original = $node->getOriginalNode();

        if (!$original->isFinal()) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Domain event "%s" must be declared final.',
                $classReflection->getName(),
            ))
                ->identifier('solidframe.eventMustBeFinal')
                ->build();
        }

        if (!$original->isReadonly()) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Domain event "%s" must be declared readonly.',
                $classReflection->getName(),
            ))
                ->identifier('solidframe.eventMustBeReadonly')
                ->tip('Events are immutable data structures. Declare the class as readonly.')
                ->build();
        }

        return $errors;
    }
}
