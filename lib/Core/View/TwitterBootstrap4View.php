<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap4Template;

class TwitterBootstrap4View extends TwitterBootstrapView
{
    #[\Override]
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new TwitterBootstrap4Template();
    }

    #[\Override]
    public function getName(): string
    {
        return 'twitter_bootstrap4';
    }
}
