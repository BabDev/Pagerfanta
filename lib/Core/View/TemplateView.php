<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\OffsetPagerInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageNumberRouteGenerator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\View\Template\TemplateInterface;

abstract class TemplateView extends View
{
    /**
     * The template given to the view, which is cloned for each render so the options and route generator of a render are not
     * reused by the next one.
     */
    private readonly TemplateInterface $baseTemplate;

    /**
     * The template for the current render.
     */
    private TemplateInterface $template;

    public function __construct(?TemplateInterface $template = null)
    {
        $this->template = $this->baseTemplate = $template ?? $this->createDefaultTemplate();

        if (!$this->baseTemplate instanceof SequentialTemplateInterface) {
            trigger_deprecation('pagerfanta/core', '4.10', 'Using a template which does not implement "%s" with "%s" is deprecated, the "%s" template will be required to implement it in 5.0.', SequentialTemplateInterface::class, static::class, get_debug_type($this->baseTemplate));
        }
    }

    abstract protected function createDefaultTemplate(): TemplateInterface;

    /**
     * @param PagerInterface<mixed, Position> $pager
     * @param array<string, mixed>            $options
     *
     * @throws InvalidArgumentException if the pager is not an offset pager
     */
    public function render(PagerInterface $pager, PositionRouteGeneratorInterface $routeGenerator, array $options = []): string
    {
        if (!$pager instanceof OffsetPagerInterface) {
            throw new InvalidArgumentException(\sprintf('The "%s" view can only render pagers implementing "%s", "%s" given.', static::class, OffsetPagerInterface::class, get_debug_type($pager)));
        }

        $this->template = clone $this->baseTemplate;

        $this->initializePagerfanta($pager);
        $this->initializeOptions($options);

        // The numbered templates link to pages by their number
        $this->configureTemplate(new PageNumberRouteGenerator($routeGenerator), $options);

        return $this->generate();
    }

    /**
     * @param callable(int): string $routeGenerator
     * @param array<string, mixed>  $options
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
            return $this->template->previousEnabled($this->pagerfanta->getPreviousPosition()->page);
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
        \assert(null !== $this->startPage);
        \assert(null !== $this->endPage);

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
            return $this->template->nextEnabled($this->pagerfanta->getNextPosition()->page);
        }

        return $this->template->nextDisabled();
    }
}
