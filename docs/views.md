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
     * Renders a Pagerfanta instance.
     *
     * The route generator can be any callable to generate the routes receiving the page number as first and unique argument.
     *
     * @param PagerfantaInterface $pagerfanta
     * @param callable            $routeGenerator
     * @param array               $options
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = []);

    /**
     * Returns the canonical name.
     *
     * @return string
     */
    public function getName();
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
| -------------------- | --------------------------------------- | ---------------------------------------------------- |
| `default`            | `Pagerfanta\View\DefaultView`           | `Pagerfanta\View\Template\DefaultTemplate`           |
| `semantic_ui`        | `Pagerfanta\View\SemanticUiView`        | `Pagerfanta\View\Template\SemanticUiTemplate`        |
| `twitter_bootstrap`  | `Pagerfanta\View\TwitterBootstrapView`  | `Pagerfanta\View\Template\TwitterBootstrapTemplate`  |
| `twitter_bootstrap3` | `Pagerfanta\View\TwitterBootstrap3View` | `Pagerfanta\View\Template\TwitterBootstrap3Template` |
| `twitter_bootstrap4` | `Pagerfanta\View\TwitterBootstrap4View` | `Pagerfanta\View\Template\TwitterBootstrap4Template` |

## Reusable View Configurations

Sometimes you want to reuse options for a view in your project and you don't want to repeat those options each time you render a view, or you have different configurations for a view and you want to save those configurations to be able to change them easily.

For this you can define views with the `Pagerfanta\View\OptionableView` class, which is a decorator for any `Pagerfanta\View\ViewInterface` instance.

```php
<?php

use Pagerfanta\DefaultView;
use Pagerfanta\OptionableView;

$defaultView = new DefaultView();

$myView1 = new OptionableView($defaultView, ['proximity' => 5]);
$myView2 = new OptionableView($defaultView, ['proximity' => 2, 'prev_message' => 'Anterior', 'next_message' => 'Siguiente']);

$myView1->render($pagerfanta, $routeGenerator);

// Overwriting the optionable view options
$myView2->render($pagerfanta, $routeGenerator, ['next_message' => 'Siguiente!!']);
```
