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
use Vologzhan\DoctrineDto\Tests\Dto\PhotoDto;
use Vologzhan\DoctrineDto\Tests\Dto\ProfileDto;
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
                'driver' => 'pdo_pgsql',
                'host' => 'doctrine-dto-db',
                'port' => 5432,
                'user' => 'doctrine-dto',
                'password' => 'doctrine-dto',
                'dbname' => 'doctrine-dto',
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

    public function testEmpty(): void
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

    public function testSeveralPhotos(): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $conn = $this->em->getConnection();
        $conn->executeStatement(<<<SQL
        INSERT INTO users (id, balance, created_at, updated_at, deleted_at) VALUES
                          (1,  123.321, '$now',     null,       null),
                          (2,  null,    '$now',     null,       null),
                          (3,  null,    '$now',     null,       null)
        SQL
        );
        $conn->executeStatement(<<<SQL
        INSERT INTO profile (id, user_id, nickname) VALUES
                            (1,  1,       'foo'),
                            (2,  2,       'bar'),
                            (3,  3,       'baz')
        SQL
        );
        $conn->executeStatement(<<<SQL
        INSERT INTO profile_photo (id, profile_id, link) VALUES
                                  (1,  2,          'http://'),
                                  (2,  2,          'https://'),
                                  (3,  3,          'link'),
                                  (4,  3,          'yet another')
        SQL
        );

        $qb = $this->em
            ->createQueryBuilder()
            ->select('user', 'profile', 'photos')
            ->from(User::class, 'user')
            ->leftJoin('user.profile', 'profile')
            ->leftJoin('profile.photos', 'photos')
            ->addOrderBy('user.id')
            ->addOrderBy('photos.id');

        $user1 = new UserDto();
        $user1->balance = 123.321;
        $user1->createdAt = new \DateTimeImmutable($now);
        $user1->updatedAt = null;
        $user1->deletedAt = null;
        $profile1 = new ProfileDto();
        $profile1->nickname = 'foo';
        $profile1->photos = [];
        $user1->profile = $profile1;

        $user2 = new UserDto();
        $user2->balance = null;
        $user2->createdAt = new \DateTimeImmutable($now);
        $user2->updatedAt = null;
        $user2->deletedAt = null;
        $profile2 = new ProfileDto();
        $profile2->nickname = 'bar';
        $user2->profile = $profile2;
        $photo1 = new PhotoDto();
        $photo1->link = 'http://';
        $photo2 = new PhotoDto();
        $photo2->link = 'https://';
        $profile2->photos = [$photo1, $photo2];

        $user3 = new UserDto();
        $user3->balance = null;
        $user3->createdAt = new \DateTimeImmutable($now);
        $user3->updatedAt = null;
        $user3->deletedAt = null;
        $profile3 = new ProfileDto();
        $profile3->nickname = 'baz';
        $user3->profile = $profile3;
        $photo3 = new PhotoDto();
        $photo3->link = 'link';
        $photo4 = new PhotoDto();
        $photo4->link = 'yet another';
        $profile3->photos = [$photo3, $photo4];

        $this->assertEquals([$user1, $user2, $user3], $this->mapper->array(UserDto::class, $qb));
    }
}
