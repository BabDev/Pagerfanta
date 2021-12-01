<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

/**
 * Decorator for a view with a default options list, enables re-use of option configurations.
 */
class OptionableView implements ViewInterface
{
    private ViewInterface $view;
    private array $defaultOptions;

    public function __construct(ViewInterface $view, array $defaultOptions)
    {
        $this->view = $view;
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * @param callable|RouteGeneratorInterface $routeGenerator
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string
    {
        return $this->view->render($pagerfanta, $routeGenerator, array_merge($this->defaultOptions, $options));
    }

    public function getName(): string
    {
        return 'optionable';
    }
}
