<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\Template\TemplateInterface;

abstract class TemplateView extends View
{
    private TemplateInterface $template;

    public function __construct(TemplateInterface $template = null)
    {
        if (null === $template) {
            $template = $this->createDefaultTemplate();
        }

        $this->template = $template;
    }

    abstract protected function createDefaultTemplate(): TemplateInterface;

    /**
     * @param PagerfantaInterface<mixed>       $pagerfanta
     * @param callable|RouteGeneratorInterface $routeGenerator
     *
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string
    {
        $this->initializePagerfanta($pagerfanta);
        $this->initializeOptions($options);

        $this->configureTemplate($routeGenerator, $options);

        return $this->generate();
    }

    /**
     * @param callable|RouteGeneratorInterface $routeGenerator
     *
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    private function configureTemplate(callable $routeGenerator, array $options): void
    {
        $this->template->setRouteGenerator($routeGenerator);
        $this->template->setOptions($options);
    }

    private function generate(): string
    {
        return $this->generateContainer($this->generatePages());
    }

    private function generateContainer(string $pages): string
    {
        return str_replace('%pages%', $pages, $this->template->container());
    }

    private function generatePages(): string
    {
        $this->calculateStartAndEndPage();

        return $this->previous().
               $this->first().
               $this->secondIfStartIs3().
               $this->dotsIfStartIsOver3().
               $this->pages().
               $this->dotsIfEndIsUnder3ToLast().
               $this->secondToLastIfEndIs3ToLast().
               $this->last().
               $this->next();
    }

    private function previous(): string
    {
        if ($this->pagerfanta->hasPreviousPage()) {
            return $this->template->previousEnabled($this->pagerfanta->getPreviousPage());
        }

        return $this->template->previousDisabled();
    }

    private function first(): string
    {
        if ($this->startPage > 1) {
            return $this->template->first();
        }

        return '';
    }

    private function secondIfStartIs3(): string
    {
        if (3 === $this->startPage) {
            return $this->template->page(2);
        }

        return '';
    }

    private function dotsIfStartIsOver3(): string
    {
        if ($this->startPage > 3) {
            return $this->template->separator();
        }

        return '';
    }

    private function pages(): string
    {
        $pages = '';

        foreach (range($this->startPage, $this->endPage) as $page) {
            $pages .= $this->page($page);
        }

        return $pages;
    }

    private function page(int $page): string
    {
        if ($page === $this->currentPage) {
            return $this->template->current($page);
        }

        return $this->template->page($page);
    }

    private function dotsIfEndIsUnder3ToLast(): string
    {
        if ($this->endPage < $this->toLast(3)) {
            return $this->template->separator();
        }

        return '';
    }

    private function secondToLastIfEndIs3ToLast(): string
    {
        if ($this->endPage == $this->toLast(3)) {
            return $this->template->page($this->toLast(2));
        }

        return '';
    }

    private function last(): string
    {
        if ($this->pagerfanta->getNbPages() > $this->endPage) {
            return $this->template->last($this->pagerfanta->getNbPages());
        }

        return '';
    }

    private function next(): string
    {
        if ($this->pagerfanta->hasNextPage()) {
            return $this->template->nextEnabled($this->pagerfanta->getNextPage());
        }

        return $this->template->nextDisabled();
    }
}
