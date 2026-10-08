# Templates

Pagerfanta defines two interfaces which are an abstraction layer for building the markup for different sections of a pagination list:

- `Pagerfanta\View\Template\SequentialTemplateInterface` for rendering the previous and next links of any pager
- `Pagerfanta\View\Template\TemplateInterface`, which extends the sequential template interface for rendering numbered page links

All of the templates provided by Pagerfanta implement the `TemplateInterface`, so they can be used with both the numbered and the sequential views.

## Sequential Templates

The `Pagerfanta\View\Template\SequentialTemplateInterface` is used by the [sequential view](/open-source/packages/pagerfanta/docs/5.x/views#sequential-views) to render the previous and next links of any pager, using [positions](/open-source/packages/pagerfanta/docs/5.x/route-generator) instead of page numbers.

The interface requires several methods to be implemented:

- `setPositionRouteGenerator`: Injects the route generator to use while rendering the template
- `setOptions`: Sets options for the template
- `container`: Generates the wrapping container for the pagination list
- `previousDisabled`: Generates the markup for the previous page button in the disabled state
- `previousEnabledForPosition`: Generates the markup for the previous page button in the enabled state, linking to the given position
- `nextDisabled`: Generates the markup for the next page button in the disabled state
- `nextEnabledForPosition`: Generates the markup for the next page button in the enabled state, linking to the given position

```php
<?php

namespace Pagerfanta\View\Template;

use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;

interface SequentialTemplateInterface
{
    /**
     * Sets the position based route generator used while rendering the template.
     */
    public function setPositionRouteGenerator(PositionRouteGeneratorInterface $routeGenerator): void;

    /**
     * Sets the options for the template, overwriting keys that were previously set.
     */
    public function setOptions(array $options): void;

    /**
     * Renders the container for the pagination.
     *
     * The %pages% placeholder will be replaced by the rendering of the links.
     */
    public function container(): string;

    /**
     * Renders the disabled state of the previous page.
     */
    public function previousDisabled(): string;

    /**
     * Renders the enabled state of the previous page, linking to the given position.
     */
    public function previousEnabledForPosition(Position $position): string;

    /**
     * Renders the disabled state of the next page.
     */
    public function nextDisabled(): string;

    /**
     * Renders the enabled state of the next page, linking to the given position.
     */
    public function nextEnabledForPosition(Position $position): string;
}
```

## Numbered Templates

The `Pagerfanta\View\Template\TemplateInterface` is used by the [numbered views](/open-source/packages/pagerfanta/docs/5.x/views#available-views) to render a list of numbered page links for an offset pager, along with the previous and next links.

In addition to the methods of the `SequentialTemplateInterface`, the interface requires several methods to be implemented:

- `page`: Generates the markup for a single page in the pagination list
- `pageWithText`: Generates the markup for a single page with the specified text label
- `first`: Generates the markup for the first page button
- `last`: Generates the markup for the last page button
- `current`: Generates the markup for the current page button
- `separator`: Generates the markup for a separator button, used to represent a break in a list of pages (i.e. 1, 2, ..., 6, 7)

```php
<?php

namespace Pagerfanta\View\Template;

interface TemplateInterface extends SequentialTemplateInterface
{
    /**
     * Renders a given page.
     */
    public function page(int $page): string;

    /**
     * Renders a given page with a specified text.
     */
    public function pageWithText(int $page, string $text, ?string $rel = null): string;

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

## Base Class

The `Pagerfanta\View\Template\Template` base class is recommended for use when creating a custom template. It implements the `setPositionRouteGenerator()` and `setOptions()` methods, and provides the following helpers:

- `getDefaultOptions`: Override this method to define the default options for the template
- `option`: Retrieves the value of an option
- `generateRoute`: Generates the URL for a page number
- `generateRouteForPosition`: Generates the URL for a position
