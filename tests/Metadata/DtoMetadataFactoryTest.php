<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Metadata;

include_once 'DataProvider.php';

use PHPUnit\Framework\TestCase;
use Vologzhan\DoctrineDto\Metadata\DtoFactory\DtoMetadataFactory;

final class DtoMetadataFactoryTest extends TestCase
{
    public function test(): void
    {
        $this->assertEquals(
            DataProvider::DtoMetadata(),
            DtoMetadataFactory::create(UserForNotification::class),
        );
    }
}
