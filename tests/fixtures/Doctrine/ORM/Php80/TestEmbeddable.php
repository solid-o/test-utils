<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\fixtures\Doctrine\ORM\Php80;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class TestEmbeddable
{
    /**
     * @ORM\Column(type="integer")
     */
    public ?int $id;

    /**
     * @ORM\Column()
     */
    public string $field42Name;
}
