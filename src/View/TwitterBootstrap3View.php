<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TwitterBootstrap3Template;

/**
 * TwitterBootstrap3View.
 *
 * View that can be used with the pagination module
 * from the Twitter Bootstrap3 CSS Toolkit
 * http://getbootstrap.com/
 */
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
