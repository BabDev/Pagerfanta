<?php

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;

interface ViewFactoryInterface
{
    /**
     * Sets a view to the factory.
     *
     * @param string        $name
     * @param ViewInterface $view
     */
    public function set($name, ViewInterface $view);

    /**
     * Checks whether a named view is registered to the factory.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Adds a collection of views.
     *
     * @param array<string, ViewInterface> $views
     */
    public function add(array $views);

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
