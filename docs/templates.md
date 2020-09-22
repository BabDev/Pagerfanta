# Templates

Pagerfanta defines `Pagerfanta\View\Template\TemplateInterface` which is an abstraction layer for building the markup for different sections of a pagination list.

The interface requires several methods to be implemented:

- `setRouteGenerator`: Injects the route generator to use while rendering the template
- `setOptions`: Sets options for the template
- `container`: Generates the wrapping container for the pagination list
- `page`: Generates the markup for a single page in the pagination list 
- `pageWithText`: Generates the markup for a single page with the specified text label 
- `previousDisabled`: Generates the markup for the previous page button in the disabled state 
- `previousEnabled`: Generates the markup for the previous page button in the enabled state 
- `nextDisabled`: Generates the markup for the next page button in the disabled state 
- `nextEnabled`: Generates the markup for the next page button in the enabled state 
- `first`: Generates the markup for the first page button 
- `last`: Generates the markup for the last page button 
- `current`: Generates the markup for the current page button 
- `separator`: Generates the markup for a separator button, used to represent a break in a list of pages (i.e. 1, 2, ..., 6, 7) 

```php
<?php

namespace Pagerfanta\View\Template;

use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

interface TemplateInterface
{
    /**
     * Sets the route generator used while rendering the template.
     *
     * @param callable|RouteGeneratorInterface $routeGenerator
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
```
