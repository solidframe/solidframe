<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Preset;

use SolidFrame\Archtest\Analyzer\ClassFinder;
use SolidFrame\Archtest\Analyzer\ClassInfo;

final readonly class EventDrivenPreset implements PresetInterface
{
    private const string DOMAIN_EVENT_INTERFACE = \SolidFrame\Core\Event\DomainEventInterface::class;

    public function __construct(
        private string $eventDir,
    ) {}

    public function evaluate(): array
    {
        $violations = [];

        foreach (ClassFinder::inDirectory($this->eventDir) as $fqcn) {
            $info = ClassInfo::fromFqcn($fqcn);

            if ($info->isInterface) {
                continue;
            }

            if (!$info->isFinal) {
                $violations[] = sprintf('[EventDriven] Event %s is not final', $info->fqcn);
            }

            if (!$info->isReadonly) {
                $violations[] = sprintf('[EventDriven] Event %s is not readonly', $info->fqcn);
            }

            if (!in_array(self::DOMAIN_EVENT_INTERFACE, $info->interfaces, true)) {
                $violations[] = sprintf('[EventDriven] Event %s does not implement DomainEventInterface', $info->fqcn);
            }
        }

        return $violations;
    }
}
