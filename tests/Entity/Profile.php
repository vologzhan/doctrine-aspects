<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("profile")
 */
class Profile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column
     */
    private string $nickname;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="profile")
     */
    private User $user;

    /**
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="profile")
     */
    private Collection $photos;
}
