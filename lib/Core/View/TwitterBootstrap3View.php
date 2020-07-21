<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;

class TwitterBootstrap3View extends TwitterBootstrapView
{
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new TwitterBootstrap3Template();
    }

    public function getName(): string
    {
        return 'twitter_bootstrap3';
    }
}
