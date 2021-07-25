<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="groups")
 */
class Group
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;

    /**
     * @var Collection<array-key, User>
     *
     * @ORM\ManyToMany(targetEntity="\Pagerfanta\Doctrine\ORM\Tests\Entity\User", mappedBy="groups")
     */
    public Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }
}
