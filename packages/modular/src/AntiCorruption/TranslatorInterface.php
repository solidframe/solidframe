<?php

declare(strict_types=1);

namespace SolidFrame\Modular\AntiCorruption;

/**
 * @template TSource of object
 * @template TTarget of object
 */
interface TranslatorInterface
{
    /**
     * @param TSource $source
     * @return TTarget
     */
    public function translate(object $source): object;
}
