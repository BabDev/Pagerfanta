<?php declare(strict_types=1);

namespace Pagerfanta\Twig\View;

use Pagerfanta\CursorPagerInterface;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageNumberRouteGenerator;
use Pagerfanta\RouteGenerator\PageRouteGeneratorWrapper;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\PagerViewInterface;
use Pagerfanta\View\View;
use Twig\BlockChain;
use Twig\Environment;
use Twig\TemplateWrapper;

/**
 * View which renders a pager with Twig templates.
 *
 * Pagers implementing {@see PagerfantaInterface} are rendered with numbered page links, unless the "sequential" option is
 * set. All other pagers are rendered with previous and next links, using the "sequential_pager" block of the template.
 */
final class TwigView extends View implements PagerViewInterface
{
    public const DEFAULT_TEMPLATE = '@Pagerfanta/default.html.twig';

    /**
     * @var string|list<string>
     */
    private string|array $template = self::DEFAULT_TEMPLATE;

    private readonly bool $useBlockChain;

    /**
     * @param string|list<string>|null $defaultTemplate A template name, or a list of template names ordered from highest to lowest precedence whose blocks are composed together (requires twig/twig 3.29 or later)
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly string|array|null $defaultTemplate = null,
    ) {
        $this->useBlockChain = class_exists(BlockChain::class);
    }

    public function getName(): string
    {
        return 'twig';
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position> $pager
     */
    public function supports(PagerfantaInterface|PagerInterface $pager): bool
    {
        return true;
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position>                    $pager
     * @param PositionRouteGeneratorInterface|RouteGeneratorInterface|callable(int): string $routeGenerator
     * @param array<string, mixed>                                                          $options
     */
    public function render(PagerfantaInterface|PagerInterface $pager, callable $routeGenerator, array $options = []): string
    {
        if (!$routeGenerator instanceof PositionRouteGeneratorInterface) {
            trigger_deprecation('pagerfanta/twig', '4.10', 'Passing a page number based route generator to "%s::render()" is deprecated, pass an instance of "%s" instead.', self::class, PositionRouteGeneratorInterface::class);
        }

        if (!$pager instanceof PagerfantaInterface || true === ($options['sequential'] ?? false)) {
            return $this->renderSequential($pager, $routeGenerator, $options);
        }

        $this->initializePagerfanta($pager);
        $this->initializeOptions($options);

        $this->calculateStartAndEndPage();

        return $this->loadTemplate($this->template)->renderBlock(
            'pager_widget',
            [
                'pagerfanta' => $pager,
                'route_generator' => new PageNumberRouteGenerator($routeGenerator),
                'options' => $options,
                'sequential' => false,
                'start_page' => $this->startPage,
                'end_page' => $this->endPage,
                'current_page' => $this->currentPage,
                'nb_pages' => $this->nbPages,
            ]
        );
    }

    /**
     * @param PagerfantaInterface<mixed>|PagerInterface<mixed, Position>                    $pager
     * @param PositionRouteGeneratorInterface|RouteGeneratorInterface|callable(int): string $routeGenerator
     * @param array<string, mixed>                                                          $options
     */
    private function renderSequential(PagerfantaInterface|PagerInterface $pager, callable $routeGenerator, array $options): string
    {
        $this->initializeOptions($options);

        // Implementations of PagerfantaInterface are not required to implement the position API until 5.0
        $previousPosition = match (true) {
            !$pager->hasPreviousPage() => null,
            $pager instanceof PagerInterface => $pager->getPreviousPosition(),
            default => new PagePosition($pager->getPreviousPage()),
        };

        $nextPosition = match (true) {
            !$pager->hasNextPage() => null,
            $pager instanceof PagerInterface => $pager->getNextPosition(),
            default => new PagePosition($pager->getNextPage()),
        };

        return $this->loadTemplate($this->template)->renderBlock(
            'pager_widget',
            [
                'pagerfanta' => $pager,
                'route_generator' => new PositionRouteGeneratorDecorator(PageRouteGeneratorWrapper::wrap($routeGenerator)),
                'options' => $options,
                'sequential' => true,
                'supports_backward_navigation' => !$pager instanceof CursorPagerInterface || $pager->supportsBackwardNavigation(),
                'previous_position' => $previousPosition,
                'next_position' => $nextPosition,
            ]
        );
    }

    /**
     * @param string|list<string> $template
     *
     * @throws \LogicException if a list of templates is given and the installed Twig version does not support block chains
     */
    private function loadTemplate(string|array $template): TemplateWrapper|BlockChain
    {
        if (\is_string($template)) {
            return $this->twig->load($template);
        }

        if (!$this->useBlockChain) {
            throw new \LogicException('Rendering a pager with a list of templates requires twig/twig 3.29 or later, try running "composer require twig/twig:^3.29".');
        }

        // Fall back to the default templates for any block not defined by the given templates, keeping the highest precedence position of repeated templates
        return new BlockChain($this->twig, array_values(array_unique([...$template, ...(array) $this->defaultTemplate, self::DEFAULT_TEMPLATE])));
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function initializeOptions(array $options): void
    {
        $this->template = $options['template'] ?? $this->defaultTemplate ?? self::DEFAULT_TEMPLATE;

        parent::initializeOptions($options);
    }
}
