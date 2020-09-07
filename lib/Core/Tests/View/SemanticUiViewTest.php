<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\View\SemanticUiView;
use Pagerfanta\View\ViewInterface;

final class SemanticUiViewTest extends ViewTestCase
{
    protected function createView(): ViewInterface
    {
        return new SemanticUiView();
    }

    public function testRenderNormal(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|9|" rel="prev">Previous</a>
    <a class="item " href="|1|">1</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|7|">7</a>
    <a class="item " href="|8|">8</a>
    <a class="item " href="|9|">9</a>
    <div class="item active">10</div>
    <a class="item " href="|11|">11</a>
    <a class="item " href="|12|">12</a>
    <a class="item " href="|13|">13</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|11|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderFirstPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <div class="item  disabled">Previous</div>
    <div class="item active">1</div>
    <a class="item " href="|2|">2</a>
    <a class="item " href="|3|">3</a>
    <a class="item " href="|4|">4</a>
    <a class="item " href="|5|">5</a>
    <a class="item " href="|6|">6</a>
    <a class="item " href="|7|">7</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|2|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderLastPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(100);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|99|" rel="prev">Previous</a>
    <a class="item " href="|1|">1</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|94|">94</a>
    <a class="item " href="|95|">95</a>
    <a class="item " href="|96|">96</a>
    <a class="item " href="|97|">97</a>
    <a class="item " href="|98|">98</a>
    <a class="item " href="|99|">99</a>
    <div class="item active">100</div>
    <div class="item  disabled">Next</div>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs2(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(4);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|3|" rel="prev">Previous</a>
    <a class="item " href="|1|">1</a><a class="item " href="|2|">2</a>
    <a class="item " href="|3|">3</a>
    <div class="item active">4</div>
    <a class="item " href="|5|">5</a>
    <a class="item " href="|6|">6</a>
    <a class="item " href="|7|">7</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|5|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs3(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(5);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|4|" rel="prev">Previous</a>
    <a class="item " href="|1|">1</a>
    <a class="item " href="|2|">2</a>
    <a class="item " href="|3|">3</a>
    <a class="item " href="|4|">4</a>
    <div class="item active">5</div>
    <a class="item " href="|6|">6</a>
    <a class="item " href="|7|">7</a>
    <a class="item " href="|8|">8</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|6|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs2FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(97);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|96|" rel="prev">Previous</a>
    <a class="item " href="|1|">1</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|94|">94</a>
    <a class="item " href="|95|">95</a>
    <a class="item " href="|96|">96</a>
    <div class="item active">97</div>
    <a class="item " href="|98|">98</a>
    <a class="item " href="|99|">99</a>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|98|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs3FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(96);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|95|" rel="prev">Previous</a>
    <a class="item " href="|1|">1</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|93|">93</a>
    <a class="item " href="|94|">94</a>
    <a class="item " href="|95|">95</a>
    <div class="item active">96</div>
    <a class="item " href="|97|">97</a>
    <a class="item " href="|98|">98</a>
    <a class="item " href="|99|">99</a>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|97|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderModifyingProximity(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = ['proximity' => 2];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|9|" rel="prev">Previous</a>
    <a class="item " href="|1|">1</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|8|">8</a>
    <a class="item " href="|9|">9</a>
    <div class="item active">10</div>
    <a class="item " href="|11|">11</a>
    <a class="item " href="|12|">12</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|11|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderModifyingPreviousAndNextMessages(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = [
            'prev_message' => 'Anterior',
            'next_message' => 'Siguiente',
        ];

        $this->assertRenderedView(<<<EOF
<div class="ui pagination menu">
    <a class="item " href="|9|" rel="prev">Anterior</a>
    <a class="item " href="|1|">1</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|7|">7</a>
    <a class="item " href="|8|">8</a>
    <a class="item " href="|9|">9</a>
    <div class="item active">10</div>
    <a class="item " href="|11|">11</a>
    <a class="item " href="|12|">12</a>
    <a class="item " href="|13|">13</a>
    <div class="item disabled">&hellip;</div>
    <a class="item " href="|100|">100</a>
    <a class="item " href="|11|" rel="next">Siguiente</a>
</div>
EOF
            , $this->renderView($options));
    }

    public function testRenderModifyingCssClasses(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [
            'css_container_class' => 'paginacion',
            'css_item_class' => 'itemo',
            'css_prev_class' => 'anterior',
            'css_next_class' => 'siguiente',
            'css_disabled_class' => 'deshabilitado',
            'css_dots_class' => 'puntos',
            'css_active_class' => 'activo',
        ];

        $this->assertRenderedView(<<<EOF
<div class="paginacion">
    <div class="itemo anterior deshabilitado">Previous</div>
    <div class="itemo activo">1</div>
    <a class="itemo " href="|2|">2</a>
    <a class="itemo " href="|3|">3</a>
    <a class="itemo " href="|4|">4</a>
    <a class="itemo " href="|5|">5</a>
    <a class="itemo " href="|6|">6</a>
    <a class="itemo " href="|7|">7</a>
    <div class="itemo puntos">&hellip;</div>
    <a class="itemo " href="|100|">100</a>
    <a class="itemo siguiente" href="|2|" rel="next">Next</a>
</div>
EOF
            , $this->renderView($options));
    }

    protected function filterExpectedView(string $expected): string
    {
        return $this->removeWhitespacesBetweenTags($expected);
    }
}
