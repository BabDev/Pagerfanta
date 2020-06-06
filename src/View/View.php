<?php

namespace Pagerfanta\View;

use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

abstract class View implements ViewInterface
{
    /**
     * @var Pagerfanta
     */
    protected $pagerfanta;

    /**
     * @var int
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $nbPages;

    /**
     * @var int
     */
    protected $proximity;

    /**
     * @var int
     */
    protected $startPage;

    /**
     * @var int
     */
    protected $endPage;

    protected function initializePagerfanta(PagerfantaInterface $pagerfanta): void
    {
        if (!($pagerfanta instanceof Pagerfanta)) {
            trigger_deprecation(
                'babdev/pagerfanta',
                '2.2',
                '%1$s::render() will no longer accept "%2$s" implementations that are not a subclass of "%3$s" as of 3.0. Ensure your pager is a subclass of "%3$s".',
                ViewInterface::class,
                PagerfantaInterface::class,
                Pagerfanta::class
            );
        }

        $this->pagerfanta = $pagerfanta;

        $this->currentPage = $pagerfanta->getCurrentPage();
        $this->nbPages = $pagerfanta->getNbPages();
    }

    /**
     * @param array $options
     */
    protected function initializeOptions(array $options): void
    {
        $this->proximity = isset($options['proximity']) ? (int) $options['proximity'] : $this->getDefaultProximity();
    }

    /**
     * @return int
     */
    protected function getDefaultProximity()
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

    protected function startPageUnderflow($startPage)
    {
        return $startPage < 1;
    }

    protected function endPageOverflow($endPage)
    {
        return $endPage > $this->nbPages;
    }

    protected function calculateEndPageForStartPageUnderflow($startPage, $endPage)
    {
        return min($endPage + (1 - $startPage), $this->nbPages);
    }

    protected function calculateStartPageForEndPageOverflow($startPage, $endPage)
    {
        return max($startPage - ($endPage - $this->nbPages), 1);
    }

    protected function toLast($n)
    {
        return $this->pagerfanta->getNbPages() - ($n - 1);
    }
}
