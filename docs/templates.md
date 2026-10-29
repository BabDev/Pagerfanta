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

## Link Attributes

The `Pagerfanta\View\Template\Template` base class allows additional attributes to be added to the links rendered by a template. All of the templates provided by Pagerfanta support link attributes.

To add attributes, extend a template and override the `linkAttributes()` method. The method receives the relation of the link to the current page (`previous`, `page`, or `next`) and the `Pagerfanta\Position\Position` the link points to, and returns the attributes to add to the link. The first, last, and numbered page links use the `page` relation, and numbered pagers give a `Pagerfanta\Position\PagePosition` for each link, while cursor pagers rendered with a [sequential view](/open-source/packages/pagerfanta/docs/5.x/views#sequential-views) give a `Pagerfanta\Position\CursorPosition` for the previous and next links.

```php
<?php

namespace App\Pagination;

use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\View\Template\TwitterBootstrap5Template;

final class TrackedTwitterBootstrap5Template extends TwitterBootstrap5Template
{
    protected function linkAttributes(string $relation, Position $position): array
    {
        return [
            'data-relation' => $relation,
            'data-page' => $position instanceof PagePosition ? $position->page : null,
        ];
    }
}
```

The attribute values are escaped when rendered. A `true` value renders a boolean attribute, and a `false` or `null` value omits the attribute. The `href`, `rel`, and `class` attributes are controlled by the template and cannot be set, use the template's options to change the CSS classes and `rel` values.

<div class="docs-note">Attribute names are not escaped and must be valid HTML attribute names, an exception is thrown otherwise. Do not build attribute names from user input.</div>

### Custom Link Markup

A template extending the base class renders its links inside the `renderLink()` method, which makes the link's relation and position available while the markup is built, and adds the attributes to the link's opening tag with the `renderLinkAttributes()` method. When a link is identified by its page number, the `renderPageLink()` method can be used instead of `renderLink()`.

```php
<?php

namespace App\Pagination;

use Pagerfanta\Position\Position;
use Pagerfanta\View\Template\DefaultTemplate;

final class ButtonTemplate extends DefaultTemplate
{
    public function nextEnabledForPosition(Position $position): string
    {
        return $this->renderLink(
            'next',
            $position,
            fn (): string => sprintf('<a class="button" href="%s"%s>Load more</a>', $this->generateRouteForPosition($position), $this->renderLinkAttributes()),
        );
    }
}
```

When overriding the methods which render the link markup of the provided templates, such as the `linkLi()` method of the Twitter Bootstrap templates, call the `renderLinkAttributes()` method in the overridden method to keep supporting link attributes. The `DefaultTemplate` renders the link attributes with the `%attributes%` placeholder of its `page_template` option.
