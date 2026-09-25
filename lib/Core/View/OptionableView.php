<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageNumberRouteGenerator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

/**
 * Decorator for a view with a default options list, enables re-use of option configurations.
 */
class OptionableView implements ViewInterface
{
    /**
     * @param array<string, mixed> $defaultOptions
     */
    public function __construct(
        private readonly ViewInterface $view,
        private readonly array $defaultOptions,
    ) {}

    /**
     * @param PagerfantaInterface<mixed>                                                    $pagerfanta
     * @param PositionRouteGeneratorInterface|RouteGeneratorInterface|callable(int): string $routeGenerator
     * @param array<string, mixed>                                                          $options
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string
    {
        // Views which do not accept position route generators are given a page number based route generator
        if ($routeGenerator instanceof PositionRouteGeneratorInterface && !self::acceptsPositionRouteGenerators($this->view)) {
            $routeGenerator = new PageNumberRouteGenerator($routeGenerator);
        }

        return $this->view->render($pagerfanta, $routeGenerator, [...$this->defaultOptions, ...$options]);
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerfantaInterface|PagerInterface $pager): bool
    {
        if ($this->view instanceof PagerViewInterface || $this->view instanceof View || $this->view instanceof self) {
            return $this->view->supports($pager);
        }

        return $pager instanceof PagerfantaInterface;
    }

    public function getName(): string
    {
        return 'optionable';
    }

    /**
     * Checks whether a view is known to accept position route generators.
     *
     * @internal
     */
    public static function acceptsPositionRouteGenerators(ViewInterface $view): bool
    {
        return $view instanceof PagerViewInterface || $view instanceof TemplateView || $view instanceof self;
    }
}
