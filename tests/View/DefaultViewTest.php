<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\View\DefaultView;
use Pagerfanta\View\ViewInterface;

class DefaultViewTest extends ViewTestCase
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

        $this->assertRenderedView(<<<EOF
<nav>
    <a href="|9|" rel="prev">Previous</a>
    <a href="|1|">1</a>
    <span class="dots">...</span>
    <a href="|8|">8</a>
    <a href="|9|">9</a>
    <span class="current">10</span>
    <a href="|11|">11</a>
    <a href="|12|">12</a>
    <span class="dots">...</span>
    <a href="|100|">100</a>
    <a href="|11|" rel="next">Next</a>
</nav>
EOF
            , $this->renderView($options));
    }

    public function testRenderFirstPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [];

        $this->assertRenderedView(<<<EOF
<nav>
    <span class="disabled">Previous</span>
    <span class="current">1</span>
    <a href="|2|">2</a>
    <a href="|3|">3</a>
    <a href="|4|">4</a>
    <a href="|5|">5</a>
    <span class="dots">...</span>
    <a href="|100|">100</a>
    <a href="|2|" rel="next">Next</a>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderLastPage(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(100);

        $options = [];

        $this->assertRenderedView(<<<EOF
<nav>
    <a href="|99|" rel="prev">Previous</a>
    <a href="|1|">1</a>
    <span class="dots">...</span>
    <a href="|96|">96</a>
    <a href="|97|">97</a>
    <a href="|98|">98</a>
    <a href="|99|">99</a>
    <span class="current">100</span>
    <span class="disabled">Next</span>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs2(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(4);

        $options = [];

        $this->assertRenderedView(<<<EOF
<nav>
    <a href="|3|" rel="prev">Previous</a>
    <a href="|1|">1</a>
    <a href="|2|">2</a>
    <a href="|3|">3</a>
    <span class="current">4</span>
    <a href="|5|">5</a>
    <a href="|6|">6</a>
    <span class="dots">...</span>
    <a href="|100|">100</a>
    <a href="|5|" rel="next">Next</a>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderWhenStartProximityIs3(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(5);

        $options = [];

        $this->assertRenderedView(<<<EOF
<nav>
    <a href="|4|" rel="prev">Previous</a>
    <a href="|1|">1</a>
    <a href="|2|">2</a>
    <a href="|3|">3</a>
    <a href="|4|">4</a>
    <span class="current">5</span>
    <a href="|6|">6</a>
    <a href="|7|">7</a>
    <span class="dots">...</span>
    <a href="|100|">100</a>
    <a href="|6|" rel="next">Next</a>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs2FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(97);

        $options = [];

        $this->assertRenderedView(<<<EOF
<nav>
    <a href="|96|" rel="prev">Previous</a>
    <a href="|1|">1</a>
    <span class="dots">...</span>
    <a href="|95|">95</a>
    <a href="|96|">96</a>
    <span class="current">97</span>
    <a href="|98|">98</a>
    <a href="|99|">99</a>
    <a href="|100|">100</a>
    <a href="|98|" rel="next">Next</a>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderWhenEndProximityIs3FromLast(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(96);

        $options = [];

        $this->assertRenderedView(<<<EOF
<nav>
    <a href="|95|" rel="prev">Previous</a>
    <a href="|1|">1</a>
    <span class="dots">...</span>
    <a href="|94|">94</a>
    <a href="|95|">95</a>
    <span class="current">96</span>
    <a href="|97|">97</a>
    <a href="|98|">98</a>
    <a href="|99|">99</a>
    <a href="|100|">100</a>
    <a href="|97|" rel="next">Next</a>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderModifyingProximity(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = ['proximity' => 3];

        $this->assertRenderedView(<<<EOF
<nav>
    <a href="|9|" rel="prev">Previous</a>
    <a href="|1|">1</a>
    <span class="dots">...</span>
    <a href="|7|">7</a>
    <a href="|8|">8</a>
    <a href="|9|">9</a>
    <span class="current">10</span>
    <a href="|11|">11</a>
    <a href="|12|">12</a>
    <a href="|13|">13</a>
    <span class="dots">...</span>
    <a href="|100|">100</a>
    <a href="|11|" rel="next">Next</a>
</nav>
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
<nav>
    <a href="|9|" rel="prev">Anterior</a>
    <a href="|1|">1</a>
    <span class="dots">...</span>
    <a href="|8|">8</a>
    <a href="|9|">9</a>
    <span class="current">10</span>
    <a href="|11|">11</a>
    <a href="|12|">12</a>
    <span class="dots">...</span>
    <a href="|100|">100</a>
    <a href="|11|" rel="next">Siguiente</a>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderModifyingCssClasses(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [
            'css_disabled_class' => 'deshabilitado',
            'css_dots_class' => 'puntos',
            'css_current_class' => 'actual',
        ];

        $this->assertRenderedView(<<<EOF
<nav>
    <span class="deshabilitado">Previous</span>
    <span class="actual">1</span>
    <a href="|2|">2</a>
    <a href="|3|">3</a>
    <a href="|4|">4</a>
    <a href="|5|">5</a>
    <span class="puntos">...</span>
    <a href="|100|">100</a>
    <a href="|2|" rel="next">Next</a>
</nav>
EOF
        , $this->renderView($options));
    }

    public function testRenderModifiyingStringTemplate(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(1);

        $options = [
            'container_template' => '<nav><ul>%pages%</ul></nav>',
            'page_template' => '<li><a href="%href%">%text%</a></li>',
            'span_template' => '<li><span class="%class%">%text%</span></li>',
        ];

        $this->assertRenderedView(<<<EOF
<nav>
    <ul>
        <li><span class="disabled">Previous</span></li>
        <li><span class="current">1</span></li>
        <li><a href="|2|">2</a></li>
        <li><a href="|3|">3</a></li>
        <li><a href="|4|">4</a></li>
        <li><a href="|5|">5</a></li>
        <li><span class="dots">...</span></li>
        <li><a href="|100|">100</a></li>
        <li><a href="|2|">Next</a></li>
    </ul>
</nav>
EOF
        , $this->renderView($options));
    }

    protected function filterExpectedView($expected)
    {
        return $this->removeWhitespacesBetweenTags($expected);
    }
}
