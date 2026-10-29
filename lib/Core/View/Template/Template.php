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
     * Creates a link to the given position.
     *
     * @throws RuntimeException if the position route generator has not been set
     */
    protected function createLink(LinkRelation $relation, Position $position, string $text, ?string $rel = null): Link
    {
        return new Link($relation, $position, $this->generateRouteForPosition($position), $text, $rel);
    }

    /**
     * Creates a link to the given page by its number.
     *
     * @throws LessThan1CurrentPageException if the page is less than 1
     * @throws RuntimeException              if the position route generator has not been set
     */
    protected function createPageLink(LinkRelation $relation, int $page, string $text, ?string $rel = null): Link
    {
        if ($page < 1) {
            throw new LessThan1CurrentPageException();
        }

        return $this->createLink($relation, new PagePosition($page), $text, $rel);
    }

    /**
     * Returns the additional attributes for a link, override this method to add attributes to the links of a template.
     *
     * Values are escaped when rendered, a true value renders a boolean attribute and a false or null value omits the attribute.
     * The "href", "rel", and "class" attributes are controlled by the template and cannot be set.
     *
     * @return array<string, string|int|bool|null>
     */
    protected function linkAttributes(Link $link): array
    {
        return [];
    }

    /**
     * Renders the additional attributes of a link, with a leading space, for use inside the link's opening tag.
     *
     * @throws InvalidArgumentException if an attribute name is not valid or is controlled by the template
     */
    protected function renderLinkAttributes(Link $link): string
    {
        $html = '';

        foreach ($this->linkAttributes($link) as $name => $value) {
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
