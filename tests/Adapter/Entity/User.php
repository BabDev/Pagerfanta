<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Adapter\Entity;

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
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var Collection<array-key, Group>
     *
     * @ORM\ManyToMany(targetEntity="Pagerfanta\Tests\Adapter\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(
     *     name="user_groups",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function addGroup(Group $group): void
    {
        if (!$this->hasGroup($group)) {
            $this->groups->add($group);
        }
    }

    /**
     * @return Collection<array-key, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function hasGroup(Group $group): bool
    {
        return $this->groups->contains($group);
    }

    public function removeGroup(Group $group): void
    {
        if ($this->hasGroup($group)) {
            $this->groups->removeElement($group);
        }
    }
}
