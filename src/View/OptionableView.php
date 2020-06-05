<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\View;

use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

/**
 * OptionableView.
 *
 * This view renders another view with a default options to reuse them in a project.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class OptionableView implements ViewInterface
{
    private $view;
    private $defaultOptions;

    /**
     * Constructor.
     *
     * @param ViewInterface $view           a view
     * @param array         $defaultOptions an array of default options
     */
    public function __construct(ViewInterface $view, array $defaultOptions)
    {
        $this->view = $view;
        $this->defaultOptions = $defaultOptions;
    }

    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = [])
    {
        if (!($pagerfanta instanceof Pagerfanta)) {
            trigger_deprecation(
                'pagerfanta/pagerfanta',
                '2.2',
                '%1$s::render() will no longer accept "%2$s" implementations that are not a subclass of "%3$s" as of 3.0. Ensure your pager is a subclass of "%3$s".',
                self::class,
                PagerfantaInterface::class,
                Pagerfanta::class
            );
        }

        return $this->view->render($pagerfanta, $routeGenerator, array_merge($this->defaultOptions, $options));
    }

    public function getName()
    {
        return 'optionable';
    }
}
