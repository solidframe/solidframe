<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Rules\Cqrs;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<InClassNode> */
final readonly class MessageMustBeFinalReadonlyRule implements Rule
{
    public function __construct(
        private string $messageInterface,
        private string $label,
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

        if (!$classReflection->implementsInterface($this->messageInterface)) {
            return [];
        }

        $errors = [];
        $original = $node->getOriginalNode();

        if (!$original->isFinal()) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s "%s" must be declared final.',
                $this->label,
                $classReflection->getName(),
            ))
                ->identifier(sprintf('solidframe.%sMustBeFinal', lcfirst($this->label)))
                ->build();
        }

        if (!$original->isReadonly()) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s "%s" must be declared readonly.',
                $this->label,
                $classReflection->getName(),
            ))
                ->identifier(sprintf('solidframe.%sMustBeReadonly', lcfirst($this->label)))
                ->build();
        }

        return $errors;
    }
}
