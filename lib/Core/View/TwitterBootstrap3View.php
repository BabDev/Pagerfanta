<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TwitterBootstrap3Template;

class TwitterBootstrap3View extends TwitterBootstrapView
{
    protected function createDefaultTemplate()
    {
        return new TwitterBootstrap3Template();
    }

    public function getName()
    {
        return 'twitter_bootstrap3';
    }
}
