# Route Generator

Pagerfanta uses a route generator as a mechanism for building URLs to different pages in a paginated list.

A route generator is any callable which accepts a single `$page` parameter (the page to build the URL for) and returns the URL for the page being requested.

```php
$routeGenerator = static fn (int $page): string => 'http://localhost/blog?page=' . $page;
```

## Generator Interface

It is recommended that route generators are classes which implement `Pagerfanta\RouteGenerator\RouteGeneratorInterface`.

## Generator Factory

Often, it is necessary to configure a route generator based on runtime information (such as data from the current request). The `Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface` defines a class which can assist in building your route generators.

A basic example of how these factories can be used is with a Twig extension when rendering your pagination list.

```php
<?php

namespace App\Twig;

use Pagerfanta\Pagerfanta;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\ViewFactoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PagerfantaExtension extends AbstractExtension
{
    public function __construct(
        private readonly RouteGeneratorFactoryInterface $routeGeneratorFactory,
        private readonly ViewFactoryInterface $viewFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pagerfanta', $this->renderPagerfanta(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderPagerfanta(Pagerfanta $pagerfanta, string $view, array $options = []): string
    {
        return $this->viewFactory->get($view)
            ->render($pagerfanta, $this->createRouteGenerator($options), $options);
    }

    private function createRouteGenerator(array $options = []): RouteGeneratorInterface
    {
        return $this->routeGeneratorFactory->create($options);
    }
}
```

## Generator Decorator

Included in the core API is the `Pagerfanta\RouteGenerator\RouteGeneratorDecorator` class which can be used to decorate any route generator, whether the generator implements the interface or any callable.

The primary reason this class was created is to allow any generator to be used within Twig, but the decorator can also be used to enforce strict typehinting for generators.

<div class="docs-note docs-note--tip">When using the Twig view, it will automatically decorate any route generator so you will not need to do this on your own.</div>
