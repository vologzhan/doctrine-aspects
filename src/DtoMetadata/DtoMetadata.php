<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\DtoMetadata;

class DtoMetadata
{
    public string $className;
    public bool $isArray;

    public ?string $parentClass;
    public ?string $parentProperty;

    public DtoDoctrine $doctrine;

    /** @var Property[] */
    public array $properties;

    /** @var DtoMetadata[] */
    public array $relations;

    public function __construct(
        string $className,
        bool $isArray,
        ?string $parentClass,
        ?string $parentProperty,
        array $properties,
        array $relations,
        ?dtoDoctrine $doctrine = null
    ) {
        $this->className = $className;
        $this->isArray = $isArray;
        $this->parentClass = $parentClass;
        $this->parentProperty = $parentProperty;
        $this->properties = $properties;
        $this->relations = $relations;

        if ($doctrine !== null) {
            $this->doctrine = $doctrine;
        }
    }
}
