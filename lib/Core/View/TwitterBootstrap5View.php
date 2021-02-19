<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap5Template;

class TwitterBootstrap5View extends TwitterBootstrapView
{
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new TwitterBootstrap5Template();
    }

    public function getName(): string
    {
        return 'twitter_bootstrap5';
    }
}
