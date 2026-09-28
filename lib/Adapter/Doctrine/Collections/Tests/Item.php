<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\Collections\Tests;

final class Item
{
    public function __construct(
        private readonly int $id,
        private readonly ?int $score,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }
}
