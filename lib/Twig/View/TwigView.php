<?php declare(strict_types=1);

namespace Pagerfanta\Twig\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\View\View;
use Twig\BlockChain;
use Twig\Environment;
use Twig\TemplateWrapper;

final class TwigView extends View
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
     * @param PagerfantaInterface<mixed>       $pagerfanta
     * @param callable|RouteGeneratorInterface $routeGenerator
     * @param array<string, mixed>             $options
     *
     * @phpstan-param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string
    {
        $this->initializePagerfanta($pagerfanta);
        $this->initializeOptions($options);

        $this->calculateStartAndEndPage();

        return $this->loadTemplate($this->template)->renderBlock(
            'pager_widget',
            [
                'pagerfanta' => $pagerfanta,
                'route_generator' => $this->decorateRouteGenerator($routeGenerator),
                'options' => $options,
                'start_page' => $this->startPage,
                'end_page' => $this->endPage,
                'current_page' => $this->currentPage,
                'nb_pages' => $this->nbPages,
            ]
        );
    }

    /**
     * @param (callable(int $page): string)|RouteGeneratorInterface $routeGenerator
     */
    private function decorateRouteGenerator(callable|RouteGeneratorInterface $routeGenerator): RouteGeneratorDecorator
    {
        return new RouteGeneratorDecorator($routeGenerator);
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
