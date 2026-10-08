<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;

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
     * @param PagerInterface<mixed, Position> $pager
     * @param array<string, mixed>            $options
     */
    public function render(PagerInterface $pager, PositionRouteGeneratorInterface $routeGenerator, array $options = []): string
    {
        return $this->view->render($pager, $routeGenerator, [...$this->defaultOptions, ...$options]);
    }

    /**
     * @param PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerInterface $pager): bool
    {
        return $this->view->supports($pager);
    }

    public function getName(): string
    {
        return 'optionable';
    }
}
