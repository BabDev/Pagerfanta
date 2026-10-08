# Route Generator

Pagerfanta uses a route generator as a mechanism for building URLs to different pages in a paginated list.

A page number cannot describe a page of a [cursor pager](/open-source/packages/pagerfanta/docs/5.x/cursor-pagination), so pagers describe the pages they link to with a `Pagerfanta\Position\Position`: either a `Pagerfanta\Position\PagePosition` (holding a page number) or a `Pagerfanta\Position\CursorPosition` (holding a cursor). A route generator accepts a position and returns the URL for the page being requested.

## Generator Interface

Route generators are classes which implement `Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface`.

Cursors should be converted to strings with a [cursor encoder](/open-source/packages/pagerfanta/docs/5.x/cursor-pagination#encoding-cursors) when building the URL. A route generator should throw a `Pagerfanta\Exception\InvalidArgumentException` for a position it does not support.

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

All of the views provided by Pagerfanta give the route generator a position for each page they link to. The numbered views give the route generator a `Pagerfanta\Position\PagePosition` for each page.

## Generator Decorator

A plain callable is not accepted as a route generator. To use a callable which accepts a position as a route generator, decorate it with the `Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator` class, which also provides a `route()` method for use in Twig templates.

```php
<?php

use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;

$routeGenerator = new PositionRouteGeneratorDecorator(
    static fn (Position $position): string => 'http://localhost/blog?page=' . ($position instanceof PagePosition ? $position->page : 1)
);
```

## Page Number Generator

When only offset pagination is used, the `Pagerfanta\RouteGenerator\PageNumberRouteGenerator` class can be used to generate the URL for a page number with a route generator. It wraps the page number in a `Pagerfanta\Position\PagePosition`, and throws a `Pagerfanta\Exception\LessThan1CurrentPageException` for a page number less than 1.

```php
<?php

use Pagerfanta\RouteGenerator\PageNumberRouteGenerator;

$pageRouteGenerator = new PageNumberRouteGenerator($routeGenerator);

$pageRouteGenerator(2); // Will return the URL for the second page
$pageRouteGenerator->route(2); // The same, for use in Twig templates
```

## Generator Factory

Often, it is necessary to configure a route generator based on runtime information (such as data from the current request). The `Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface` defines a class which can assist in building your route generators.

A basic example of how these factories can be used is with a Twig extension when rendering your pagination list.

```php
<?php

namespace App\Twig;

use Pagerfanta\PagerInterface;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\View\ViewFactoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PagerfantaExtension extends AbstractExtension
{
    public function __construct(
        private readonly PositionRouteGeneratorFactoryInterface $routeGeneratorFactory,
        private readonly ViewFactoryInterface $viewFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pagerfanta', $this->renderPagerfanta(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderPagerfanta(PagerInterface $pager, string $view, array $options = []): string
    {
        return $this->viewFactory->get($view)
            ->render($pager, $this->createRouteGenerator($options), $options);
    }

    private function createRouteGenerator(array $options = []): PositionRouteGeneratorInterface
    {
        return $this->routeGeneratorFactory->createPositionRouteGenerator($options);
    }
}
```
