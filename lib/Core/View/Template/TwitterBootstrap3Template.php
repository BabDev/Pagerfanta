<?php

namespace Pagerfanta\View\Template;

class TwitterBootstrap3Template extends TwitterBootstrapTemplate
{
    protected function getDefaultOptions(): array
    {
        return array_merge(
            parent::getDefaultOptions(),
            [
                'active_suffix' => '<span class="sr-only">(current)</span>',
            ]
        );
    }

    public function container(): string
    {
        return sprintf('<ul class="%s">%%pages%%</ul>',
            $this->option('css_container_class')
        );
    }
}
