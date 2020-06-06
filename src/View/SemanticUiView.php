<?php declare(strict_types=1);

namespace Pagerfanta\View;

use Pagerfanta\View\Template\SemanticUiTemplate;
use Pagerfanta\View\Template\TemplateInterface;

class SemanticUiView extends TemplateView
{
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new SemanticUiTemplate();
    }

    protected function getDefaultProximity(): int
    {
        return 3;
    }

    public function getName(): string
    {
        return 'semantic_ui';
    }
}
