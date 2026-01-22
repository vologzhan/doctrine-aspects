<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Dto
{
    public string $entityClassName = '';
}
