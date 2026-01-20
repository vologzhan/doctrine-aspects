<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("users")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user")
     */
    private Profile $profile;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="users")
     */
    private City $city;
}
