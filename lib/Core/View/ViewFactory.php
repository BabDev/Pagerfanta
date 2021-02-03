<?php

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * @final
 */
/* final */ class ViewFactory implements ViewFactoryInterface
{
    /**
     * @var array<string, ViewInterface>
     */
    private $views = [];

    /**
     * @param array<string, ViewInterface> $views
     */
    public function add(array $views): void
    {
        foreach ($views as $name => $view) {
            $this->set($name, $view);
        }
    }

    /**
     * @return array<string, ViewInterface>
     */
    public function all()
    {
        return $this->views;
    }

    public function clear(): void
    {
        $this->views = [];
    }

    /**
     * @param string $name
     *
     * @throws InvalidArgumentException if the view does not exist
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The view "%s" does not exist.', $name));
        }

        return $this->views[$name];
    }

    /**
     * @param string $name
     */
    public function has($name)
    {
        return isset($this->views[$name]);
    }

    /**
     * @param string $name
     *
     * @throws InvalidArgumentException if the view does not exist
     */
    public function remove($name): void
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The view "%s" does not exist.', $name));
        }

        unset($this->views[$name]);
    }

    /**
     * @param string $name
     */
    public function set($name, ViewInterface $view): void
    {
        $this->views[$name] = $view;
    }
}
