<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrapTemplate;

class TwitterBootstrapView extends TemplateView
{
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new TwitterBootstrapTemplate();
    }

    protected function getDefaultProximity(): int
    {
        return 3;
    }

    public function getName(): string
    {
        return 'twitter_bootstrap';
    }
}
