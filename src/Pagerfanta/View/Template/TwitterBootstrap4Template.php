<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\View\Template;

/**
 * TwitterBootstrap4Template
 */
class TwitterBootstrap4Template extends TwitterBootstrap3Template
{
    protected function linkLi($class, $href, $text, $rel = null)
    {
        $liClass = implode(' ', array_filter(array('page-item', $class)));
        $rel = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf('<li class="%s"><a class="page-link" href="%s"%s>%s</a></li>', $liClass, $href, $rel, $text);
    }

    protected function spanLi($class, $text)
    {
        $liClass = implode(' ', array_filter(array('page-item', $class)));
        
        return sprintf('<li class="%s"><span class="page-link">%s</span></li>', $liClass, $text);
    }
}
