<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\Cqrs;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<InClassNode> */
final readonly class HandlerMustBeInvokableRule implements Rule
{
    /** @param list<string> $handlerInterfaces */
    public function __construct(
        private array $handlerInterfaces,
    ) {}

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if ($classReflection->isAnonymous() || $classReflection->isAbstract()) {
            return [];
        }

        $isHandler = false;

        foreach ($this->handlerInterfaces as $interface) {
            if ($classReflection->implementsInterface($interface)) {
                $isHandler = true;

                break;
            }
        }

        if (!$isHandler) {
            return [];
        }

        if (!$classReflection->hasNativeMethod('__invoke')) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Handler "%s" must implement __invoke() method.',
                    $classReflection->getName(),
                ))
                    ->identifier('solidframe.handlerMustBeInvokable')
                    ->build(),
            ];
        }

        $publicMethods = array_filter(
            $node->getOriginalNode()->getMethods(),
            static fn(Node\Stmt\ClassMethod $m): bool => $m->isPublic()
                && !in_array($m->name->toString(), ['__construct', '__invoke'], true),
        );

        if ($publicMethods !== []) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Handler "%s" must have only one public method (__invoke), found additional public methods.',
                    $classReflection->getName(),
                ))
                    ->identifier('solidframe.handlerSinglePublicMethod')
                    ->build(),
            ];
        }

        return [];
    }
}
