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
use Vologzhan\DoctrineDto\Tests\Dto\CityDto;
use Vologzhan\DoctrineDto\Tests\Dto\NewsDto;
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
            [],
            [
                new DtoMetadata(ProfileDto::class, false, UserDto::class, 'profile',
                    [
                        new Property('firstName', null, new PropertyDoctrine('first_name')),
                        new Property('secondName', null, new PropertyDoctrine('second_name')),
                        new Property('email', null, new PropertyDoctrine('email')),
                    ],
                    [],
                    new DtoDoctrine('profile', 'id'),
                ),
                new DtoMetadata(CityDto::class, false, UserDto::class, 'city',
                    [
                        new Property('name', null, new PropertyDoctrine('name')),
                    ],
                    [
                        new DtoMetadata(NewsDto::class, true, CityDto::class, 'news',
                            [
                                new Property('title', null, new PropertyDoctrine('title')),
                                new Property('link', null, new PropertyDoctrine('link')),
                            ],
                            [],
                            new DtoDoctrine('news', 'id'),
                        ),
                    ],
                    new DtoDoctrine('city', 'id'),
                ),
            ],
            new DtoDoctrine('users', 'id')
        );

        $actual = $this->metadataFactory->create(UserDto::class);

        $this->assertEquals($expected, $actual);
    }
}
