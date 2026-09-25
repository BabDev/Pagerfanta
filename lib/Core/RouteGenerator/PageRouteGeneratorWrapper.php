<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;

/**
 * Adapts a page number based route generator to the position based API.
 *
 * Only {@see PagePosition} positions are supported.
 *
 * @internal
 */
final class PageRouteGeneratorWrapper implements PositionRouteGeneratorInterface
{
    /**
     * @var callable(int): string
     */
    private $decorated;

    /**
     * @param callable(int $page): string $decorated
     */
    public function __construct(callable $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * Wraps the given route generator if it is not already position based.
     *
     * Callables which are not an instance of {@see PositionRouteGeneratorInterface} are treated as page number based generators.
     *
     * @param PositionRouteGeneratorInterface|(callable(int $page): string) $routeGenerator
     */
    public static function wrap(PositionRouteGeneratorInterface|callable $routeGenerator): PositionRouteGeneratorInterface
    {
        if ($routeGenerator instanceof PositionRouteGeneratorInterface) {
            return $routeGenerator;
        }

        return new self($routeGenerator);
    }

    /**
     * @throws InvalidArgumentException if the position is not a page position
     */
    public function __invoke(Position $position): string
    {
        if (!$position instanceof PagePosition) {
            throw new InvalidArgumentException(\sprintf('The "%s" route generator only supports "%s" positions, "%s" given.', self::class, PagePosition::class, get_debug_type($position)));
        }

        $decorated = $this->decorated;

        return $decorated($position->page);
    }
}
