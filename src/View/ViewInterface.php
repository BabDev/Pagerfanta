<?php

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;

/**
 * ViewInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
interface ViewInterface
{
    /**
     * Renders a pagerfanta.
     *
     * The route generator can be any callable to generate
     * the routes receiving the page number as first and
     * unique argument.
     *
     * @param PagerfantaInterface $pagerfanta     a pagerfanta
     * @param callable            $routeGenerator a callable to generate the routes
     * @param array               $options        an array of options (optional)
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = []);

    /**
     * Returns the canonical name.
     *
     * @return string the canonical name
     */
    public function getName();
}
