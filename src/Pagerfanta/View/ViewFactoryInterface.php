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
     * @param string        $name The view name.
     * @param ViewInterface $view The view.
     */
    public function set($name, ViewInterface $view);

    /**
     * Returns whether a view exists or not.
     *
     * @param string $name The name.
     *
     * @return boolean Whether a view exists or not.
     */
    public function has($name);

    /**
     * Adds views.
     *
     * @param array $views An array of views.
     */
    public function add(array $views);

    /**
     * Returns a view.
     *
     * @param string $name The name.
     *
     * @return ViewInterface The view.
     *
     * @throws \InvalidArgumentException If the view does not exist.
     */
    public function get($name);

    /**
     * Returns all the views.
     *
     * @return array The views.
     */
    public function all();

    /**
     * Removes a view.
     *
     * @param string $name The name.
     *
     * @throws \InvalidArgumentException If the view does not exist.
     */
    public function remove($name);

    /**
     * Clears the views.
     */
    public function clear();
}
