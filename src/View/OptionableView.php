<?php

namespace Pagerfanta\View;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Pagerfanta;
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
        if (!($pagerfanta instanceof Pagerfanta)) {
            trigger_deprecation(
                'babdev/pagerfanta',
                '2.2',
                '%1$s::render() will no longer accept "%2$s" implementations that are not a subclass of "%3$s" as of 3.0. Ensure your pager is a subclass of "%3$s".',
                self::class,
                PagerfantaInterface::class,
                Pagerfanta::class
            );
        }

        if (!is_callable($routeGenerator)) {
            throw new InvalidArgumentException(sprintf('The $routeGenerator argument of %s() must be a callable, a %s was given.', __METHOD__, gettype($routeGenerator)));
        }

        return $this->view->render($pagerfanta, $routeGenerator, array_merge($this->defaultOptions, $options));
    }

    public function getName()
    {
        return 'optionable';
    }
}
