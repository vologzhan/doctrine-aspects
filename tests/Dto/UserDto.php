<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Dto;

use Vologzhan\DoctrineDto\Annotation\Dto;

/**
 * @Dto(\Vologzhan\DoctrineDto\Tests\Entity\User::class)
 */
class UserDto
{
    public ProfileDto $profile;
    public CityDto $city;
}
