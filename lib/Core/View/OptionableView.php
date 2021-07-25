<?php

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\PagerfantaInterface;

/**
 * Decorator for a view with a default options list, enables re-use of option configurations.
 */
class OptionableView implements ViewInterface
{
    /**
     * @var ViewInterface
     */
    private $view;

    /**
     * @var array
     */
    private $defaultOptions;

    public function __construct(ViewInterface $view, array $defaultOptions)
    {
        $this->view = $view;
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * @throws InvalidArgumentException if the $routeGenerator is not a callable
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = [])
    {
        if (!\is_callable($routeGenerator)) {
            throw new InvalidArgumentException(sprintf('The $routeGenerator argument of %s() must be a callable, %s given.', __METHOD__, get_debug_type($routeGenerator)));
        }

        return $this->view->render($pagerfanta, $routeGenerator, array_merge($this->defaultOptions, $options));
    }

    public function getName()
    {
        return 'optionable';
    }
}
