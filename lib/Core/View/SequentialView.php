<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\CursorPagerInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\View\Template\SequentialTemplateInterface;

/**
 * View which renders previous and next links for any pager, independent of its pagination strategy.
 *
 * The previous link is omitted for cursor pagers which do not support backward navigation.
 */
final class SequentialView implements ViewInterface
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
     * @param PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerInterface $pager): bool
    {
        return true;
    }

    /**
     * @param PagerInterface<mixed, Position> $pager
     * @param array<string, mixed>            $options
     */
    public function render(PagerInterface $pager, PositionRouteGeneratorInterface $routeGenerator, array $options = []): string
    {
        // Render from a copy of the template so the options and route generator of this render are not reused by the next one
        $template = clone $this->template;
        $template->setPositionRouteGenerator($routeGenerator);
        $template->setOptions($options);

        return str_replace('%pages%', $this->previous($template, $pager).$this->next($template, $pager), $template->container());
    }

    /**
     * @param PagerInterface<mixed, Position> $pager
     */
    private function previous(SequentialTemplateInterface $template, PagerInterface $pager): string
    {
        if ($pager instanceof CursorPagerInterface && !$pager->supportsBackwardNavigation()) {
            return '';
        }

        if (!$pager->hasPreviousPage()) {
            return $template->previousDisabled();
        }

        return $template->previousEnabledForPosition($pager->getPreviousPosition());
    }

    /**
     * @param PagerInterface<mixed, Position> $pager
     */
    private function next(SequentialTemplateInterface $template, PagerInterface $pager): string
    {
        if (!$pager->hasNextPage()) {
            return $template->nextDisabled();
        }

        return $template->nextEnabledForPosition($pager->getNextPosition());
    }
}
