<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\Pagerfanta;

interface ViewInterface
{
    /**
     * @param callable $routeGenerator callable with a signature of `function (int $page): string {}`
     */
    public function render(Pagerfanta $pagerfanta, callable $routeGenerator, array $options = []): string;

    public function getName(): string;
}
