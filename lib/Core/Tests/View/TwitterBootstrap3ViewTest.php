<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\View\TwitterBootstrap3View;
use Pagerfanta\View\ViewInterface;

final class TwitterBootstrap3ViewTest extends TwitterBootstrapViewTest
{
    protected function createView(): ViewInterface
    {
        return new TwitterBootstrap3View();
    }

    public function testRenderNormal(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|9|" rel="prev">Previous</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|7|">7</a></li>
                <li class=""><a href="|8|">8</a></li>
                <li class=""><a href="|9|">9</a></li>
                <li class="active"><span>10 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|11|">11</a></li>
                <li class=""><a href="|12|">12</a></li>
                <li class=""><a href="|13|">13</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|11|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderFirstPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class="disabled"><span>Previous</span></li>
                <li class="active"><span>1 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|2|">2</a></li>
                <li class=""><a href="|3|">3</a></li>
                <li class=""><a href="|4|">4</a></li>
                <li class=""><a href="|5|">5</a></li>
                <li class=""><a href="|6|">6</a></li>
                <li class=""><a href="|7|">7</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|2|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderLastPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(100);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|99|" rel="prev">Previous</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|94|">94</a></li>
                <li class=""><a href="|95|">95</a></li>
                <li class=""><a href="|96|">96</a></li>
                <li class=""><a href="|97|">97</a></li>
                <li class=""><a href="|98|">98</a></li>
                <li class=""><a href="|99|">99</a></li>
                <li class="active"><span>100 <span class="sr-only">(current)</span></span></li>
                <li class="disabled"><span>Next</span></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs2(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(4);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|3|" rel="prev">Previous</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class=""><a href="|2|">2</a></li>
                <li class=""><a href="|3|">3</a></li>
                <li class="active"><span>4 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|5|">5</a></li>
                <li class=""><a href="|6|">6</a></li>
                <li class=""><a href="|7|">7</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|5|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs3(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(5);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|4|" rel="prev">Previous</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class=""><a href="|2|">2</a></li>
                <li class=""><a href="|3|">3</a></li>
                <li class=""><a href="|4|">4</a></li>
                <li class="active"><span>5 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|6|">6</a></li>
                <li class=""><a href="|7|">7</a></li>
                <li class=""><a href="|8|">8</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|6|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs2FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(97);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|96|" rel="prev">Previous</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|94|">94</a></li>
                <li class=""><a href="|95|">95</a></li>
                <li class=""><a href="|96|">96</a></li>
                <li class="active"><span>97 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|98|">98</a></li>
                <li class=""><a href="|99|">99</a></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|98|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs3FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(96);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|95|" rel="prev">Previous</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|93|">93</a></li>
                <li class=""><a href="|94|">94</a></li>
                <li class=""><a href="|95|">95</a></li>
                <li class="active"><span>96 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|97|">97</a></li>
                <li class=""><a href="|98|">98</a></li>
                <li class=""><a href="|99|">99</a></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|97|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderModifyingProximity(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = ['proximity' => 2];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|9|" rel="prev">Previous</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|8|">8</a></li>
                <li class=""><a href="|9|">9</a></li>
                <li class="active"><span>10 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|11|">11</a></li>
                <li class=""><a href="|12|">12</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|11|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderModifyingPreviousAndNextMessages(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = [
            'prev_message' => 'Anterior',
            'next_message' => 'Siguiente',
        ];

        $this->assertRenderedView(<<<HTML
            <ul class="pagination">
                <li class=""><a href="|9|" rel="prev">Anterior</a></li>
                <li class=""><a href="|1|">1</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|7|">7</a></li>
                <li class=""><a href="|8|">8</a></li>
                <li class=""><a href="|9|">9</a></li>
                <li class="active"><span>10 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|11|">11</a></li>
                <li class=""><a href="|12|">12</a></li>
                <li class=""><a href="|13|">13</a></li>
                <li class="disabled"><span>&hellip;</span></li>
                <li class=""><a href="|100|">100</a></li>
                <li class=""><a href="|11|" rel="next">Siguiente</a></li>
            </ul>
            HTML, $this->renderView($options));
    }

    public function testRenderModifyingCssClasses(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [
            'css_container_class' => 'paginacion',
            'css_prev_class' => 'anterior',
            'css_next_class' => 'siguiente',
            'css_disabled_class' => 'deshabilitado',
            'css_dots_class' => 'puntos',
            'css_active_class' => 'activo',
        ];

        $this->assertRenderedView(<<<HTML
            <ul class="paginacion">
                <li class="anterior deshabilitado"><span>Previous</span></li>
                <li class="activo"><span>1 <span class="sr-only">(current)</span></span></li>
                <li class=""><a href="|2|">2</a></li>
                <li class=""><a href="|3|">3</a></li>
                <li class=""><a href="|4|">4</a></li>
                <li class=""><a href="|5|">5</a></li>
                <li class=""><a href="|6|">6</a></li>
                <li class=""><a href="|7|">7</a></li>
                <li class="puntos"><span>&hellip;</span></li>
                <li class=""><a href="|100|">100</a></li>
                <li class="siguiente"><a href="|2|" rel="next">Next</a></li>
            </ul>
            HTML, $this->renderView($options));
    }
}
