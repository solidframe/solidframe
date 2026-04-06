<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\Ddd;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<InClassNode> */
final readonly class ValueObjectMustBeReadonlyRule implements Rule
{
    public function __construct(
        private string $valueObjectInterface,
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

        if (!$classReflection->implementsInterface($this->valueObjectInterface)) {
            return [];
        }

        if ($node->getOriginalNode()->isReadonly()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Value object "%s" must be declared readonly.',
                $classReflection->getName(),
            ))
                ->identifier('solidframe.valueObjectMustBeReadonly')
                ->tip('Value objects are immutable. Declare the class as readonly.')
                ->build(),
        ];
    }
}
