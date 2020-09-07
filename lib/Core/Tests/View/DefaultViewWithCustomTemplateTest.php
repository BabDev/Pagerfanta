<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\View\DefaultView;
use Pagerfanta\View\Template\TwitterBootstrapTemplate;
use Pagerfanta\View\ViewInterface;

final class DefaultViewWithCustomTemplateTest extends ViewTestCase
{
    protected function createView(): ViewInterface
    {
        $template = new TwitterBootstrapTemplate();

        return new DefaultView($template);
    }

    public function testRenderNormal(): void
    {
        $this->setNbPages(100);
        $this->setCurrentPage(10);

        $options = [];

        $this->assertRenderedView(<<<EOF
<div class="pagination">
    <ul>
        <li class="prev"><a href="|9|" rel="prev">Previous</a></li>
        <li class=""><a href="|1|">1</a></li>
        <li class="disabled"><span>&hellip;</span></li>
        <li class=""><a href="|8|">8</a></li>
        <li class=""><a href="|9|">9</a></li>
        <li class="active"><span>10</span></li>
        <li class=""><a href="|11|">11</a></li>
        <li class=""><a href="|12|">12</a></li>
        <li class="disabled"><span>&hellip;</span></li>
        <li class=""><a href="|100|">100</a></li>
        <li class="next"><a href="|11|" rel="next">Next</a></li>
    </ul>
</div>
EOF
        , $this->renderView($options));
    }

    protected function filterExpectedView(string $expected): string
    {
        return $this->removeWhitespacesBetweenTags($expected);
    }
}
