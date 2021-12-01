<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\RuntimeException;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

abstract class Template implements TemplateInterface
{
    private array $options;

    /**
     * @var callable|RouteGeneratorInterface|null
     * @phpstan-var callable(int $page): string|RouteGeneratorInterface|null
     */
    private $routeGenerator;

    public function __construct()
    {
        $this->options = $this->getDefaultOptions();
    }

    /**
     * Sets the route generator used while rendering the template.
     *
     * @param callable|RouteGeneratorInterface $routeGenerator
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function setRouteGenerator(callable $routeGenerator): void
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * Sets the options for the template, overwriting keys that were previously set.
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Generate the route (URL) for the given page.
     */
    protected function generateRoute(int $page): string
    {
        $generator = $this->getRouteGenerator();

        return $generator($page);
    }

    protected function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * @phpstan-return callable(int $page): string|RouteGeneratorInterface
     *
     * @throws RuntimeException if the route generator has not been set
     */
    private function getRouteGenerator(): callable
    {
        if (!$this->routeGenerator) {
            throw new RuntimeException(sprintf('The route generator was not set to the template, ensure you call %s::setRouteGenerator().', static::class));
        }

        return $this->routeGenerator;
    }

    /**
     * @return mixed The option value if it exists
     *
     * @throws InvalidArgumentException if the option does not exist
     */
    protected function option(string $name)
    {
        if (!isset($this->options[$name])) {
            throw new InvalidArgumentException(sprintf('The option "%s" does not exist.', $name));
        }

        return $this->options[$name];
    }
}
