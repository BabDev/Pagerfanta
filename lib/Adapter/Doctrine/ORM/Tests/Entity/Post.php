<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
class Post
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    public function __construct(
        #[ORM\Column]
        public int $score,
        #[ORM\Column]
        public \DateTimeImmutable $publishedAt,
    ) {}
}
