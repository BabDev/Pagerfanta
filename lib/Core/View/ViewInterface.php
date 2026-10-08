<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;

interface ViewInterface
{
    /**
     * @param PagerInterface<mixed, Position> $pager
     * @param array<string, mixed>            $options
     *
     * @throws InvalidArgumentException if the pager is not supported by this view
     */
    public function render(PagerInterface $pager, PositionRouteGeneratorInterface $routeGenerator, array $options = []): string;

    /**
     * @param PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerInterface $pager): bool;

    public function getName(): string;
}
