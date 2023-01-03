<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class Person
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(nullable: true)]
    public ?string $name = null;

    #[ORM\Column(nullable: true)]
    public ?string $biography = null;
}
