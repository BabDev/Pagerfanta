<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Extension;

use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\ViewFactoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class PagerfantaRuntime implements RuntimeExtensionInterface
{
    /**
     * @var string
     */
    private $defaultView;

    /**
     * @var ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var RouteGeneratorFactoryInterface
     */
    private $routeGeneratorFactory;

    public function __construct(string $defaultView, ViewFactoryInterface $viewFactory, RouteGeneratorFactoryInterface $routeGeneratorFactory)
    {
        $this->defaultView = $defaultView;
        $this->viewFactory = $viewFactory;
        $this->routeGeneratorFactory = $routeGeneratorFactory;
    }

    /**
     * @param string|array|null $viewName the view name
     *
     * @throws \InvalidArgumentException if the $viewName argument is an invalid type
     */
    public function renderPagerfanta(PagerfantaInterface $pagerfanta, $viewName = null, array $options = []): string
    {
        if (\is_array($viewName)) {
            [$viewName, $options] = [null, $viewName];
        } elseif (null !== $viewName && !\is_string($viewName)) {
            throw new \InvalidArgumentException(sprintf('The $viewName argument of %s() must be an array, a string, or a null value; a %s was given.', __METHOD__, \gettype($viewName)));
        }

        if (!($pagerfanta instanceof Pagerfanta)) {
            trigger_deprecation(
                'pagerfanta/pagerfanta',
                '2.4',
                'The "pagerfanta" Twig function will no longer accept "%1$s" implementations that are not a subclass of "%2$s" as of 3.0. Ensure your pager is a subclass of "%2$s".',
                PagerfantaInterface::class,
                Pagerfanta::class
            );
        }

        $viewName = $viewName ?: $this->defaultView;

        return $this->viewFactory->get($viewName)->render($pagerfanta, $this->createRouteGenerator($options), $options);
    }

    /**
     * @throws OutOfRangeCurrentPageException if the page is out of bounds
     */
    public function getPageUrl(PagerfantaInterface $pagerfanta, int $page, array $options = []): string
    {
        if (!($pagerfanta instanceof Pagerfanta)) {
            trigger_deprecation(
                'pagerfanta/pagerfanta',
                '2.4',
                'The "pagerfanta_page_url" Twig function will no longer accept "%1$s" implementations that are not a subclass of "%2$s" as of 3.0. Ensure your pager is a subclass of "%2$s".',
                PagerfantaInterface::class,
                Pagerfanta::class
            );
        }

        if ($page < 0 || $page > $pagerfanta->getNbPages()) {
            throw new OutOfRangeCurrentPageException("Page '{$page}' is out of bounds");
        }

        $routeGenerator = $this->createRouteGenerator($options);

        return $routeGenerator($page);
    }

    private function createRouteGenerator(array $options = []): RouteGeneratorInterface
    {
        return $this->routeGeneratorFactory->create($options);
    }
}
