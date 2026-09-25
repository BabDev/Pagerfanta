<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\Tests\CapturesDeprecations;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\Template\DefaultTemplate;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class TemplateViewTest extends TestCase
{
    use CapturesDeprecations;

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

    public function testAPositionRouteGeneratorIsGivenPagePositions(): void
    {
        $view = new DefaultView();

        $routeGenerator = new PositionRouteGeneratorDecorator(static fn (Position $position): string => '|'.($position instanceof PagePosition ? $position->page : 'cursor').'|');

        $rendered = null;
        $deprecations = $this->captureDeprecations(function () use ($view, $routeGenerator, &$rendered): void {
            $rendered = $view->render($this->createPagerfanta(), $routeGenerator);
        });

        $this->assertSame([], $deprecations);
        $this->assertSame(
            $this->captureRender($view, static fn (int $page): string => '|'.$page.'|'),
            $rendered,
            'The output matches a page number based route generator',
        );
    }

    #[Group('legacy')]
    public function testAPageNumberRouteGeneratorIsDeprecated(): void
    {
        $deprecations = $this->captureDeprecations(fn () => (new DefaultView())->render($this->createPagerfanta(), static fn (int $page): string => '|'.$page.'|'));

        $this->assertSame(['Since pagerfanta/core 4.10: Passing a page number based route generator to "Pagerfanta\\View\\DefaultView::render()" is deprecated, pass an instance of "Pagerfanta\\RouteGenerator\\PositionRouteGeneratorInterface" instead.'], $deprecations);
    }

    public function testANumberedViewOnlySupportsOffsetPagers(): void
    {
        $view = new DefaultView();

        $this->assertTrue($view->supports($this->createPagerfanta()));
        $this->assertFalse($view->supports(new CursorPagerfanta(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])))));
    }

    /**
     * Renders the view with a page number based route generator, ignoring its deprecation.
     *
     * @param callable(int): string $routeGenerator
     */
    private function captureRender(DefaultView $view, callable $routeGenerator): string
    {
        $rendered = '';

        $this->captureDeprecations(function () use ($view, $routeGenerator, &$rendered): void {
            $rendered = $view->render($this->createPagerfanta(), $routeGenerator);
        });

        return $rendered;
    }
}
