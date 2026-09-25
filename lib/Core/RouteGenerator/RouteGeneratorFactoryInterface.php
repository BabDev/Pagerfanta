<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

use Pagerfanta\Exception\RuntimeException;

/**
 * @deprecated since Pagerfanta 4.10, implement {@see PositionRouteGeneratorFactoryInterface} instead
 */
interface RouteGeneratorFactoryInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @throws RuntimeException if the route generator cannot be created
     */
    public function create(array $options = []): RouteGeneratorInterface;
}
