<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineAspects\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Aspect
{
    public string $entityClassName = '';
}
