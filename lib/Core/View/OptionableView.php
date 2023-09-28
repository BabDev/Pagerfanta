<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

/**
 * Decorator for a view with a default options list, enables re-use of option configurations.
 */
class OptionableView implements ViewInterface
{
    /**
     * @param array<string, mixed> $defaultOptions
     */
    public function __construct(
        private readonly ViewInterface $view,
        private readonly array $defaultOptions,
    ) {}

    /**
     * @param array<string, mixed> $options
     *
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string
    {
        return $this->view->render($pagerfanta, $routeGenerator, [...$this->defaultOptions, ...$options]);
    }

    public function getName(): string
    {
        return 'optionable';
    }
}
