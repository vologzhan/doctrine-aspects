<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto;

use Vologzhan\DoctrineDto\ColumnMetadata\ColumnMetadata;
use Vologzhan\DoctrineDto\DtoMetadata\DtoMetadata;

final class ColumnMetadataFactory
{
    /**
     * @param DtoMetadata[] $dtoMetadataList
     *
     * Клюли в возвращаемом массиве - название колонок в SELECT
     * @return array<string, ColumnMetadata>
     */
    public static function create(array $dtoMetadataList): array
    {
        /** @var ColumnMetadata[] $columns */
        $columns = [];

        foreach ($dtoMetadataList as $alias => $dtoMetadata) {
            // Для маппинга всегда добавляется id в запрос, todo оставить только для списков
            $columnMetadata = new ColumnMetadata();
            $columnMetadata->isPrimaryKey = true;
            $columnMetadata->dtoClassName = $dtoMetadata->className;
            $columnMetadata->parentClassName = $dtoMetadata->parentClass;
            $columnMetadata->parentPropertyName = $dtoMetadata->parentProperty;
            $columnMetadata->isArray = $dtoMetadata->isArray;

            $nameInQuery = sprintf('%s.%s', $alias, $dtoMetadata->doctrine->primaryKey);
            $columns[$nameInQuery] = $columnMetadata;

            foreach ($dtoMetadata->properties as $property) {
                $nameInQuery = sprintf('%s.%s', $alias, $property->doctrine->columnName);
                $columnMetadata = $columns[$nameInQuery] ?? null;
                if ($columnMetadata === null) {
                    $columnMetadata = new ColumnMetadata();
                    $columns[$nameInQuery] = $columnMetadata;
                }

                $columnMetadata->dtoPropertyName = $property->name;
                $columnMetadata->type = $property->type;
            }
        }

        return $columns;
    }
}
