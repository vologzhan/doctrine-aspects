<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineAspects;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class AspectMapper
{
    /**
     * @template T
     * @param class-string<T> $aspectClassName
     * @param EntityManagerInterface|QueryBuilder $doctrine
     * @return T
     */
    public static function one(string $aspectClassName, $doctrine, string $sql = '', array $params = [])
    {
        // todo
    }

    /**
     * @template T
     * @param class-string<T> $aspectClassName
     * @param EntityManagerInterface|QueryBuilder $doctrine
     * @return T|null
     */
    public static function oneOrNull(string $aspectClassName, $doctrine, string $sql = '', array $params = [])
    {
        // todo
    }

    /**
     * @template T
     * @param class-string<T> $aspectClassName
     * @param EntityManagerInterface|QueryBuilder $doctrine
     * @return T[]
     */
    public static function array(string $aspectClassName, $doctrine, string $sql = '', array $params = []): array
    {
        // todo
    }
}
