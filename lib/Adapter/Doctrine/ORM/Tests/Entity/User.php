<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
#[ORM\Entity]
#[ORM\Table]
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    /**
     * @var Collection<array-key, Group>
     *
     * @ORM\ManyToMany(targetEntity="Pagerfanta\Doctrine\ORM\Tests\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(
     *     name="user_groups",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_groups')]
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'group_id', onDelete: 'CASCADE')]
    private Collection $groups;

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
