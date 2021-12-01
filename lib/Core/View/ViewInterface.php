<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

interface ViewInterface
{
    /**
     * @param PagerfantaInterface<mixed>       $pagerfanta
     * @param callable|RouteGeneratorInterface $routeGenerator
     * @param array<string, mixed>             $options
     *
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string;

    public function getName(): string;
}
