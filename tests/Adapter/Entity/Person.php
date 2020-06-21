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
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     */
    public $name;

    /**
     * @ORM\Column(type="string")
     */
    public $biography;
}
