<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Dto;

class Property
{
    public string $name;
    public string $columnName;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
