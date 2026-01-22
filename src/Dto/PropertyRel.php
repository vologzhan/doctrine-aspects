<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Dto;

class PropertyRel
{
    public string $foreignColumn;
    public bool $isArray;

    public DtoMetadata $dtoMetadata;
    public Property $property;

    public function __construct(string $name, bool $isArray, DtoMetadata $dtoMetadata)
    {
        $this->property = new Property($name);
        $this->isArray = $isArray;
        $this->dtoMetadata = $dtoMetadata;
    }
}
