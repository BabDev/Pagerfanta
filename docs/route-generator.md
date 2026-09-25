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

## Position Route Generators

<div class="docs-note docs-note--new-feature">Position route generators were introduced in Pagerfanta 4.10.</div>

A page number cannot describe a page of a [cursor pager](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination), so pagers describe the pages they link to with a `Pagerfanta\Position\Position`: either a `Pagerfanta\Position\PagePosition` (holding a page number) or a `Pagerfanta\Position\CursorPosition` (holding a cursor). A position route generator implements `Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface` and builds the URL for a position.

Cursors should be converted to strings with a [cursor encoder](/open-source/packages/pagerfanta/docs/4.x/cursor-pagination#encoding-cursors) when building the URL. A route generator should throw a `Pagerfanta\Exception\InvalidArgumentException` for a position it does not support.

```php
<?php

use Pagerfanta\Cursor\CursorEncoderInterface;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;

final class BlogRouteGenerator implements PositionRouteGeneratorInterface
{
    public function __construct(
        private readonly CursorEncoderInterface $cursorEncoder,
    ) {}

    public function __invoke(Position $position): string
    {
        return match (true) {
            $position instanceof PagePosition => 'http://localhost/blog?page=' . $position->page,
            $position instanceof CursorPosition => 'http://localhost/blog?cursor=' . $this->cursorEncoder->encode($position->cursor),
            default => throw new InvalidArgumentException(sprintf('Unsupported position "%s".', get_debug_type($position))),
        };
    }
}
```

<div class="docs-note">In Pagerfanta 4.x, a plain callable given as a route generator is always treated as a page number based route generator. To use a callable as a position route generator, decorate it with the <code>Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator</code> class, which also provides a <code>route()</code> method for use in Twig templates.</div>

```php
<?php

use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;

$routeGenerator = new PositionRouteGeneratorDecorator(static fn (Position $position): string => /* ... */);
```

A page number based route generator can be used where a position route generator is expected by wrapping it with the `Pagerfanta\RouteGenerator\PageRouteGeneratorWrapper` class. The wrapper only supports page positions, so it can only be used with offset pagers. The `PageRouteGeneratorWrapper::wrap()` method wraps any route generator which is not already a position route generator.

### Position Route Generator Factory

Factories which can create position route generators implement `Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface`.
