<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use Vologzhan\DoctrineDto\DtoMetadata\DtoDoctrine;
use Vologzhan\DoctrineDto\DtoMetadata\DtoMetadata;
use Vologzhan\DoctrineDto\DtoMetadata\Property;
use Vologzhan\DoctrineDto\DtoMetadata\PropertyDoctrine;
use Vologzhan\DoctrineDto\DtoMetadataFactory;
use Vologzhan\DoctrineDto\Tests\Dto\PhotoDto;
use Vologzhan\DoctrineDto\Tests\Dto\ProfileDto;
use Vologzhan\DoctrineDto\Tests\Dto\UserDto;

final class MetadataFactoryTest extends TestCase
{
    private DtoMetadataFactory $metadataFactory;

    protected function setUp(): void
    {
        $reader = new AnnotationReader();
        $driver = new AnnotationDriver($reader, [__DIR__ . '../Entity']);

        $config = Setup::createConfiguration(true);
        $config->setMetadataDriverImpl($driver);

        $em = EntityManager::create(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            $config
        );

        $this->metadataFactory = new DtoMetadataFactory($em);
    }

    public function testCreate(): void
    {
        $expected = new DtoMetadata(UserDto::class, false, null, null,
            [
                new Property('balance', 'float', new PropertyDoctrine('balance')),
                new Property('createdAt', 'DateTimeInterface', new PropertyDoctrine('created_at')),
                new Property('updatedAt', 'DateTimeImmutable', new PropertyDoctrine('updated_at')),
                new Property('deletedAt', 'DateTime', new PropertyDoctrine('deleted_at')),
            ],
            [
                new DtoMetadata(ProfileDto::class, false, UserDto::class, 'profile',
                    [
                        new Property('nickname', 'string', new PropertyDoctrine('nickname')),
                    ],
                    [
                        new DtoMetadata(PhotoDto::class, true, ProfileDto::class, 'photos',
                            [
                                new Property('link', 'string', new PropertyDoctrine('link')),
                            ],
                            [],
                            new DtoDoctrine('profile_photo', 'id')
                        )
                    ],
                    new DtoDoctrine('profile', 'id'),
                ),
            ],
            new DtoDoctrine('users', 'id')
        );

        $actual = $this->metadataFactory->create(UserDto::class);

        $this->assertEquals($expected, $actual);
    }
}
