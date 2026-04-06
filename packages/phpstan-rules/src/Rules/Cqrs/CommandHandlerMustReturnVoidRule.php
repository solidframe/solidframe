<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\Cqrs;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VoidType;

/** @implements Rule<InClassMethodNode> */
final readonly class CommandHandlerMustReturnVoidRule implements Rule
{
    public function __construct(
        private string $handlerInterface,
    ) {}

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();
        $methodName = $node->getMethodReflection()->getName();

        if (!in_array($methodName, ['__invoke', 'handle'], true)) {
            return [];
        }

        if (!$classReflection->implementsInterface($this->handlerInterface)) {
            return [];
        }

        $returnType = $node->getMethodReflection()->getVariants()[0]->getReturnType();

        if ($returnType instanceof VoidType) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Command handler "%s::%s" must return void.',
                $classReflection->getName(),
                $methodName,
            ))
                ->identifier('solidframe.commandHandlerMustReturnVoid')
                ->tip('Command handlers perform side effects and should not return values.')
                ->build(),
        ];
    }
}
