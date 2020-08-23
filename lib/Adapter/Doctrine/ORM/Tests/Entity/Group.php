<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="groups")
 */
class Group
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
     * @var User[]|null
     *
     * @ORM\ManyToMany(targetEntity="\Pagerfanta\Doctrine\ORM\Tests\Entity\User", mappedBy="groups")
     */
    public $users;
}
