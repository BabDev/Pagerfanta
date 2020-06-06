<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\SemanticUiTemplate;

class SemanticUiView extends TemplateView
{
    protected function createDefaultTemplate()
    {
        return new SemanticUiTemplate();
    }

    protected function getDefaultProximity()
    {
        return 3;
    }

    public function getName()
    {
        return 'semantic_ui';
    }
}
