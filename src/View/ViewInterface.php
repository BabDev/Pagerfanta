<?php

namespace Pagerfanta\View;

use Pagerfanta\Pagerfanta;

interface ViewInterface
{
    /**
     * Renders a Pagerfanta instance.
     *
     * The route generator can be any callable to generate the routes receiving the page number as first and unique argument.
     *
     * @param Pagerfanta $pagerfanta
     * @param callable   $routeGenerator
     * @param array      $options
     */
    public function render(Pagerfanta $pagerfanta, $routeGenerator, array $options = []);

    /**
     * Returns the canonical name.
     *
     * @return string
     */
    public function getName();
}
