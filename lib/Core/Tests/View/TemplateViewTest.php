<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
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

    private function createPageRouteGenerator(): PositionRouteGeneratorDecorator
    {
        return new PositionRouteGeneratorDecorator(static fn (Position $position): string => '|'.($position instanceof PagePosition ? $position->page : 'cursor').'|');
    }

    public function testTheOptionsFromAPreviousRenderAreNotReused(): void
    {
        $view = new DefaultView();
        $routeGenerator = $this->createPageRouteGenerator();

        $this->assertStringContainsString('rel="prev">Newer</a>', $view->render($this->createPagerfanta(), $routeGenerator, ['prev_message' => 'Newer']));
        $this->assertStringContainsString('rel="prev">Previous</a>', $view->render($this->createPagerfanta(), $routeGenerator));
    }

    public function testThePagesAreLinkedWithPagePositions(): void
    {
        $this->assertSame(
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="|1|" rel="prev">Previous</a><a class="pagination__item" href="|1|">1</a><span class="pagination__item pagination__item--current-page">2</span><a class="pagination__item" href="|3|">3</a><a class="pagination__item pagination__item--next-page" href="|3|" rel="next">Next</a></nav>',
            (new DefaultView())->render($this->createPagerfanta(), $this->createPageRouteGenerator()),
        );
    }

    public function testTheOptionsSetOnTheTemplateAreKeptForEachRender(): void
    {
        $template = new DefaultTemplate();
        $template->setOptions(['prev_message' => 'Newer']);

        $view = new DefaultView($template);
        $routeGenerator = $this->createPageRouteGenerator();

        $first = $view->render($this->createPagerfanta(), $routeGenerator, ['next_message' => 'Older']);

        $this->assertStringContainsString('rel="prev">Newer</a>', $first);
        $this->assertStringContainsString('rel="next">Older</a>', $first);

        $second = $view->render($this->createPagerfanta(), $routeGenerator);

        $this->assertStringContainsString('rel="prev">Newer</a>', $second);
        $this->assertStringContainsString('rel="next">Next</a>', $second);
    }

    public function testANumberedViewOnlySupportsOffsetPagers(): void
    {
        $view = new DefaultView();

        $this->assertTrue($view->supports($this->createPagerfanta()));
        $this->assertFalse($view->supports(new CursorPagerfanta(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])))));
    }

    public function testANumberedViewCannotRenderACursorPager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "Pagerfanta\\View\\DefaultView" view can only render pagers implementing "Pagerfanta\\OffsetPagerInterface", "Pagerfanta\\CursorPagerfanta" given.');

        (new DefaultView())->render(new CursorPagerfanta(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([]))), $this->createPageRouteGenerator());
    }
}
