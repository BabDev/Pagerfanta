<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TwitterBootstrap4Template;

/**
 * TwitterBootstrap4View.
 *
 * View that can be used with the pagination module
 * from the Twitter Bootstrap4 CSS Toolkit
 * http://getbootstrap.com/
 */
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
