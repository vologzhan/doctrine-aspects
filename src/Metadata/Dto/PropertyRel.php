<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineAspects\Metadata\Dto;

class PropertyRel extends Property
{
    public bool $isArray;
    public AspectMetadata $aspectMetadata;

    public function __construct(string $name, bool $isArray, AspectMetadata $aspectMetadata)
    {
        parent::__construct($name);
        $this->isArray = $isArray;
        $this->aspectMetadata = $aspectMetadata;
    }
}
