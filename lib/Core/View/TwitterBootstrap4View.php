<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TwitterBootstrap4Template;

class TwitterBootstrap4View extends TwitterBootstrapView
{
    protected function createDefaultTemplate()
    {
        return new TwitterBootstrap4Template();
    }

    public function getName()
    {
        return 'twitter_bootstrap4';
    }
}
