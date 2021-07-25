<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
class Person
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $name = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $biography = null;
}
