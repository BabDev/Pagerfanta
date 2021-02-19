<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TwitterBootstrap5Template;

class TwitterBootstrap5View extends TwitterBootstrapView
{
    protected function createDefaultTemplate()
    {
        return new TwitterBootstrap5Template();
    }

    public function getName()
    {
        return 'twitter_bootstrap5';
    }
}
