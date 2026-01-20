<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("news")
 */
class News
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column("title")
     */
    private string $title;

    /**
     * @ORM\Column("link")
     */
    private string $link;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="news")
     */
    private City $city;
}
