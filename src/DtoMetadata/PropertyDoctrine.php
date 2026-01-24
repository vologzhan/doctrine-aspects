<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\DtoMetadata;

final class PropertyDoctrine
{
    public string $columnName;

    public function __construct(string $columnName)
    {
        $this->columnName = $columnName;
    }
}
