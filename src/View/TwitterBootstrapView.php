<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TwitterBootstrapTemplate;

/**
 * TwitterBootstrapView.
 *
 * View that can be used with the pagination module
 * from the Twitter Bootstrap CSS Toolkit
 * http://twitter.github.com/bootstrap/
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author Jan Sorgalla <jsorgalla@gmail.com>
 */
class TwitterBootstrapView extends DefaultView
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
