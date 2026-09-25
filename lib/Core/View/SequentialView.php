<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\CursorPagerInterface;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageRouteGeneratorWrapper;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\Template\SequentialTemplateInterface;

/**
 * View which renders previous and next links for any pager, independent of its pagination strategy.
 *
 * The previous link is omitted for cursor pagers which do not support backward navigation.
 */
final class SequentialView implements PagerViewInterface
{
    public function __construct(
        private readonly SequentialTemplateInterface $template,
        private readonly string $name = 'sequential',
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerfantaInterface|PagerInterface $pager): bool
    {
        return true;
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position>                    $pager
     * @param PositionRouteGeneratorInterface|RouteGeneratorInterface|callable(int): string $routeGenerator
     * @param array<string, mixed>                                                          $options
     */
    public function render(PagerfantaInterface|PagerInterface $pager, callable $routeGenerator, array $options = []): string
    {
        $this->template->setPositionRouteGenerator(PageRouteGeneratorWrapper::wrap($routeGenerator));
        $this->template->setOptions($options);

        return str_replace('%pages%', $this->previous($pager).$this->next($pager), $this->template->container());
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager
     */
    private function previous(PagerfantaInterface|PagerInterface $pager): string
    {
        if ($pager instanceof CursorPagerInterface && !$pager->supportsBackwardNavigation()) {
            return '';
        }

        if (!$pager->hasPreviousPage()) {
            return $this->template->previousDisabled();
        }

        // Implementations of PagerfantaInterface are not required to implement the position API until 5.0
        $position = $pager instanceof PagerInterface ? $pager->getPreviousPosition() : new PagePosition($pager->getPreviousPage());

        return $this->template->previousEnabledForPosition($position);
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager
     */
    private function next(PagerfantaInterface|PagerInterface $pager): string
    {
        if (!$pager->hasNextPage()) {
            return $this->template->nextDisabled();
        }

        $position = $pager instanceof PagerInterface ? $pager->getNextPosition() : new PagePosition($pager->getNextPage());

        return $this->template->nextEnabledForPosition($position);
    }
}
