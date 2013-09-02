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
 * from the Twitter Bootstrap CSS Toolkit
 * http://twitter.github.com/bootstrap/
 *
 * @author Pablo Díez <pablodip@gmail.com>
 * @author Jan Sorgalla <jsorgalla@gmail.com>
 */
class TwitterBootstrap3View extends DefaultView
{

    protected function createDefaultTemplate()
    {
        return new TwitterBootstrap3Template();
    }

    protected function getDefaultProximity()
    {
        return 4;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twitter_bootstrap3';
    }

}
