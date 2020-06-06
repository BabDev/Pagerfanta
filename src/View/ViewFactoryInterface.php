<?php

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;

interface ViewFactoryInterface
{
    public function set(string $name, ViewInterface $view): void;

    public function has(string $name): bool;

    /**
     * @param array<string, ViewInterface> $views
     */
    public function add(array $views): void;

    /**
     * @throws InvalidArgumentException if the view does not exist
     */
    public function get(string $name): ViewInterface;

    /**
     * @return array<string, ViewInterface>
     */
    public function all(): array;

    /**
     * @throws InvalidArgumentException if the view does not exist
     */
    public function remove(string $name): void;

    public function clear(): void;
}
