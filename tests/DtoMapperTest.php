<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use Vologzhan\DoctrineDto\DtoMapper;
use Vologzhan\DoctrineDto\DtoMetadataFactory;
use Vologzhan\DoctrineDto\Tests\Dto\UserDto;
use Vologzhan\DoctrineDto\Tests\Entity\User;

final class DtoMapperTest extends TestCase
{
    private EntityManagerInterface $em;
    private DtoMapper $mapper;

    protected function setUp(): void
    {
        $reader = new AnnotationReader();
        $driver = new AnnotationDriver($reader, [__DIR__ . '../Entity']);

        $config = Setup::createConfiguration(true);
        $config->setMetadataDriverImpl($driver);

        $this->em = EntityManager::create(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            $config
        );

        $factory = new DtoMetadataFactory($this->em);
        $this->mapper = new DtoMapper($this->em, $factory);
    }

    public function testArray(): void
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->leftJoin('u.profile', 'p')
            ->leftJoin('u.city', 'c')
            ->leftJoin('c.news', 'n')
            ->andWhere('u.id = :id')
            ->setParameter('id', 1);

        $this->assertEquals(
            'FROM users u0_ LEFT JOIN profile p1_ ON u0_.id = p1_.user_id LEFT JOIN city c2_ ON u0_.city_id = c2_.id LEFT JOIN news n3_ ON c2_.id = n3_.city_id',
            $this->mapper->array(UserDto::class, $qb));
    }
}
