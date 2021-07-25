<?php

namespace Pagerfanta\Twig\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorDecorator;
use Pagerfanta\View\View;
use Twig\Environment;

final class TwigView extends View
{
    public const DEFAULT_TEMPLATE = '@Pagerfanta/default.html.twig';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string|null
     */
    private $defaultTemplate;

    /**
     * @var string
     */
    private $template;

    public function __construct(Environment $twig, ?string $defaultTemplate = null)
    {
        $this->twig = $twig;
        $this->defaultTemplate = $defaultTemplate;
    }

    public function getName()
    {
        return 'twig';
    }

    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = [])
    {
        $this->initializePagerfanta($pagerfanta);
        $this->initializeOptions($options);

        $this->calculateStartAndEndPage();

        return $this->twig->load($this->template)->renderBlock(
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
     * @param callable $routeGenerator
     */
    private function decorateRouteGenerator($routeGenerator): RouteGeneratorDecorator
    {
        if (!\is_callable($routeGenerator)) {
            throw new InvalidArgumentException(sprintf('The route generator for "%s" must be a callable, %s given.', self::class, get_debug_type($routeGenerator)));
        }

        return new RouteGeneratorDecorator($routeGenerator);
    }

    protected function initializeOptions(array $options): void
    {
        if (isset($options['template'])) {
            $this->template = $options['template'];
        } elseif (null !== $this->defaultTemplate) {
            $this->template = $this->defaultTemplate;
        } else {
            $this->template = self::DEFAULT_TEMPLATE;
        }

        parent::initializeOptions($options);
    }
}
