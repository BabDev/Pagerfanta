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

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * ViewFactoryInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
interface ViewFactoryInterface
{
    /**
     * Sets a view.
     *
     * @param string        $name the view name
     * @param ViewInterface $view the view
     */
    public function set($name, ViewInterface $view);

    /**
     * Returns whether a view exists or not.
     *
     * @param string $name the name
     *
     * @return bool whether a view exists or not
     */
    public function has($name);

    /**
     * Adds a collection of views.
     *
     * @param array<string, ViewInterface> $views an array of views
     */
    public function add(array $views);

    /**
     * Returns a view.
     *
     * @param string $name the name
     *
     * @return ViewInterface the view
     *
     * @throws InvalidArgumentException if the view does not exist
     */
    public function get($name);

    /**
     * Returns all the views.
     *
     * @return array<string, ViewInterface>
     */
    public function all();

    /**
     * Removes a view.
     *
     * @param string $name the name
     *
     * @throws InvalidArgumentException if the view does not exist
     */
    public function remove($name);

    /**
     * Clears the views.
     */
    public function clear();
}
