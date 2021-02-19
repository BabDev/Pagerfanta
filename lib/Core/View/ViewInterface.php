<?php

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;

interface ViewInterface
{
    /**
     * Renders a Pagerfanta instance.
     *
     * The route generator can be any callable to generate the routes receiving the page number as first and unique argument.
     *
     * @param callable             $routeGenerator
     * @param array<string, mixed> $options
     *
     * @return string
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = []);

    /**
     * Returns the canonical name.
     *
     * @return string
     */
    public function getName();
}
