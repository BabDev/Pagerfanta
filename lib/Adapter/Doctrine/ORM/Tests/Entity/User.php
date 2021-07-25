<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    public ?int $id = null;

    /**
     * @var Collection<array-key, Group>
     *
     * @ORM\ManyToMany(targetEntity="\Pagerfanta\Doctrine\ORM\Tests\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(
     *     name="user_groups",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    public Collection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }
}
