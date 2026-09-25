# Views

Pagerfanta defines `Pagerfanta\View\ViewInterface` which is the abstraction layer for rendering a pagination list.

The interface requires two methods to be implemented:

- `render`: Generates the markup for the pagination list
- `getName`: Retrieves the unique name of the view 

```php
<?php

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;

interface ViewInterface
{
    /**
     * @param callable $routeGenerator callable with a signature of `function (int $page): string {}`, or a `Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface`
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string;

    public function getName(): string;
}
```

<div class="docs-note docs-note--deprecated-feature">Passing a page number based route generator to a view is deprecated since Pagerfanta 4.10, all of the views provided by Pagerfanta accept a <a href="/open-source/packages/pagerfanta/docs/4.x/route-generator#position-route-generators">position route generator</a>. In Pagerfanta 5.0, the <code>render</code> method will accept any pager and a position route generator, and a <code>supports</code> method will be added to check whether a view can render a pager. Implement the <code>Pagerfanta\View\PagerViewInterface</code> to prepare for this change.</div>

## Base Classes

Pagerfanta provides two base classes to build upon to assist in creating custom views.

### `Pagerfanta\View\View`

The `View` class is the base class that is recommended for use. It contains all of the logic necessary for calculating the page items to be displayed in the pagination list.

### `Pagerfanta\View\TemplateView`

The `TemplateView` class is an extension of the `View` class and provides support for rendering pagination lists using `Pagerfanta\View\Template\TemplateInterface` instances.

## Available Views

Below is a list of the views that are available with this package, and the corresponding template class.

| View Name            | View Class Name                         | Template Class Name                                  |
|----------------------|-----------------------------------------|------------------------------------------------------|
| `default`            | `Pagerfanta\View\DefaultView`           | `Pagerfanta\View\Template\DefaultTemplate`           |
| `foundation6`        | `Pagerfanta\View\Foundation6View`       | `Pagerfanta\View\Template\Foundation6Template`       |
| `semantic_ui`        | `Pagerfanta\View\SemanticUiView`        | `Pagerfanta\View\Template\SemanticUiTemplate`        |
| `twitter_bootstrap`  | `Pagerfanta\View\TwitterBootstrapView`  | `Pagerfanta\View\Template\TwitterBootstrapTemplate`  |
| `twitter_bootstrap3` | `Pagerfanta\View\TwitterBootstrap3View` | `Pagerfanta\View\Template\TwitterBootstrap3Template` |
| `twitter_bootstrap4` | `Pagerfanta\View\TwitterBootstrap4View` | `Pagerfanta\View\Template\TwitterBootstrap4Template` |
| `twitter_bootstrap5` | `Pagerfanta\View\TwitterBootstrap5View` | `Pagerfanta\View\Template\TwitterBootstrap5Template` |

## Sequential Views

<div class="docs-note docs-note--new-feature">Sequential views were introduced in Pagerfanta 4.10.</div>

The views above render numbered page links, which requires the total number of pages and is only possible with offset pagers. Sequential views only render links to the previous and next pages, so they can render any pager, including [cursor pagers](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination).

Views which can render any pager implement `Pagerfanta\View\PagerViewInterface`, which extends `ViewInterface` with a `render` method accepting any pager and a `supports` method to check whether the view can render a pager.

```php
<?php

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;

interface PagerViewInterface extends ViewInterface
{
    public function render(PagerfantaInterface|PagerInterface $pager, callable $routeGenerator, array $options = []): string;

    public function supports(PagerfantaInterface|PagerInterface $pager): bool;
}
```

The `Pagerfanta\View\SequentialView` renders the previous and next links using any template implementing `Pagerfanta\View\Template\SequentialTemplateInterface`, which all of the templates listed above do. The view name defaults to `sequential` and can be changed with the second argument.

```php
<?php

use Pagerfanta\View\SequentialView;
use Pagerfanta\View\Template\TwitterBootstrap5Template;

$view = new SequentialView(new TwitterBootstrap5Template(), 'twitter_bootstrap5_sequential');

echo $view->render($pager, $routeGenerator, ['prev_message' => 'Newer', 'next_message' => 'Older']);
```

The previous link is disabled when there is no previous page, and omitted entirely for cursor pagers which do not support backward navigation.

<div class="docs-note">To render a cursor pager, the route generator must be a position route generator, see the <a href="/open-source/packages/pagerfanta/docs/4.x/route-generator#position-route-generators">route generator documentation</a>. A plain callable is treated as a page number based route generator, which can only render offset pagers.</div>

