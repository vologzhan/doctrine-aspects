<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Entity;

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
     * @ORM\Column("first_name")
     */
    private string $firstName;

    /**
     * @ORM\Column("second_name")
     */
    private string $secondName;

    /**
     * @ORM\Column("email")
     */
    private string $email;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="profile")
     */
    private User $user;
}
