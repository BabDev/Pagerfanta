<?php

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;

interface ViewFactoryInterface
{
    /**
     * Adds a collection of views.
     *
     * @param array<string, ViewInterface> $views
     *
     * @return void
     */
    public function add(array $views);

    /**
     * Returns all the views.
     *
     * @return array<string, ViewInterface>
     */
    public function all();

    /**
     * Clears the views.
     *
     * @return void
     */
    public function clear();

    /**
     * Fetches a named view from the factory.
     *
     * @param string $name
     *
     * @return ViewInterface the view
     *
     * @throws InvalidArgumentException if the view does not exist
     */
    public function get($name);

    /**
     * Checks whether a named view is registered to the factory.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Removes a view.
     *
     * @param string $name the name
     *
     * @return void
     *
     * @throws InvalidArgumentException if the view does not exist
     */
    public function remove($name);

    /**
     * Sets a view to the factory.
     *
     * @param string $name
     *
     * @return void
     */
    public function set($name, ViewInterface $view);
}
