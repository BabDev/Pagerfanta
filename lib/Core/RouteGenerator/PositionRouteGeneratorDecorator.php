<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

use Pagerfanta\Position\Position;

/**
 * Adapts a callable to the position based route generator API.
 *
 * In 4.x, a plain callable given as a route generator is always treated as a page number based generator. Wrap a callable
 * accepting a {@see Position} with this decorator to use it as a position based generator.
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
