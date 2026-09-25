<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

/**
 * A view which can render pagers independent of their pagination strategy.
 */
interface PagerViewInterface extends ViewInterface
{
    /**
     * Renders the pager.
     *
     * In 4.x, a callable route generator which is not an instance of {@see PositionRouteGeneratorInterface} is treated as a
     * page number based route generator, which only supports offset pagers.
     *
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position>                    $pager
     * @param PositionRouteGeneratorInterface|RouteGeneratorInterface|callable(int): string $routeGenerator
     * @param array<string, mixed>                                                          $options
     *
     * @throws InvalidArgumentException if the pager is not supported by this view
     */
    public function render(PagerfantaInterface|PagerInterface $pager, callable $routeGenerator, array $options = []): string;

    /**
     * Checks whether this view can render the given pager.
     *
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerfantaInterface|PagerInterface $pager): bool;
}
