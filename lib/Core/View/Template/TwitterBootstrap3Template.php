<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

class TwitterBootstrap3Template extends TwitterBootstrapTemplate
{
    protected function getDefaultOptions(): array
    {
        return array_merge(
            parent::getDefaultOptions(),
            [
                'active_suffix' => '<span class="sr-only">(current)</span>',
                'container_template' => '<ul class="%s">%%pages%%</ul>',
            ]
        );
    }
}
