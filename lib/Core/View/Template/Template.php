<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\RuntimeException;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

abstract class Template implements TemplateInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @var (callable(int): string)|RouteGeneratorInterface|null
     */
    private $routeGenerator;

    private ?PositionRouteGeneratorInterface $positionRouteGenerator = null;

    /**
     * The relation and position of the link being rendered, used to resolve its attributes.
     *
     * @var array{'previous'|'page'|'next', Position}|null
     */
    private ?array $link = null;

    public function __construct()
    {
        $this->options = $this->getDefaultOptions();
    }

    /**
     * Sets the route generator used while rendering the template.
     *
     * @deprecated since Pagerfanta 4.10, to be removed in 5.0.
     *
     * @param callable|RouteGeneratorInterface $routeGenerator
     *
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function setRouteGenerator(callable $routeGenerator): void
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * Sets the position based route generator used while rendering the template.
     *
     * This is used by templates which implement {@see SequentialTemplateInterface}.
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
     */
    protected function generateRoute(int $page): string
    {
        $generator = $this->getRouteGenerator();

        return $generator($page);
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
     * Returns the additional attributes for a link, override this method to add attributes to the links of a template.
     *
     * Values are escaped when rendered, a true value renders a boolean attribute and a false or null value omits the attribute.
     * The "href", "rel", and "class" attributes are controlled by the template and cannot be set.
     *
     * @param 'previous'|'page'|'next' $relation The relation of the link to the current page, the first, last, and numbered page links use "page"
     * @param Position                 $position The position the link points to, numbered page links are given a {@see PagePosition}
     *
     * @return array<string, string|int|bool|null>
     */
    protected function linkAttributes(string $relation, Position $position): array
    {
        return [];
    }

    /**
     * Renders the attributes of the link being rendered, with a leading space, for use inside the link's opening tag.
     *
     * An empty string is returned when called outside of {@see renderLink()}.
     *
     * @throws InvalidArgumentException if an attribute name is not valid or is controlled by the template
     */
    protected function renderLinkAttributes(): string
    {
        if (null === $this->link) {
            return '';
        }

        $html = '';

        foreach ($this->linkAttributes(...$this->link) as $name => $value) {
            $name = (string) $name;

            if (1 !== preg_match('/^[a-zA-Z_:][a-zA-Z0-9_:.-]*$/', $name)) {
                throw new InvalidArgumentException(\sprintf('The link attribute name "%s" is not valid.', $name));
            }

            if (\in_array(strtolower($name), ['href', 'rel', 'class'], true)) {
                throw new InvalidArgumentException(\sprintf('The "%s" link attribute is controlled by the template and cannot be set.', $name));
            }

            if (null === $value || false === $value) {
                continue;
            }

            $html .= true === $value ? ' '.$name : \sprintf(' %s="%s"', $name, htmlspecialchars((string) $value, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8'));
        }

        return $html;
    }

    /**
     * Renders a link, making its relation and position available to {@see renderLinkAttributes()}.
     *
     * When called while another link is being rendered, the outer link is kept.
     *
     * @param 'previous'|'page'|'next' $relation
     * @param \Closure(): string       $render
     */
    protected function renderLink(string $relation, Position $position, \Closure $render): string
    {
        if (null !== $this->link) {
            return $render();
        }

        $this->link = [$relation, $position];

        try {
            return $render();
        } finally {
            $this->link = null;
        }
    }

    /**
     * Renders a link to a page by its number, see {@see renderLink()}.
     *
     * A link to a page less than 1 is rendered without attributes, as it does not have a position.
     *
     * @param 'previous'|'page'|'next' $relation
     * @param \Closure(): string       $render
     */
    protected function renderPageLink(string $relation, int $page, \Closure $render): string
    {
        if ($page < 1) {
            return $render();
        }

        return $this->renderLink($relation, new PagePosition($page), $render);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * @return (callable(int $page): string)|RouteGeneratorInterface
     *
     * @throws RuntimeException if the route generator has not been set
     */
    private function getRouteGenerator(): callable
    {
        if (!$this->routeGenerator) {
            throw new RuntimeException(\sprintf('The route generator was not set to the template, ensure you call %s::setRouteGenerator().', static::class));
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
            throw new InvalidArgumentException(\sprintf('The option "%s" does not exist.', $name));
        }

        return $this->options[$name];
    }
}
