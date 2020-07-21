<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap4Template;

class TwitterBootstrap4View extends TwitterBootstrapView
{
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new TwitterBootstrap4Template();
    }

    public function getName(): string
    {
        return 'twitter_bootstrap4';
    }
}
