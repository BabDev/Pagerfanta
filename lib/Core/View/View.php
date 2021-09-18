<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;

abstract class View implements ViewInterface
{
    /**
     * @var PagerfantaInterface<mixed>
     */
    protected PagerfantaInterface $pagerfanta;

    /**
     * @phpstan-var positive-int|null
     */
    protected ?int $currentPage = null;

    /**
     * @phpstan-var positive-int|null
     */
    protected ?int $nbPages = null;
    protected ?int $proximity = null;

    /**
     * @phpstan-var positive-int|null
     */
    protected ?int $startPage = null;

    /**
     * @phpstan-var positive-int|null
     */
    protected ?int $endPage = null;

    /**
     * @param PagerfantaInterface<mixed> $pagerfanta
     */
    protected function initializePagerfanta(PagerfantaInterface $pagerfanta): void
    {
        $this->pagerfanta = $pagerfanta;

        $this->currentPage = $pagerfanta->getCurrentPage();
        $this->nbPages = $pagerfanta->getNbPages();
    }

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
