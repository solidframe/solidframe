<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\EventSourcing;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<InClassMethodNode> */
final readonly class ApplyMethodMustExistRule implements Rule
{
    public function __construct(
        private string $aggregateRootClass,
    ) {}

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if (!$classReflection->isSubclassOf($this->aggregateRootClass)) {
            return [];
        }

        $methodNode = $node->getOriginalNode();
        $stmts = $methodNode->stmts;

        if ($stmts === null) {
            return [];
        }

        $nodeFinder = new NodeFinder();

        /** @var list<MethodCall> $recordThatCalls */
        $recordThatCalls = $nodeFinder->find($stmts, static fn(Node $n): bool => $n instanceof MethodCall
            && $n->name instanceof Node\Identifier
            && $n->name->toString() === 'recordThat');

        $errors = [];

        foreach ($recordThatCalls as $call) {
            if (!isset($call->args[0])) {
                continue;
            }

            $arg = $call->args[0];

            if (!$arg instanceof Node\Arg) {
                continue;
            }

            $value = $arg->value;

            if (!$value instanceof New_ || !$value->class instanceof Name) {
                continue;
            }

            $eventShortName = $value->class->getLast();
            $applyMethod = 'apply' . $eventShortName;

            if (!$classReflection->hasNativeMethod($applyMethod)) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Aggregate "%s" records "%s" but is missing method "%s()".',
                    $classReflection->getName(),
                    $eventShortName,
                    $applyMethod,
                ))
                    ->identifier('solidframe.applyMethodMustExist')
                    ->line($call->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }
}
