<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\Ddd;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<New_> */
final readonly class NoDirectAggregateConstructionRule implements Rule
{
    public function __construct(
        private string $aggregateRootClass,
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function getNodeType(): string
    {
        return New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Name) {
            return [];
        }

        $className = $scope->resolveName($node->class);

        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$classReflection->isSubclassOf($this->aggregateRootClass)) {
            return [];
        }

        // Allow construction inside the aggregate itself
        if ($scope->isInClass() && $scope->getClassReflection() !== null) {
            $currentClass = $scope->getClassReflection()->getName();

            if ($currentClass === $className || $scope->getClassReflection()->isSubclassOf($className)) {
                return [];
            }
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Aggregate root "%s" must not be constructed directly. Use a named constructor or factory method.',
                $className,
            ))
                ->identifier('solidframe.noDirectAggregateConstruction')
                ->tip('Use a static factory method like MyAggregate::create() instead of new MyAggregate().')
                ->build(),
        ];
    }
}
