<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Extension;

use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\ViewFactoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class PagerfantaRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly string $defaultView,
        private readonly ViewFactoryInterface $viewFactory,
        private readonly RouteGeneratorFactoryInterface $routeGeneratorFactory,
    ) {}

    /**
     * @param PagerfantaInterface<mixed>       $pagerfanta
     * @param string|array<string, mixed>|null $viewName   The name of the view to render, or the options array
     * @param array<string, mixed>             $options
     */
    public function renderPagerfanta(PagerfantaInterface $pagerfanta, string|array|null $viewName = null, array $options = []): string
    {
        if (\is_array($viewName)) {
            $options = $viewName;
            $viewName = null;
        }

        $viewName = $viewName ?: $this->defaultView;

        return $this->viewFactory->get($viewName)->render($pagerfanta, $this->createRouteGenerator($options), $options);
    }

    /**
     * @param PagerfantaInterface<mixed> $pagerfanta
     * @param array<string, mixed>       $options
     *
     * @throws OutOfRangeCurrentPageException if the page is out of bounds
     */
    public function getPageUrl(PagerfantaInterface $pagerfanta, int $page, array $options = []): string
    {
        if ($page < 0 || $page > $pagerfanta->getNbPages()) {
            throw new OutOfRangeCurrentPageException("Page '{$page}' is out of bounds");
        }

        $routeGenerator = $this->createRouteGenerator($options);

        return $routeGenerator($page);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createRouteGenerator(array $options = []): RouteGeneratorInterface
    {
        return $this->routeGeneratorFactory->create($options);
    }
}
