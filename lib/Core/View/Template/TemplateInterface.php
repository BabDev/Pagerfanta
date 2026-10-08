<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

/**
 * In 5.0, this interface will extend {@see SequentialTemplateInterface}, and the page number based {@see setRouteGenerator()},
 * {@see previousEnabled()}, and {@see nextEnabled()} methods will be removed. Implement {@see SequentialTemplateInterface}
 * to prepare for this change.
 *
 * @method void   setPositionRouteGenerator(PositionRouteGeneratorInterface $routeGenerator)
 * @method string previousEnabledForPosition(Position $position)
 * @method string nextEnabledForPosition(Position $position)
 */
interface TemplateInterface /* extends SequentialTemplateInterface */
{
    /**
     * Sets the route generator used while rendering the template.
     *
     * @deprecated since Pagerfanta 4.10, to be removed in 5.0. Templates will be given a position route generator with {@see SequentialTemplateInterface::setPositionRouteGenerator()} instead.
     *
     * @param callable|RouteGeneratorInterface $routeGenerator
     *
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function setRouteGenerator(callable $routeGenerator): void;

    /**
     * Sets the options for the template, overwriting keys that were previously set.
     *
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void;

    /**
     * Renders the container for the pagination.
     *
     * The %pages% placeholder will be replaced by the rendering of pages.
     */
    public function container(): string;

    /**
     * Renders a given page.
     */
    public function page(int $page): string;

    /**
     * Renders a given page with a specified text.
     */
    public function pageWithText(int $page, string $text, ?string $rel = null): string;

    /**
     * Renders the disabled state of the previous page.
     */
    public function previousDisabled(): string;

    /**
     * Renders the enabled state of the previous page.
     *
     * @deprecated since Pagerfanta 4.10, to be removed in 5.0. Implement {@see SequentialTemplateInterface::previousEnabledForPosition()} instead.
     */
    public function previousEnabled(int $page): string;

    /**
     * Renders the disabled state of the next page.
     */
    public function nextDisabled(): string;

    /**
     * Renders the enabled state of the next page.
     *
     * @deprecated since Pagerfanta 4.10, to be removed in 5.0. Implement {@see SequentialTemplateInterface::nextEnabledForPosition()} instead.
     */
    public function nextEnabled(int $page): string;

    /**
     * Renders the first page.
     */
    public function first(): string;

    /**
     * Renders the last page.
     */
    public function last(int $page): string;

    /**
     * Renders the current page.
     */
    public function current(int $page): string;

    /**
     * Renders the separator between pages.
     */
    public function separator(): string;
}
