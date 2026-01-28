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
     * @ORM\Column(nullable=true)
     */
    private ?float $balance;

    /**
     * @ORM\Column(name="created_at")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at")
     */
    private ?\DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(name="deleted_at")
     */
    private ?\DateTimeInterface $deletedAt;

    /**
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user")
     */
    private Profile $profile;
}
