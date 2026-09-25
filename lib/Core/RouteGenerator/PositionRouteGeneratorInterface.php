<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Position\Position;

/**
 * Generates the URL for a position in a paginated list, independent of the pagination strategy.
 */
interface PositionRouteGeneratorInterface
{
    /**
     * @return string The URL for the position
     *
     * @throws InvalidArgumentException if the position is not supported by this generator
     */
    public function __invoke(Position $position): string;
}
