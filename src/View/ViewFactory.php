<?php

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;

/**
 * ViewFactory.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @final
 */
/* final */class ViewFactory implements ViewFactoryInterface
{
    /**
     * @var array<string, ViewInterface>
     */
    private $views = [];

    public function set($name, ViewInterface $view): void
    {
        $this->views[$name] = $view;
    }

    public function has($name)
    {
        return isset($this->views[$name]);
    }

    public function add(array $views): void
    {
        foreach ($views as $name => $view) {
            $this->set($name, $view);
        }
    }

    public function get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The view "%s" does not exist.', $name));
        }

        return $this->views[$name];
    }

    public function remove($name): void
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The view "%s" does not exist.', $name));
        }

        unset($this->views[$name]);
    }

    public function all()
    {
        return $this->views;
    }

    public function clear(): void
    {
        $this->views = [];
    }
}
