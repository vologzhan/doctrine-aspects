<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("city")
 */
class City
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column("name")
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="city")
     */
    private ArrayCollection $users;

    /**
     * @ORM\OneToMany(targetEntity="News", mappedBy="city")
     */
    private ArrayCollection $news;
}
