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
     * @param callable $routeGenerator callable with a signature of `function (int $page): string {}`
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string;

    public function getName(): string;
}
```

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

Additionally, for most page blocks (`previous_page_link`, `page_link`, `current_page_link`, and `next_page_link`), there are two additional variables available:

- `page` - The current page in the pager
- `path` - The generated URL for the item

If you want to create your own Twig template, the quickest and easiest way to do that is to extend one of the supplied templates (typically the default one). Have a look at `semantic_ui.html.twig` to see the blocks you will likely want to override.

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
