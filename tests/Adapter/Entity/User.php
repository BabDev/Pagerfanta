<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\ManyToMany(targetEntity="\Pagerfanta\Tests\Adapter\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(
     *     name="user_groups",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    public $groups;
}
