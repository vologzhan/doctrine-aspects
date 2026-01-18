<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineAspects\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Vologzhan\DoctrineAspects\Annotation\Aspect;
use Vologzhan\DoctrineAspects\Metadata\AspectMetadataFactory;
use Vologzhan\DoctrineAspects\Metadata\Dto\AspectMetadata;
use Vologzhan\DoctrineAspects\Metadata\Dto\Property;
use Vologzhan\DoctrineAspects\Metadata\Dto\PropertyRel;

final class AspectMetadataFactoryTest extends TestCase
{
    public function test(): void
    {
        $metadata = AspectMetadataFactory::parse(UserForNotification::class);

        $this->assertEquals(
            new AspectMetadata(UserForNotification::class, [
                new PropertyRel('profile', false, new AspectMetadata(ProfileForNotification::class, [
                    new Property('firstName'),
                    new Property('secondName'),
                    new Property('email'),
                ])),
                new PropertyRel('city', false, new AspectMetadata(CityForNotification::class, [
                    new Property('name'),
                    new PropertyRel('news', true, new AspectMetadata(NewsForNotification::class, [
                        new Property('title'),
                        new Property('link'),
                    ]))
                ]))
            ]),
            $metadata,
        );
    }
}

/**
 * @Aspect(\App\Entity\User::class)
 */
class UserForNotification
{
    public ProfileForNotification $profile;
    public CityForNotification $city;
}

class ProfileForNotification
{
    public string $firstName;
    public string $secondName;
    public string $email;
}

class CityForNotification
{
    public string $name;

    /** @var NewsForNotification[] */
    public array $news;
}

class NewsForNotification
{
    public string $title;
    public string $link;
}
