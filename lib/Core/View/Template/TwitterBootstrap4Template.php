<?php

namespace Pagerfanta\View\Template;

class TwitterBootstrap4Template extends TwitterBootstrap3Template
{
    protected function linkLi($class, $href, $text, $rel = null): string
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));
        $rel = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf('<li class="%s"><a class="page-link" href="%s"%s>%s</a></li>', $liClass, $href, $rel, $text);
    }

    protected function spanLi($class, $text): string
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));

        return sprintf('<li class="%s"><span class="page-link">%s</span></li>', $liClass, $text);
    }
}
