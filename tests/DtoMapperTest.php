<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
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
        $driver = new AnnotationDriver($reader, [__DIR__ . '/Entity']);

        $config = Setup::createConfiguration(true);
        $config->setMetadataDriverImpl($driver);

        $this->em = EntityManager::create(
            [
                'driver'   => 'pdo_pgsql',
                'host'     => 'doctrine-dto-db',
                'port'     => 5432,
                'user'     => 'doctrine-dto',
                'password' => 'doctrine-dto',
                'dbname'   => 'doctrine-dto',
            ],
            $config
        );

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $sqls = $schemaTool->getCreateSchemaSql($metadata);

        $conn = $this->em->getConnection();
        $conn->beginTransaction();
        $conn->executeStatement('CREATE SCHEMA IF NOT EXISTS public');
        foreach ($sqls as $sql) {
            $conn->executeStatement($sql);
        }

        $factory = new DtoMetadataFactory($this->em);
        $this->mapper = new DtoMapper($this->em, $factory);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
    }

    public function testSelectEmpty(): void
    {
        $qb = $this->em
            ->createQueryBuilder()
            ->select('user', 'profile', 'photos')
            ->from(User::class, 'user')
            ->leftJoin('user.profile', 'profile')
            ->leftJoin('profile.photos', 'photos');

        $this->assertEquals([], $this->mapper->array(UserDto::class, $qb));
    }

    public function testEmptyProfile(): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->em->getConnection()->executeStatement(
            "INSERT INTO users (id, balance, created_at, updated_at, deleted_at) VALUES (1, null, '$now', '$now', '$now')",
        );

        $qb = $this->em
            ->createQueryBuilder()
            ->select('user', 'profile', 'photos')
            ->from(User::class, 'user')
            ->leftJoin('user.profile', 'profile')
            ->leftJoin('profile.photos', 'photos');

        $user = new UserDto();
        $user->balance = null;
        $user->createdAt = new \DateTimeImmutable($now);
        $user->updatedAt = new \DateTimeImmutable($now);
        $user->deletedAt = new \DateTime($now);
        $user->profile = null;

        $this->assertEquals([$user], $this->mapper->array(UserDto::class, $qb));
    }
}
