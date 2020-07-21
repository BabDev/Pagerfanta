<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
class Person
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    public $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    public $biography;
}
