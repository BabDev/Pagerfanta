<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\View\Foundation6View;
use Pagerfanta\View\ViewInterface;

final class Foundation6ViewTest extends ViewTestCase
{
    protected function createView(): ViewInterface
    {
        return new Foundation6View();
    }

    public function testRenderNormal(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = [];

        $this->assertRenderedView(<<<EOF
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|9|" rel="prev">Previous</a></li>
    <li><a href="|1|">1</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|7|">7</a></li>
    <li><a href="|8|">8</a></li>
    <li><a href="|9|">9</a></li>
    <li class="current">10</li>
    <li><a href="|11|">11</a></li>
    <li><a href="|12|">12</a></li>
    <li><a href="|13|">13</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|11|" rel="next">Next</a></li>
    </ul>
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
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous disabled">Previous</li>
    <li class="current">1</li>
    <li><a href="|2|">2</a></li>
    <li><a href="|3|">3</a></li>
    <li><a href="|4|">4</a></li>
    <li><a href="|5|">5</a></li>
    <li><a href="|6|">6</a></li>
    <li><a href="|7|">7</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|2|" rel="next">Next</a></li>
    </ul>
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
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|99|" rel="prev">Previous</a></li>
    <li><a href="|1|">1</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|94|">94</a></li>
    <li><a href="|95|">95</a></li>
    <li><a href="|96|">96</a></li>
    <li><a href="|97|">97</a></li>
    <li><a href="|98|">98</a></li>
    <li><a href="|99|">99</a></li>
    <li class="current">100</li>
    <li class="pagination-next disabled">Next</li>
    </ul>
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
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|3|" rel="prev">Previous</a></li>
    <li><a href="|1|">1</a></li>
    <li><a href="|2|">2</a></li>
    <li><a href="|3|">3</a></li>
    <li class="current">4</li>
    <li><a href="|5|">5</a></li>
    <li><a href="|6|">6</a></li>
    <li><a href="|7|">7</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|5|" rel="next">Next</a></li>
    </ul>
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
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|4|" rel="prev">Previous</a></li>
    <li><a href="|1|">1</a></li>
    <li><a href="|2|">2</a></li>
    <li><a href="|3|">3</a></li>
    <li><a href="|4|">4</a></li>
    <li class="current">5</li>
    <li><a href="|6|">6</a></li>
    <li><a href="|7|">7</a></li>
    <li><a href="|8|">8</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|6|" rel="next">Next</a></li>
    </ul>
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
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|96|" rel="prev">Previous</a></li>
    <li><a href="|1|">1</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|94|">94</a></li>
    <li><a href="|95|">95</a></li>
    <li><a href="|96|">96</a></li>
    <li class="current">97</li>
    <li><a href="|98|">98</a></li>
    <li><a href="|99|">99</a></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|98|" rel="next">Next</a></li>
    </ul>
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
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|95|" rel="prev">Previous</a></li>
    <li><a href="|1|">1</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|93|">93</a></li>
    <li><a href="|94|">94</a></li>
    <li><a href="|95|">95</a></li>
    <li class="current">96</li>
    <li><a href="|97|">97</a></li>
    <li><a href="|98|">98</a></li>
    <li><a href="|99|">99</a></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|97|" rel="next">Next</a></li>
    </ul>
</nav>
EOF
            , $this->renderView($options));
    }

    public function testRenderModifyingProximity(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = ['proximity' => 2];

        $this->assertRenderedView(<<<EOF
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|9|" rel="prev">Previous</a></li>
    <li><a href="|1|">1</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|8|">8</a></li>
    <li><a href="|9|">9</a></li>
    <li class="current">10</li>
    <li><a href="|11|">11</a></li>
    <li><a href="|12|">12</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|11|" rel="next">Next</a></li>
    </ul>
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
<nav aria-label="Pagination">
    <ul class="pagination">
    <li class="pagination-previous"><a href="|9|" rel="prev">Anterior</a></li>
    <li><a href="|1|">1</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|7|">7</a></li>
    <li><a href="|8|">8</a></li>
    <li><a href="|9|">9</a></li>
    <li class="current">10</li>
    <li><a href="|11|">11</a></li>
    <li><a href="|12|">12</a></li>
    <li><a href="|13|">13</a></li>
    <li aria-hidden="true" class="ellipsis"></li>
    <li><a href="|100|">100</a></li>
    <li class="pagination-next"><a href="|11|" rel="next">Siguiente</a></li>
    </ul>
</nav>
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
<nav aria-label="Pagination">
    <ul class="paginacion">
    <li class="itemo anterior deshabilitado">Previous</li>
    <li class="itemo activo">1</li>
    <li class="itemo"><a href="|2|">2</a></li>
    <li class="itemo"><a href="|3|">3</a></li>
    <li class="itemo"><a href="|4|">4</a></li>
    <li class="itemo"><a href="|5|">5</a></li>
    <li class="itemo"><a href="|6|">6</a></li>
    <li class="itemo"><a href="|7|">7</a></li>
    <li aria-hidden="true" class="itemo puntos"></li>
    <li class="itemo"><a href="|100|">100</a></li>
    <li class="itemo siguiente"><a href="|2|" rel="next">Next</a></li>
    </ul>
</nav>
EOF
            , $this->renderView($options));
    }

    protected function filterExpectedView(string $expected): string
    {
        return $this->removeWhitespacesBetweenTags($expected);
    }
}
