<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

interface TemplateInterface
{
    /**
     * Sets the route generator used while rendering the template.
     *
     * @param callable|RouteGeneratorInterface $routeGenerator
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function setRouteGenerator(callable $routeGenerator): void;

    /**
     * Sets the options for the template, overwriting keys that were previously set.
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
     */
    public function previousEnabled(int $page): string;

    /**
     * Renders the disabled state of the next page.
     */
    public function nextDisabled(): string;

    /**
     * Renders the enabled state of the next page.
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
