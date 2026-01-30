<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto;

use Vologzhan\DoctrineDto\SqlMetadata\ColumnMetadata;

class DtoHydrator
{
    /**
     * @param ColumnMetadata[] $metadataColumns
     */
    public static function hydrate(array $metadataColumns, array $rows): array
    {
        $dtoList = [];

        foreach ($rows as $row) {
            $currentDtoList = [];
            $skip = false;
            $dto = null;

            foreach ($row as $i => $v) {
                $metadata = $metadataColumns[$i];

                if ($metadata->isPrimaryKey) {
                    $skip = $v === null;
                    if ($skip) {
                        $parent = $currentDtoList[$metadata->parentClassName] ?? null;
                        if ($parent) {
                            $parent->{$metadata->parentPropertyName} = $metadata->isArray ? [] : null;
                        }

                        continue;
                    }

                    $dto = $dtoList[$metadata->dtoClassName][$v] ?? null;
                    if ($dto === null) {
                        $dto = new $metadata->dtoClassName();

                        $dtoList[$metadata->dtoClassName][$v] = $dto;
                    }
                    $currentDtoList[$metadata->dtoClassName] = $dto;

                    if ($metadata->parentClassName) {
                        $parent = $currentDtoList[$metadata->parentClassName];

                        if ($metadata->isArray) {
                            $parent->{$metadata->parentPropertyName}[] = $dto;
                        } else {
                            $parent->{$metadata->parentPropertyName} = $dto;
                        }
                    }
                }

                if ($skip || $metadata->dtoPropertyName === null) {
                    continue;
                }

                $type = $metadata->type;
                if ($v === null) {
                    // nothing
                } elseif ($type === \DateTimeInterface::class || $type === \DateTimeImmutable::class) {
                    $v = new \DateTimeImmutable($v);
                } elseif ($type === \DateTime::class) {
                    $v = new \DateTime($v);
                } elseif ($type === 'float') {
                    $v = (float)$v;
                }

                $dto->{$metadata->dtoPropertyName} = $v;
            }
        }

        $dtoClassName = $metadataColumns[0]->dtoClassName;

        return array_values($dtoList[$dtoClassName] ?? []);
    }
}
