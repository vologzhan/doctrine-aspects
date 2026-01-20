<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Dto;

final class CityDto
{
    public string $name;

    /** @var NewsDto[] */
    public array $news;
}