## Twig View

Pagerfanta includes native support for the [Twig](https://twig.symfony.com/) templating engine and allows integrators to build flexible templates for rendering their pagers.

If you have not already, you will need to install the `pagerfanta/twig` package to use the Twig integration.

The below table lists the available templates and the CSS framework they correspond to.

| Template Name                              | Framework                                                     |
|--------------------------------------------|---------------------------------------------------------------|
| `@Pagerfanta/default.html.twig`            | None (Pagerfanta's default view)                              |
| `@Pagerfanta/foundation6.html.twig`        | [Foundation](https://get.foundation/index.html) (version 6.x) |
| `@Pagerfanta/semantic_ui.html.twig`        | [Semantic UI](https://semantic-ui.com) (version 2.x)          |
| `@Pagerfanta/tailwind.html.twig`           | [Tailwind CSS](https://tailwindcss.com/)                      |
| `@Pagerfanta/twitter_bootstrap.html.twig`  | [Bootstrap](https://getbootstrap.com) (version 2.x)           |
| `@Pagerfanta/twitter_bootstrap3.html.twig` | [Bootstrap](https://getbootstrap.com) (version 3.x)           |
| `@Pagerfanta/twitter_bootstrap4.html.twig` | [Bootstrap](https://getbootstrap.com) (version 4.x)           |
| `@Pagerfanta/twitter_bootstrap5.html.twig` | [Bootstrap](https://getbootstrap.com) (version 5.x)           |

### Configuring the Twig Integration

In order to use the Twig integration, you will need to register the Twig extension, a runtime loader to resolve the runtime service, and the Pagerfanta template path to your Twig environment.

```php
<?php

use Pagerfanta\Twig\Extension\PagerfantaExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\ContainerRuntimeLoader;

/*
 * We'll use Reflection to dynamically resolve the path to the templates provided by the package.
 * This method will work regardless of whether the monolithic `pagerfanta/pagerfanta` package
 * or the `pagerfanta/twig` package is installed.
 */
$refl = new \ReflectionClass(PagerfantaExtension::class);
$path = \dirname($refl->getFileName(), 2) . '/templates';

$loader = new FilesystemLoader(['/path/to/app/templates']);

// The namespace *MUST* be "Pagerfanta" otherwise the templates will not work correctly
$loader->addPath($path, 'Pagerfanta');

$environment = new Environment($loader);

/*
 * Add the runtime loader so the runtime serivce can be lazy loaded.
 *
 * If using the PSR-11 runtime loader, the runtime service must
 * be registered to the container using its FQCN as its service ID,
 * i.e. `Pagerfanta\Twig\Extension\PagerfantaRuntime`
 */
/** @var Psr\Container\ContainerInterface $container */
$environment->addRuntimeLoader(new ContainerRuntimeLoader($container));

// Add the extension
$environment->addExtension(new PagerfantaExtension());
```

### Rendering Cursor Pagers

<div class="docs-note docs-note--new-feature">Rendering cursor pagers with the Twig view was introduced in Pagerfanta 4.10.</div>

The Twig view implements `Pagerfanta\View\PagerViewInterface`. Offset pagers are rendered with numbered page links, and all other pagers (such as cursor pagers) are rendered with previous and next links using the same templates. To render an offset pager with only previous and next links, set the `sequential` option.

```twig
{{ pagerfanta(pager, 'twig', {'sequential': true}) }}
```

When the view given to the `pagerfanta()` function (or the default view) cannot render a pager, such as a cursor pager with a numbered view, an exception is thrown. To render these pagers with another view instead, give the name of a default sequential view as the fourth argument of the `Pagerfanta\Twig\Extension\PagerfantaRuntime` constructor. A view explicitly named in the `pagerfanta()` function never falls back to the default sequential view.

The `pagerfanta_position_url()` function generates the URL for a position, such as the next page of a pager.

```twig
{% if pager.hasNextPage() %}
    <a href="{{ pagerfanta_position_url(pager.nextPosition) }}">Load more</a>
{% endif %}
```

When the route generator factory given to the runtime implements `Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface`, it is used to create the route generators for the views and for the `pagerfanta_position_url()` function. Views which only accept page number based route generators are given one adapted from the position route generator.

<div class="docs-note docs-note--deprecated-feature">Giving the runtime a route generator factory which does not implement the <code>PositionRouteGeneratorFactoryInterface</code> is deprecated since Pagerfanta 4.10. Until then, its page number based route generators are adapted, which only support offset pagers.</div>

### Creating a Twig View Template

If creating a custom template, you are encouraged to extend the `@Pagerfanta/default.html.twig` template and override only the blocks needed.

Generally, the `pager_widget` block should only be extended if you need to change the wrapping HTML for the paginator. The `pager` block should still be rendered from your extended block.

The `pager` block is designed to hold the structure of the pager and generally should not be extended unless the intent is to change the logic involved in rendering the paginator (such as removing the ellipsis separators or changing to only display previous/next buttons).

When rendering a Twig view, the following options are passed into the template for use. Note that for the most part, only the `pager` block will use these variables.

- `pagerfanta` - The `Pagerfanta\PagerfantaInterface` object
- `route_generator` - A `Pagerfanta\RouteGenerator\RouteGeneratorDecorator` object which decorates the route generator created by the `pagerfanta()` Twig function
    - The decorator is required because Twig does not allow direct execution of Closures within templates
- `options` - The options array passed through the `pagerfanta()` Twig function
- `start_page` - The calculated start page for the list of items displayed between separators, this is based on the `proximity` option and the total number of pages
- `end_page` - The calculated end page for the list of items displayed between separators, this is based on the `proximity` option and the total number of pages
- `current_page` - The current page in the paginated list
- `nb_pages` - The total number of pages in the paginated list
- `sequential` - Whether the pager is rendered with only previous and next links

When rendering with only previous and next links, the `sequential_pager` block is rendered instead of the numbered page links, and the page number variables are not available. Instead, the following variables are passed into the template:

- `route_generator` - A `Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator` object, whose `route()` method generates the URL for a position
- `supports_backward_navigation` - Whether the pager supports backward navigation, the previous link is omitted when it does not
- `previous_position` - The position of the previous page, or null if there is no previous page
- `next_position` - The position of the next page, or null if there is no next page

<div class="docs-note">The dispatch to the <code>sequential_pager</code> block is part of the <code>pager</code> block. If your template overrides the <code>pager</code> block, render the <code>sequential_pager</code> block from it when the <code>sequential</code> variable is true to support cursor pagers. Templates which only override the markup blocks (such as <code>pager_widget</code>, <code>previous_page_link</code>, and <code>next_page_link</code>) support cursor pagers without any changes.</div>

Additionally, for most page blocks (`previous_page_link`, `page_link`, `current_page_link`, and `next_page_link`), there are two additional variables available:

- `page` - The current page in the pager
- `path` - The generated URL for the item

If you want to create your own Twig template, the quickest and easiest way to do that is to extend one of the supplied templates (typically the default one). Have a look at `semantic_ui.html.twig` to see the blocks you will likely want to override.

### Composing Templates

When using Twig 3.29 or later, the `template` option (and the default template given to the `Pagerfanta\Twig\View\TwigView` constructor) also accepts a list of template names, ordered from highest to lowest precedence. The blocks of these templates are composed together, so a template only needs to define the blocks it overrides and does not need to extend another template. This allows a set of overrides to be reused across any of the supplied templates.

For example, a `pager_messages.html.twig` template containing only the `previous_page_message` and `next_page_message` blocks can be combined with the Bootstrap 5 template:

```twig
{{ pagerfanta(pager, 'twig', {'template': ['pager_messages.html.twig', '@Pagerfanta/twitter_bootstrap5.html.twig']}) }}
```

The default template given to the `Pagerfanta\Twig\View\TwigView` constructor and the `@Pagerfanta/default.html.twig` template are always appended to the list, so any block not defined by the listed templates falls back to them. If a template appears more than once, only its highest precedence position is used.

## Reusable View Configurations

Sometimes you want to reuse options for a view in your project and you don't want to repeat those options each time you render a view, or you have different configurations for a view and you want to save those configurations to be able to change them easily.

For this you can define views with the `Pagerfanta\View\OptionableView` class, which is a decorator for any `Pagerfanta\View\ViewInterface` instance.

```php
<?php

use Pagerfanta\View\DefaultView;
use Pagerfanta\View\OptionableView;

$defaultView = new DefaultView();

$myView1 = new OptionableView($defaultView, ['proximity' => 5]);
$myView2 = new OptionableView($defaultView, ['proximity' => 2, 'prev_message' => 'Anterior', 'next_message' => 'Siguiente']);

$myView1->render($pagerfanta, $routeGenerator);

// Overwriting the optionable view options
$myView2->render($pagerfanta, $routeGenerator, ['next_message' => 'Siguiente!!']);
```
