<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

use Pagerfanta\Position\Position;

/**
 * Adapts a callable accepting a {@see Position} to the route generator API.
 */
final class PositionRouteGeneratorDecorator implements PositionRouteGeneratorInterface
{
    /**
     * @var callable(Position): string
     */
    private $decorated;

    /**
     * @param callable(Position $position): string $decorated
     */
    public function __construct(callable $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(Position $position): string
    {
        return $this->route($position);
    }

    public function route(Position $position): string
    {
        $decorated = $this->decorated;

        return $decorated($position);
    }
}
