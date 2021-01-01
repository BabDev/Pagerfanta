<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;

final class ViewFactory implements ViewFactoryInterface
{
    /**
     * @var array<string, ViewInterface>
     */
    private array $views = [];

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
    public function all(): array
    {
        return $this->views;
    }

    public function clear(): void
    {
        $this->views = [];
    }

    /**
     * @throws InvalidArgumentException if the view does not exist
     */
    public function get(string $name): ViewInterface
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The view "%s" does not exist.', $name));
        }

        return $this->views[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->views[$name]);
    }

    public function remove(string $name): void
    {
        unset($this->views[$name]);
    }

    public function set(string $name, ViewInterface $view): void
    {
        $this->views[$name] = $view;
    }
}
