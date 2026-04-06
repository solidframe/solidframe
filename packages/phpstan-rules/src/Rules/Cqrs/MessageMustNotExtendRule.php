<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\Cqrs;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<InClassNode> */
final readonly class MessageMustNotExtendRule implements Rule
{
    /** @param list<string> $messageInterfaces */
    public function __construct(
        private array $messageInterfaces,
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

        $isMessage = false;

        foreach ($this->messageInterfaces as $interface) {
            if ($classReflection->implementsInterface($interface)) {
                $isMessage = true;

                break;
            }
        }

        if (!$isMessage) {
            return [];
        }

        $parent = $classReflection->getParentClass();

        if ($parent === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Message "%s" must not extend another class. Use composition instead of inheritance.',
                $classReflection->getName(),
            ))
                ->identifier('solidframe.messageMustNotExtend')
                ->build(),
        ];
    }
}
