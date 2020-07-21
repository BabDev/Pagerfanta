<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TwitterBootstrapTemplate;

class TwitterBootstrapView extends TemplateView
{
    protected function createDefaultTemplate()
    {
        return new TwitterBootstrapTemplate();
    }

    protected function getDefaultProximity()
    {
        return 3;
    }

    public function getName()
    {
        return 'twitter_bootstrap';
    }
}
