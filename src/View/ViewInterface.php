<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
