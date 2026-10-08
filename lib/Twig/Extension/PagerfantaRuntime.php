<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Extension;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\OffsetPagerInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface;
use Pagerfanta\View\ViewFactoryInterface;
use Pagerfanta\View\ViewInterface;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class PagerfantaRuntime implements RuntimeExtensionInterface
{
    /**
     * @param string|null $defaultSequentialView The name of the view to render pagers which the default view cannot render (i.e. cursor pagers with a numbered view)
     */
    public function __construct(
        private string $defaultView,
        private ViewFactoryInterface $viewFactory,
        private PositionRouteGeneratorFactoryInterface $routeGeneratorFactory,
        private ?string $defaultSequentialView = null,
    ) {}

    /**
     * @param PagerInterface<mixed, Position>  $pagerfanta
     * @param string|array<string, mixed>|null $viewName   The name of the view to render, or the options array
     * @param array<string, mixed>             $options
     *
     * @throws InvalidArgumentException if the view cannot render the pager
     */
    public function renderPagerfanta(PagerInterface $pagerfanta, string|array|null $viewName = null, array $options = []): string
    {
        if (\is_array($viewName)) {
            $options = $viewName;
            $viewName = null;
        }

        return $this->resolveView($pagerfanta, $viewName ?: null)->render($pagerfanta, $this->routeGeneratorFactory->createPositionRouteGenerator($options), $options);
    }

    /**
     * @param OffsetPagerInterface<mixed> $pagerfanta
     * @param array<string, mixed>        $options
     *
     * @throws OutOfRangeCurrentPageException if the page is out of bounds
     */
    public function getPageUrl(OffsetPagerInterface $pagerfanta, int $page, array $options = []): string
    {
        if ($page < 1 || $page > $pagerfanta->getNbPages()) {
            throw new OutOfRangeCurrentPageException("Page '{$page}' is out of bounds");
        }

        return $this->getPositionUrl(new PagePosition($page), $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws InvalidArgumentException if the position is not supported by the route generator
     */
    public function getPositionUrl(Position $position, array $options = []): string
    {
        $routeGenerator = $this->routeGeneratorFactory->createPositionRouteGenerator($options);

        return $routeGenerator($position);
    }

    /**
     * Resolves the view to render the pager with, falling back to the default sequential view when the default view cannot render it.
     *
     * @param PagerInterface<mixed, Position> $pagerfanta
     *
     * @throws InvalidArgumentException if the view cannot render the pager
     */
    private function resolveView(PagerInterface $pagerfanta, ?string $viewName): ViewInterface
    {
        $view = $this->viewFactory->get($viewName ?? $this->defaultView);

        if ($view->supports($pagerfanta)) {
            return $view;
        }

        if (null === $viewName && null !== $this->defaultSequentialView) {
            $view = $this->viewFactory->get($this->defaultSequentialView);

            if ($view->supports($pagerfanta)) {
                return $view;
            }
        }

        throw new InvalidArgumentException(\sprintf('The "%s" view cannot render a pager of type "%s"%s.', $view->getName(), get_debug_type($pagerfanta), null === $viewName && null === $this->defaultSequentialView ? ', configure a default sequential view to render these pagers' : ''));
    }
}
