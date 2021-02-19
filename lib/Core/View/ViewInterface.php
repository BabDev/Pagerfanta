<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;

interface ViewInterface
{
    /**
     * @param callable             $routeGenerator callable with a signature of `function (int $page): string {}`
     * @param array<string, mixed> $options
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string;

    public function getName(): string;
}
