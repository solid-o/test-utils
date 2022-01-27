<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\fixtures\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TestEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     */
    public ?int $id;

    /**
     * @ORM\Column()
     */
    public string $field42Name;
}
