<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("profile_photo")
 */
final class Photo
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private string $link;

    /**
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="photos")
     */
    private Profile $profile;
}
