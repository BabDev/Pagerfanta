<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\RuntimeException;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;

abstract class Template implements TemplateInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $options;

    private ?PositionRouteGeneratorInterface $positionRouteGenerator = null;

    public function __construct()
    {
        $this->options = $this->getDefaultOptions();
    }

    /**
     * Sets the position based route generator used while rendering the template.
     */
    public function setPositionRouteGenerator(PositionRouteGeneratorInterface $routeGenerator): void
    {
        $this->positionRouteGenerator = $routeGenerator;
    }

    /**
     * Sets the options for the template, overwriting keys that were previously set.
     *
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Generate the route (URL) for the given page.
     *
     * @throws LessThan1CurrentPageException if the page is less than 1
     * @throws RuntimeException              if the position route generator has not been set
     */
    protected function generateRoute(int $page): string
    {
        if ($page < 1) {
            throw new LessThan1CurrentPageException();
        }

        return $this->generateRouteForPosition(new PagePosition($page));
    }

    /**
     * Generate the route (URL) for the given position.
     *
     * @throws RuntimeException if the position route generator has not been set
     */
    protected function generateRouteForPosition(Position $position): string
    {
        if (!$this->positionRouteGenerator instanceof PositionRouteGeneratorInterface) {
            throw new RuntimeException(\sprintf('The position route generator was not set to the template, ensure you call %s::setPositionRouteGenerator().', static::class));
        }

        return ($this->positionRouteGenerator)($position);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * @return mixed The option value if it exists
     *
     * @throws InvalidArgumentException if the option does not exist
     */
    protected function option(string $name)
    {
        return $this->options[$name] ?? throw new InvalidArgumentException(\sprintf('The option "%s" does not exist.', $name));
    }
}
