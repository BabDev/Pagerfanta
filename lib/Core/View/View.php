<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;

abstract class View implements ViewInterface
{
    /**
     * @var PagerfantaInterface<mixed>
     */
    protected PagerfantaInterface $pagerfanta;

    /**
     * @var positive-int|null
     */
    protected ?int $currentPage = null;

    /**
     * @var positive-int|null
     */
    protected ?int $nbPages = null;
    protected ?int $proximity = null;

    /**
     * @var positive-int|null
     */
    protected ?int $startPage = null;

    /**
     * @var positive-int|null
     */
    protected ?int $endPage = null;

    /**
     * Checks whether this view can render the given pager, numbered views can only render offset pagers.
     *
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerfantaInterface|PagerInterface $pager): bool
    {
        return $pager instanceof PagerfantaInterface;
    }

    /**
     * @param PagerfantaInterface<mixed> $pagerfanta
     */
    protected function initializePagerfanta(PagerfantaInterface $pagerfanta): void
    {
        $this->pagerfanta = $pagerfanta;

        $this->currentPage = $pagerfanta->getCurrentPage();
        $this->nbPages = $pagerfanta->getNbPages();
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function initializeOptions(array $options): void
    {
        $this->proximity = isset($options['proximity']) ? (int) $options['proximity'] : $this->getDefaultProximity();
    }

    protected function getDefaultProximity(): int
    {
        return 2;
    }

    protected function calculateStartAndEndPage(): void
    {
        \assert(null !== $this->currentPage);
        \assert(null !== $this->proximity);

        $startPage = $this->currentPage - $this->proximity;
        $endPage = $this->currentPage + $this->proximity;

        if ($this->startPageUnderflow($startPage)) {
            $endPage = $this->calculateEndPageForStartPageUnderflow($startPage, $endPage);
            $startPage = 1;
        }

        if ($this->endPageOverflow($endPage)) {
            $startPage = $this->calculateStartPageForEndPageOverflow($startPage, $endPage);
            $endPage = $this->nbPages;
        }

        \assert($startPage >= 1);
        \assert($endPage >= 1);

        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    protected function startPageUnderflow(int $startPage): bool
    {
        return $startPage < 1;
    }

    protected function endPageOverflow(int $endPage): bool
    {
        return $endPage > $this->nbPages;
    }

    /**
     * @return positive-int
     */
    protected function calculateEndPageForStartPageUnderflow(int $startPage, int $endPage): int
    {
        \assert(null !== $this->nbPages);

        return min($endPage + (1 - $startPage), $this->nbPages);
    }

    /**
     * @return positive-int
     */
    protected function calculateStartPageForEndPageOverflow(int $startPage, int $endPage): int
    {
        return max($startPage - ($endPage - $this->nbPages), 1);
    }

    protected function toLast(int $n): int
    {
        return $this->pagerfanta->getNbPages() - ($n - 1);
    }
}
