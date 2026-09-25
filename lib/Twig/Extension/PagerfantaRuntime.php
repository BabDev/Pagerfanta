<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Extension;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageRouteGeneratorWrapper;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\PagerViewInterface;
use Pagerfanta\View\ViewFactoryInterface;
use Pagerfanta\View\ViewInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class PagerfantaRuntime implements RuntimeExtensionInterface
{
    /**
     * @param string|null $defaultSequentialView The name of the view to render pagers which the default view cannot render (i.e. cursor pagers with a numbered view)
     */
    public function __construct(
        private readonly string $defaultView,
        private readonly ViewFactoryInterface $viewFactory,
        private readonly RouteGeneratorFactoryInterface $routeGeneratorFactory,
        private readonly ?string $defaultSequentialView = null,
    ) {}

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pagerfanta
     * @param string|array<string, mixed>|null                           $viewName   The name of the view to render, or the options array
     * @param array<string, mixed>                                       $options
     *
     * @throws InvalidArgumentException if the view cannot render the pager
     */
    public function renderPagerfanta(PagerfantaInterface|PagerInterface $pagerfanta, string|array|null $viewName = null, array $options = []): string
    {
        if (\is_array($viewName)) {
            $options = $viewName;
            $viewName = null;
        }

        $view = $this->resolveView($pagerfanta, $viewName ?: null);

        if ($view instanceof PagerViewInterface) {
            return $view->render($pagerfanta, $this->createPositionRouteGenerator($options), $options);
        }

        \assert($pagerfanta instanceof PagerfantaInterface);

        return $view->render($pagerfanta, $this->createRouteGenerator($options), $options);
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
     *
     * @throws InvalidArgumentException if the position is not supported by the route generator
     */
    public function getPositionUrl(Position $position, array $options = []): string
    {
        $routeGenerator = $this->createPositionRouteGenerator($options);

        return $routeGenerator($position);
    }

    /**
     * Resolves the view to render the pager with, falling back to the default sequential view when the default view cannot render it.
     *
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pagerfanta
     *
     * @throws InvalidArgumentException if the view cannot render the pager
     */
    private function resolveView(PagerfantaInterface|PagerInterface $pagerfanta, ?string $viewName): ViewInterface
    {
        $view = $this->viewFactory->get($viewName ?? $this->defaultView);

        if ($this->viewSupports($view, $pagerfanta)) {
            return $view;
        }

        if (null === $viewName && null !== $this->defaultSequentialView) {
            $view = $this->viewFactory->get($this->defaultSequentialView);

            if ($this->viewSupports($view, $pagerfanta)) {
                return $view;
            }
        }

        throw new InvalidArgumentException(\sprintf('The "%s" view cannot render a pager of type "%s"%s.', $view->getName(), get_debug_type($pagerfanta), null === $viewName && null === $this->defaultSequentialView ? ', configure a default sequential view to render these pagers' : ''));
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pagerfanta
     */
    private function viewSupports(ViewInterface $view, PagerfantaInterface|PagerInterface $pagerfanta): bool
    {
        if ($view instanceof PagerViewInterface) {
            return $view->supports($pagerfanta);
        }

        return $pagerfanta instanceof PagerfantaInterface;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createRouteGenerator(array $options = []): RouteGeneratorInterface
    {
        return $this->routeGeneratorFactory->create($options);
    }

    /**
     * Creates a position route generator if the factory supports it, otherwise a page number based generator is adapted, which only supports offset pagers.
     *
     * @param array<string, mixed> $options
     */
    private function createPositionRouteGenerator(array $options = []): PositionRouteGeneratorInterface
    {
        if ($this->routeGeneratorFactory instanceof PositionRouteGeneratorFactoryInterface) {
            return $this->routeGeneratorFactory->createPositionRouteGenerator($options);
        }

        return PageRouteGeneratorWrapper::wrap($this->createRouteGenerator($options));
    }
}
