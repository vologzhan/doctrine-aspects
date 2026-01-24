<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPSQLParser\PHPSQLParser;
use Vologzhan\DoctrineDto\DtoMetadata\DtoMetadata;
use Vologzhan\DoctrineDto\Exception\DoctrineDtoException;

class DtoMapper
{
    private EntityManagerInterface $entityManager;
    private DtoMetadataFactory $dtoMetadataFactory;

    public function __construct(EntityManagerInterface $entityManager, DtoMetadataFactory $dtoMetadataFactory)
    {
        $this->entityManager = $entityManager;
        $this->dtoMetadataFactory = $dtoMetadataFactory;
    }

    /**
     * @template T
     * @param class-string<T> $dtoClassName
     * @param QueryBuilder $queryBuilder
     * @return T[]
     */
    public function array(string $dtoClassName, QueryBuilder $queryBuilder): array
    {
        $sql = $queryBuilder->getQuery()->getSQL();
        if ($sql === '') {
            throw new DoctrineDtoException('empty sql');
        }

        $dtoMetadata = $this->dtoMetadataFactory->create($dtoClassName);

        $from = $this->eraseSelectStatement($sql);
        $joinMetadataMap = $this->arrangeMetadataByJoins($dtoMetadata, $from);
        $metadataColumns = ColumnMetadataFactory::create($joinMetadataMap);
        $fullSql = sprintf("SELECT %s %s", implode(', ', array_keys($metadataColumns)), $from);

        $paramValues = [];
        $paramTypes = [];
        foreach ($queryBuilder->getQuery()->getParameters() as $param) {
            $paramValues[] = $param->getValue();
            $paramTypes[] = $param->getType();
        }

        $rows = $this->entityManager->getConnection()->fetchAllNumeric($fullSql, $paramValues, $paramTypes);

        return DtoHydrator::hydrate($rows, array_values($metadataColumns));
    }

    private function eraseSelectStatement(string $sql): string
    {
        return preg_replace('/SELECT\s+(.+?)\s+FROM/i', 'FROM', $sql);
    }

    /**
     * @throws DoctrineDtoException
     */
    private function arrangeMetadataByJoins(DtoMetadata $metadata, string $sql): array
    {
        $parser = new PHPSQLParser();
        $ast = $parser->parse($sql);

        /** @var DtoMetadata[] $dtoMetadataMap */
        $dtoMetadataMap = [];
        foreach ($ast['FROM'] as $i => $from) {
            $table = $from['table'];
            $alias = $from['alias']['name'] ?? null;
            $name = $alias ?: $table;

            if ($i === 0) {
                if ($table !== $metadata->doctrine->tableName) {
                    throw new DoctrineDtoException("Запрос должен начинаться с 'FROM {$metadata->doctrine->tableName}'");
                }
                $dtoMetadataMap[$name] = $metadata;

                continue; // основная таблица
            }

            $rel1 = $from['ref_clause'][0]['no_quotes']['parts'][0];
            $rel2 = $from['ref_clause'][2]['no_quotes']['parts'][0];

            $currentMeta = null;
            $currentRelation = null;
            if (array_key_exists($rel1, $dtoMetadataMap)) {
                $currentMeta = $dtoMetadataMap[$rel1];
                $currentRelation = $rel2;
            } else {
                $currentMeta = $dtoMetadataMap[$rel2] ?? null;
                $currentRelation = $rel1;
            }
            if ($currentMeta === null) {
                throw new DoctrineDtoException("JOIN parse error"); // todo добавить больше инфы в ошибку
            }

            $nextMeta = null;
            foreach ($currentMeta->relations as $rel) {
                $tableName = $rel->doctrine->tableName;
                if ($tableName === $table || sprintf("public.$tableName", ) === $table) { // todo schema from Doctrine
                    $nextMeta = $rel;
                    break;
                }
            }
            if ($nextMeta === null) {
                throw new DoctrineDtoException("Metadata for JOIN not found"); // todo добавить больше инфы в ошибку
            }

            $dtoMetadataMap[$currentRelation] = $nextMeta;
        }

        return $dtoMetadataMap;
    }
}
