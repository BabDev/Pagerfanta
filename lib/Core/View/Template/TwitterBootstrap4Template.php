<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

class TwitterBootstrap4Template extends TwitterBootstrap3Template
{
    #[\Override]
    protected function linkLi(string $class, Link $link): string
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));
        $rel = $link->rel ? \sprintf(' rel="%s"', $link->rel) : '';

        return \sprintf('<li class="%s"><a class="page-link" href="%s"%s%s>%s</a></li>', $liClass, $link->href, $rel, $this->renderLinkAttributes($link), $link->text);
    }

    /**
     * @param int|string $text
     */
    #[\Override]
    protected function spanLi(string $class, $text): string
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));

        return \sprintf('<li class="%s"><span class="page-link">%s</span></li>', $liClass, $text);
    }
}
