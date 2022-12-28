<?php

namespace Pagerfanta\View\Template;

class TwitterBootstrap5Template extends TwitterBootstrap4Template
{
    /**
     * @return array<string, string>
     */
    protected function getDefaultOptions(): array
    {
        return array_merge(
            parent::getDefaultOptions(),
            [
                'active_suffix' => '<span class="visually-hidden">(current)</span>',
            ]
        );
    }
}
