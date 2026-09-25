<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Position\PagePosition;

/**
 * Generates the URL for a page by its number, from either a position based route generator or a page number based route generator.
 *
 * @internal
 */
final class PageNumberRouteGenerator
{
    /**
     * @var PositionRouteGeneratorInterface|callable(int): string
     */
    private $routeGenerator;

    /**
     * @param PositionRouteGeneratorInterface|callable(int $page): string $routeGenerator
     */
    public function __construct(PositionRouteGeneratorInterface|callable $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @throws LessThan1CurrentPageException if the page is less than 1 and the route generator is position based
     */
    public function __invoke(int $page): string
    {
        return $this->route($page);
    }

    /**
     * @throws LessThan1CurrentPageException if the page is less than 1 and the route generator is position based
     */
    public function route(int $page): string
    {
        $routeGenerator = $this->routeGenerator;

        if (!$routeGenerator instanceof PositionRouteGeneratorInterface) {
            return $routeGenerator($page);
        }

        if ($page < 1) {
            throw new LessThan1CurrentPageException();
        }

        return $routeGenerator(new PagePosition($page));
    }
}
