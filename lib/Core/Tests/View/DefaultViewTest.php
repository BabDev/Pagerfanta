<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\View\DefaultView;
use Pagerfanta\View\ViewInterface;

final class DefaultViewTest extends ViewTestCase
{
    protected function createView(): ViewInterface
    {
        return new DefaultView();
    }

    public function testRenderNormal(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|9|" rel="prev">Previous</a>
                <a class="pagination__item" href="|1|">1</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|8|">8</a>
                <a class="pagination__item" href="|9|">9</a>
                <span class="pagination__item pagination__item--current-page">10</span>
                <a class="pagination__item" href="|11|">11</a>
                <a class="pagination__item" href="|12|">12</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|11|" rel="next">Next</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderFirstPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <span class="pagination__item pagination__item--previous-page pagination__item--disabled">Previous</span>
                <span class="pagination__item pagination__item--current-page">1</span>
                <a class="pagination__item" href="|2|">2</a>
                <a class="pagination__item" href="|3|">3</a>
                <a class="pagination__item" href="|4|">4</a>
                <a class="pagination__item" href="|5|">5</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|2|" rel="next">Next</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderLastPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(100);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|99|" rel="prev">Previous</a>
                <a class="pagination__item" href="|1|">1</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|96|">96</a>
                <a class="pagination__item" href="|97|">97</a>
                <a class="pagination__item" href="|98|">98</a>
                <a class="pagination__item" href="|99|">99</a>
                <span class="pagination__item pagination__item--current-page">100</span>
                <span class="pagination__item pagination__item--next-page pagination__item--disabled">Next</span>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs2(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(4);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|3|" rel="prev">Previous</a>
                <a class="pagination__item" href="|1|">1</a>
                <a class="pagination__item" href="|2|">2</a>
                <a class="pagination__item" href="|3|">3</a>
                <span class="pagination__item pagination__item--current-page">4</span>
                <a class="pagination__item" href="|5|">5</a>
                <a class="pagination__item" href="|6|">6</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|5|" rel="next">Next</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs3(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(5);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|4|" rel="prev">Previous</a>
                <a class="pagination__item" href="|1|">1</a>
                <a class="pagination__item" href="|2|">2</a>
                <a class="pagination__item" href="|3|">3</a>
                <a class="pagination__item" href="|4|">4</a>
                <span class="pagination__item pagination__item--current-page">5</span>
                <a class="pagination__item" href="|6|">6</a>
                <a class="pagination__item" href="|7|">7</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|6|" rel="next">Next</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs2FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(97);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|96|" rel="prev">Previous</a>
                <a class="pagination__item" href="|1|">1</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|95|">95</a>
                <a class="pagination__item" href="|96|">96</a>
                <span class="pagination__item pagination__item--current-page">97</span>
                <a class="pagination__item" href="|98|">98</a>
                <a class="pagination__item" href="|99|">99</a>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|98|" rel="next">Next</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs3FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(96);

        $options = [];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|95|" rel="prev">Previous</a>
                <a class="pagination__item" href="|1|">1</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|94|">94</a>
                <a class="pagination__item" href="|95|">95</a>
                <span class="pagination__item pagination__item--current-page">96</span>
                <a class="pagination__item" href="|97|">97</a>
                <a class="pagination__item" href="|98|">98</a>
                <a class="pagination__item" href="|99|">99</a>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|97|" rel="next">Next</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderModifyingProximity(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = ['proximity' => 3];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|9|" rel="prev">Previous</a>
                <a class="pagination__item" href="|1|">1</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|7|">7</a>
                <a class="pagination__item" href="|8|">8</a>
                <a class="pagination__item" href="|9|">9</a>
                <span class="pagination__item pagination__item--current-page">10</span>
                <a class="pagination__item" href="|11|">11</a>
                <a class="pagination__item" href="|12|">12</a>
                <a class="pagination__item" href="|13|">13</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|11|" rel="next">Next</a>
            </nav>
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
            <nav class="pagination">
                <a class="pagination__item pagination__item--previous-page" href="|9|" rel="prev">Anterior</a>
                <a class="pagination__item" href="|1|">1</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|8|">8</a>
                <a class="pagination__item" href="|9|">9</a>
                <span class="pagination__item pagination__item--current-page">10</span>
                <a class="pagination__item" href="|11|">11</a>
                <a class="pagination__item" href="|12|">12</a>
                <span class="pagination__item pagination__item--separator">&hellip;</span>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|11|" rel="next">Siguiente</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderModifyingCssClasses(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [
            'css_active_class' => 'actual',
            'css_disabled_class' => 'deshabilitado',
            'css_dots_class' => 'puntos',
        ];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <span class="pagination__item pagination__item--previous-page deshabilitado">Previous</span>
                <span class="pagination__item actual">1</span>
                <a class="pagination__item" href="|2|">2</a>
                <a class="pagination__item" href="|3|">3</a>
                <a class="pagination__item" href="|4|">4</a>
                <a class="pagination__item" href="|5|">5</a>
                <span class="pagination__item puntos">&hellip;</span>
                <a class="pagination__item" href="|100|">100</a>
                <a class="pagination__item pagination__item--next-page" href="|2|" rel="next">Next</a>
            </nav>
            HTML, $this->renderView($options));
    }

    public function testRenderModifyingStringTemplate(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [
            'container_template' => '<nav class="%s"><ul>%%pages%%</ul></nav>',
            'page_template' => '<li><a class="%class%" href="%href%"%rel%>%text%</a></li>',
            'span_template' => '<li><span class="%class%">%text%</span></li>',
        ];

        $this->assertRenderedView(<<<HTML
            <nav class="pagination">
                <ul>
                    <li><span class="pagination__item pagination__item--previous-page pagination__item--disabled">Previous</span></li>
                    <li><span class="pagination__item pagination__item--current-page">1</span></li>
                    <li><a class="pagination__item" href="|2|">2</a></li>
                    <li><a class="pagination__item" href="|3|">3</a></li>
                    <li><a class="pagination__item" href="|4|">4</a></li>
                    <li><a class="pagination__item" href="|5|">5</a></li>
                    <li><span class="pagination__item pagination__item--separator">&hellip;</span></li>
                    <li><a class="pagination__item" href="|100|">100</a></li>
                    <li><a class="pagination__item pagination__item--next-page" href="|2|" rel="next">Next</a></li>
                </ul>
            </nav>
            HTML, $this->renderView($options));
    }

    protected function filterExpectedView(string $expected): string
    {
        return $this->removeWhitespacesBetweenTags($expected);
    }
}
