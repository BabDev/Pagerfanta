<?php

namespace Pagerfanta\View;

use Pagerfanta\View\Template\SemanticUiTemplate;

/**
 * SemanticUiView.
 *
 * View that can be used with the pagination module
 * from the Semantic UI CSS Toolkit
 * http://semantic-ui.com/
 *
 * @author Loïc Frémont <loic@mobizel.com>
 */
class SemanticUiView extends DefaultView
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
