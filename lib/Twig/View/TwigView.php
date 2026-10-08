<?php declare(strict_types=1);

namespace Pagerfanta\Twig\View;

use Pagerfanta\CursorPagerInterface;
use Pagerfanta\OffsetPagerInterface;
use Pagerfanta\PagerInterface;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageNumberRouteGenerator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\View\View;
use Twig\BlockChain;
use Twig\Environment;
use Twig\TemplateWrapper;

/**
 * View which renders a pager with Twig templates.
 *
 * Pagers implementing {@see OffsetPagerInterface} are rendered with numbered page links, unless the "sequential" option is
 * set. All other pagers are rendered with previous and next links, using the "sequential_pager" block of the template.
 */
final class TwigView extends View
{
    public const string DEFAULT_TEMPLATE = '@Pagerfanta/default.html.twig';

    /**
     * @var string|list<string>
     */
    private string|array $template = self::DEFAULT_TEMPLATE;

    /**
     * @param string|list<string>|null $defaultTemplate A template name, or a list of template names ordered from highest to lowest precedence whose blocks are composed together
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly string|array|null $defaultTemplate = null,
    ) {}

    public function getName(): string
    {
        return 'twig';
    }

    /**
     * @param PagerInterface<mixed, Position> $pager
     */
    #[\Override]
    public function supports(PagerInterface $pager): bool
    {
        return true;
    }

    /**
     * @param PagerInterface<mixed, Position> $pager
     * @param array<string, mixed>            $options
     */
    public function render(PagerInterface $pager, PositionRouteGeneratorInterface $routeGenerator, array $options = []): string
    {
        if (!$pager instanceof OffsetPagerInterface || true === ($options['sequential'] ?? false)) {
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
     * @param PagerInterface<mixed, Position> $pager
     * @param array<string, mixed>            $options
     */
    private function renderSequential(PagerInterface $pager, PositionRouteGeneratorInterface $routeGenerator, array $options): string
    {
        $this->initializeOptions($options);

        $previousPosition = $pager->hasPreviousPage() ? $pager->getPreviousPosition() : null;
        $nextPosition = $pager->hasNextPage() ? $pager->getNextPosition() : null;

        return $this->loadTemplate($this->template)->renderBlock(
            'pager_widget',
            [
                'pagerfanta' => $pager,
                'route_generator' => new PositionRouteGeneratorDecorator($routeGenerator),
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
     */
    private function loadTemplate(string|array $template): TemplateWrapper|BlockChain
    {
        if (\is_string($template)) {
            return $this->twig->load($template);
        }

        // Fall back to the default templates for any block not defined by the given templates, keeping the highest precedence position of repeated templates
        return new BlockChain($this->twig, array_values(array_unique([...$template, ...(array) $this->defaultTemplate, self::DEFAULT_TEMPLATE])));
    }

    /**
     * @param array<string, mixed> $options
     */
    #[\Override]
    protected function initializeOptions(array $options): void
    {
        $this->template = $options['template'] ?? $this->defaultTemplate ?? self::DEFAULT_TEMPLATE;

        parent::initializeOptions($options);
    }
}
