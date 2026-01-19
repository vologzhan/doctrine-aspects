<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Metadata\DtoFactory\Dto;

class DtoMetadata
{
    public string $className;

    /** @var Property[]|PropertyRel[] */
    public array $properties;

    public function __construct(string $className, array $properties)
    {
        $this->className = $className;
        $this->properties = $properties;
    }
}
