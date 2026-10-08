<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

/**
 * In 5.0, the render() method will accept any {@see PagerInterface} and a {@see PositionRouteGeneratorInterface}, and the
 * supports() method will be added. Implement {@see PagerViewInterface} to prepare for this change.
 *
 * @method bool supports(PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager)
 */
interface ViewInterface
{
    /**
     * Passing a page number based route generator is deprecated since 4.10, views should accept a {@see PositionRouteGeneratorInterface}.
     *
     * @param PagerfantaInterface<mixed>                                                    $pagerfanta
     * @param PositionRouteGeneratorInterface|RouteGeneratorInterface|callable(int): string $routeGenerator
     * @param array<string, mixed>                                                          $options
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string;

    public function getName(): string;
}
