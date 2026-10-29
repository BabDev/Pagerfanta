# Upgrade from 4.x to 5.0

The below guide will assist in upgrading from the 4.x versions to 5.0.

Pagerfanta 5.0 removes the page number based route generator, view, and template APIs which were deprecated in 4.10. Upgrading to 4.10 and resolving its deprecations first is the recommended upgrade path.

## Package Requirements

- PHP 8.4 or later

## General Changes

- Dropped support for versions of Doctrine Collections before 2.0
- Dropped support for versions of Doctrine DBAL before 3.10
- Dropped support for versions of Doctrine MongoDB ODM before 2.11
- Dropped support for versions of Doctrine ORM before 3.7
- Dropped support for versions of Doctrine PHPCR ODM before 2.0
- Dropped support for versions of Twig before 3.29
- The Doctrine DBAL query adapter requires the COUNT query builder modifier now to return a `Doctrine\DBAL\Query\QueryBuilder`

### Pagers

- Counting a pager (i.e. `count($pagerfanta)` or `pagerfanta|length` in Twig) now returns the number of items on the current page instead of the total number of results, use the `getNbResults()` method to get the total number of results
    - Counting a `Pagerfanta\Pagerfanta` instance now fetches the current page of results from the adapter instead of the result count
    - When the adapter returns the current page of results as an iterator which is not countable, counting a `Pagerfanta\Pagerfanta` instance converts the results to an array
- `Pagerfanta\PagerInterface` now extends `Countable`
- `Pagerfanta\PagerfantaInterface` now extends `Pagerfanta\OffsetPagerInterface`, implementations are required to implement the `count()`, `getPreviousPosition()`, and `getNextPosition()` methods
- `Pagerfanta\PagerfantaInterface` now requires the `autoPagingIterator` method to be implemented
- `Pagerfanta\CursorPagerInterface` now requires the `withPosition()` method to be implemented, which returns a new pager for the given position

### Route Generators

- Route generators are now required to implement `Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface`, a callable is no longer accepted as a route generator; use `Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator` to use a callable accepting a `Pagerfanta\Position\Position` as a route generator
- `Pagerfanta\RouteGenerator\PageNumberRouteGenerator` now only accepts a `Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface` and always throws a `Pagerfanta\Exception\LessThan1CurrentPageException` for a page number less than 1

### Views

- `Pagerfanta\View\ViewInterface::render()` now accepts any `Pagerfanta\PagerInterface` as the pager and requires a `Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface` as the route generator
- `Pagerfanta\View\ViewInterface` now requires the `supports()` method to be implemented, which checks whether the view can render a pager
- The numbered views (`Pagerfanta\View\View` and its subclasses) now support any `Pagerfanta\OffsetPagerInterface`
    - The `Pagerfanta\View\View::$pagerfanta` property and the `Pagerfanta\View\View::initializePagerfanta()` method parameter are now typed as a `Pagerfanta\OffsetPagerInterface`
    - `Pagerfanta\View\TemplateView::render()` now throws a `Pagerfanta\Exception\InvalidArgumentException` when given a pager which is not an offset pager
- `Pagerfanta\View\PagerViewInterface` is deprecated, implement `Pagerfanta\View\ViewInterface` instead

### Templates

- `Pagerfanta\View\Template\TemplateInterface` now extends `Pagerfanta\View\Template\SequentialTemplateInterface`, templates are required to implement the `setPositionRouteGenerator()`, `previousEnabledForPosition()`, and `nextEnabledForPosition()` methods
- The template views now render the previous and next page links with the `previousEnabledForPosition()` and `nextEnabledForPosition()` methods
    - A template which extends one of the templates provided by Pagerfanta and overrides the `previousEnabled()` or `nextEnabled()` method must override the `previousEnabledForPosition()` or `nextEnabledForPosition()` method instead, **the old methods are no longer called and no error is raised**
- `Pagerfanta\View\Template\Template::generateRoute()` now generates the route with the position route generator and throws a `Pagerfanta\Exception\LessThan1CurrentPageException` for a page number less than 1
- The links rendered by the templates are now described by a `Pagerfanta\View\Template\Link`, with its relation to the current page given as a `Pagerfanta\View\Template\LinkRelation`, which is created with the `createLink()` or `createPageLink()` method of `Pagerfanta\View\Template\Template` and passed to the methods rendering the link markup
    - `Pagerfanta\View\Template\Template::linkAttributes()` now receives the `Link` being rendered instead of the link's relation and position, the relation is a `LinkRelation` instead of a string
    - `Pagerfanta\View\Template\Template::renderLinkAttributes()` now requires the `Link` being rendered
    - The `linkLi()` methods of `Pagerfanta\View\Template\TwitterBootstrapTemplate`, `Pagerfanta\View\Template\TwitterBootstrap4Template`, and `Pagerfanta\View\Template\Foundation6Template` now receive the CSS class and the `Link` being rendered instead of the URL, text, and `rel` attribute of the link

### Twig

- `Pagerfanta\Twig\Extension\PagerfantaRuntime` now requires a `Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface` as the route generator factory
- `Pagerfanta\Twig\Extension\PagerfantaRuntime::getPageUrl()` (the `pagerfanta_page_url()` Twig function) now accepts any `Pagerfanta\OffsetPagerInterface`
- The `pager` block of the `@Pagerfanta/default.html.twig` template now reads the previous and next page numbers from `pagerfanta.getPreviousPosition().page` and `pagerfanta.getNextPosition().page`; templates overriding this block should do the same to support every offset pager
- The `previous_page_message` and `next_page_message` blocks moved from the `@Pagerfanta/default.html.twig` template to the new `@Pagerfanta/messages.html.twig` template
    - Templates overriding these blocks continue to work, but the overrides can no longer call the `parent()` function
    - The supplied templates can no longer be rendered on their own (i.e. with `Twig\Environment::load()` and `renderBlock()`), render them with `Pagerfanta\Twig\View\TwigView` or include the messages template in a `Twig\BlockChain`
- `Pagerfanta\Twig\View\TwigView` now always renders with a `Twig\BlockChain`, a single template given to the `template` option or the constructor now falls back to the default template given to the constructor, the `@Pagerfanta/default.html.twig` template, and the `@Pagerfanta/messages.html.twig` template for any block it does not define

## Removed Features

- Removed `Pagerfanta\RouteGenerator\RouteGeneratorInterface`, implement `Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface` instead
- Removed `Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface`, implement `Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface` instead
- Removed `Pagerfanta\RouteGenerator\RouteGeneratorDecorator`, use `Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator` instead
- Removed `Pagerfanta\View\Template\TemplateInterface::setRouteGenerator()`, templates are given a position route generator with `setPositionRouteGenerator()` instead
- Removed `Pagerfanta\View\Template\TemplateInterface::previousEnabled()`, implement `previousEnabledForPosition()` instead
- Removed `Pagerfanta\View\Template\TemplateInterface::nextEnabled()`, implement `nextEnabledForPosition()` instead
- Removed `Pagerfanta\View\Template\Template::renderLink()` and `Pagerfanta\View\Template\Template::renderPageLink()`, create the link with `createLink()` or `createPageLink()` and pass it to the methods rendering the link markup instead
