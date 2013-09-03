<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;

/**
 * TwitterBootstrapView.
 *
 * View that can be used with the pagination module
 * from the Twitter Bootstrap3 CSS Toolkit
 * http://getbootstrap.com/
 *
 */
class TwitterBootstrap3View extends DefaultView
{

    protected function createDefaultTemplate()
    {
        return new TwitterBootstrap3Template();
    }

    protected function getDefaultProximity()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twitter_bootstrap3';
    }

}
