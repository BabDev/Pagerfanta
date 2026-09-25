<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\Template\DefaultTemplate;
use PHPUnit\Framework\TestCase;

final class TemplateViewTest extends TestCase
{
    /**
     * @return Pagerfanta<int>
     */
    private function createPagerfanta(): Pagerfanta
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(new ArrayAdapter(range(1, 30)), 2, 10);
    }

    public function testTheOptionsFromAPreviousRenderAreNotReused(): void
    {
        $view = new DefaultView();
        $routeGenerator = static fn (int $page): string => '|'.$page.'|';

        $this->assertStringContainsString('rel="prev">Newer</a>', $view->render($this->createPagerfanta(), $routeGenerator, ['prev_message' => 'Newer']));
        $this->assertStringContainsString('rel="prev">Previous</a>', $view->render($this->createPagerfanta(), $routeGenerator));
    }

    public function testTheOptionsSetOnTheTemplateAreKeptForEachRender(): void
    {
        $template = new DefaultTemplate();
        $template->setOptions(['prev_message' => 'Newer']);

        $view = new DefaultView($template);
        $routeGenerator = static fn (int $page): string => '|'.$page.'|';

        $first = $view->render($this->createPagerfanta(), $routeGenerator, ['next_message' => 'Older']);

        $this->assertStringContainsString('rel="prev">Newer</a>', $first);
        $this->assertStringContainsString('rel="next">Older</a>', $first);

        $second = $view->render($this->createPagerfanta(), $routeGenerator);

        $this->assertStringContainsString('rel="prev">Newer</a>', $second);
        $this->assertStringContainsString('rel="next">Next</a>', $second);
    }
}
