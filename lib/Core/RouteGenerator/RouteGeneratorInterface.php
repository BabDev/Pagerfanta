<?php declare(strict_types=1);

namespace Pagerfanta\RouteGenerator;

/**
 * @deprecated since Pagerfanta 4.10, implement {@see PositionRouteGeneratorInterface} instead
 */
interface RouteGeneratorInterface
{
    /**
     * Generates the URL for a page item in a paginator.
     *
     * @return string The page URL
     */
    public function __invoke(int $page): string;
}
