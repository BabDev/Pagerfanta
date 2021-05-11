<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\View\Template\Foundation6Template;
use Pagerfanta\View\Template\TemplateInterface;

class Foundation6View extends TemplateView
{
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new Foundation6Template();
    }

    protected function getDefaultProximity(): int
    {
        return 3;
    }

    public function getName(): string
    {
        return 'foundation6';
    }
}
