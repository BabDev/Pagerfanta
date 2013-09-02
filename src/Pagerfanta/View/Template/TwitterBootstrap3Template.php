<?php

namespace Pagerfanta\View\Template;

use Pagerfanta\View\Template\TwitterBootstrapTemplate;

class TwitterBootstrap3Template extends TwitterBootstrapTemplate
{

    public function container()
    {
        return sprintf('<ul class="%s">%%pages%%</ul>', $this->option('css_container_class')
        );
    }

}

