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

<div class="docs-note docs-note--deprecated-feature">In Pagerfanta 5.0, the <code>TemplateInterface</code> will extend the <a href="#sequential-templates"><code>SequentialTemplateInterface</code></a>, which adds the <code>setPositionRouteGenerator</code>, <code>previousEnabledForPosition</code>, and <code>nextEnabledForPosition</code> methods. These replace the page number based <code>setRouteGenerator</code>, <code>previousEnabled</code>, and <code>nextEnabled</code> methods, which are deprecated since Pagerfanta 4.10 and will be removed in 5.0. Using a template which does not implement the <code>SequentialTemplateInterface</code> with a view is deprecated.</div>

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

## Sequential Templates

<div class="docs-note docs-note--new-feature">Sequential templates were introduced in Pagerfanta 4.10.</div>

Pagerfanta defines `Pagerfanta\View\Template\SequentialTemplateInterface` which is used by the [sequential view](/open-source/packages/pagerfanta/docs/4.x/views#sequential-views) to render the previous and next links of any pager, using positions instead of page numbers.

The interface requires several methods to be implemented:

- `setPositionRouteGenerator`: Injects the position route generator to use while rendering the template
- `setOptions`: Sets options for the template
- `container`: Generates the wrapping container for the pagination list
- `previousDisabled`: Generates the markup for the previous page button in the disabled state
- `previousEnabledForPosition`: Generates the markup for the previous page button in the enabled state, linking to the given position
- `nextDisabled`: Generates the markup for the next page button in the disabled state
- `nextEnabledForPosition`: Generates the markup for the next page button in the enabled state, linking to the given position

All of the templates provided by Pagerfanta implement both the `TemplateInterface` and the `SequentialTemplateInterface`, so they can be used with both the numbered and the sequential views. The `Pagerfanta\View\Template\Template` base class provides the `setPositionRouteGenerator()` method and a `generateRouteForPosition()` helper, so a custom template extending it only needs to declare the `SequentialTemplateInterface` and implement the `previousEnabledForPosition()` and `nextEnabledForPosition()` methods to support the sequential view.

```php
<?php

namespace Pagerfanta\View\Template;

use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;

interface SequentialTemplateInterface
{
    public function setPositionRouteGenerator(PositionRouteGeneratorInterface $routeGenerator): void;

    public function setOptions(array $options): void;

    public function container(): string;

    public function previousDisabled(): string;

    public function previousEnabledForPosition(Position $position): string;

    public function nextDisabled(): string;

    public function nextEnabledForPosition(Position $position): string;
}
```

## Link Attributes

<div class="docs-note docs-note--new-feature">Link attributes were introduced in Pagerfanta 4.11.</div>

The `Pagerfanta\View\Template\Template` base class allows additional attributes to be added to the links rendered by a template. All of the templates provided by Pagerfanta support link attributes.

To add attributes, extend a template and override the `linkAttributes()` method. The method receives the relation of the link to the current page (`previous`, `page`, or `next`) and the `Pagerfanta\Position\Position` the link points to, and returns the attributes to add to the link. The first, last, and numbered page links use the `page` relation, and numbered pagers give a `Pagerfanta\Position\PagePosition` for each link, while cursor pagers rendered with a [sequential view](/open-source/packages/pagerfanta/docs/4.x/views#sequential-views) give a `Pagerfanta\Position\CursorPosition` for the previous and next links.

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

<div class="docs-note">In Pagerfanta 5.0, the link being rendered will be passed to the methods rendering the link markup instead of being made available with the <code>renderLink()</code> method.</div>
