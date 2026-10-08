<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Position\PagePosition;

/**
 * Generates the URL for a page by its number using a position based route generator.
 */
final readonly class PageNumberRouteGenerator
{
    public function __construct(
        private PositionRouteGeneratorInterface $routeGenerator,
    ) {}

    /**
     * @throws LessThan1CurrentPageException if the page is less than 1
     */
    public function __invoke(int $page): string
    {
        return $this->route($page);
    }

    /**
     * @throws LessThan1CurrentPageException if the page is less than 1
     */
    public function route(int $page): string
    {
        if ($page < 1) {
            throw new LessThan1CurrentPageException();
        }

        return ($this->routeGenerator)(new PagePosition($page));
    }
}
