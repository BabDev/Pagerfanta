<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\OptionableView;
use Pagerfanta\View\SequentialView;
use Pagerfanta\View\Template\DefaultTemplate;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class OptionableViewTest extends TestCase
{
    private const string RENDERED_VIEW = 'rendered';

    /**
     * @var Stub&PagerfantaInterface<mixed>
     */
    private Stub&PagerfantaInterface $pagerfanta;

    private PositionRouteGeneratorInterface $routeGenerator;

    protected function setUp(): void
    {
        $this->pagerfanta = $this->createStub(PagerfantaInterface::class);
        $this->routeGenerator = $this->createPositionRouteGenerator();
    }

    public function testRenderShouldDelegateToTheView(): void
    {
        $defaultOptions = ['foo' => 'bar', 'bar' => 'ups'];

        $this->assertSame(self::RENDERED_VIEW, new OptionableView($this->createViewMock($defaultOptions), $defaultOptions)->render($this->pagerfanta, $this->routeGenerator));
    }

    public function testRenderShouldMergeOptions(): void
    {
        $defaultOptions = ['foo' => 'bar'];
        $options = ['ups' => 'da'];

        $this->assertSame(self::RENDERED_VIEW, new OptionableView($this->createViewMock([...$defaultOptions, ...$options]), $defaultOptions)->render($this->pagerfanta, $this->routeGenerator, $options));
    }

    /**
     * @param array<string, mixed> $expectedOptions
     */
    private function createViewMock(array $expectedOptions): MockObject&ViewInterface
    {
        /** @var MockObject&ViewInterface $view */
        $view = $this->createMock(ViewInterface::class);
        $view->expects($this->once())
            ->method('render')
            ->with($this->pagerfanta, $this->routeGenerator, $expectedOptions)
            ->willReturn(self::RENDERED_VIEW);

        return $view;
    }

    private function createPositionRouteGenerator(): PositionRouteGeneratorDecorator
    {
        return new PositionRouteGeneratorDecorator(static fn (Position $position): string => '|'.($position instanceof PagePosition ? $position->page : 'cursor').'|');
    }

    public function testTheDefaultOptionsAreUsedByTheDecoratedView(): void
    {
        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(new ArrayAdapter(range(1, 30)), 2, 10);

        $this->assertStringContainsString(
            'href="|3|" rel="next">Siguiente</a>',
            new OptionableView(new DefaultView(), ['next_message' => 'Siguiente'])->render($pagerfanta, $this->createPositionRouteGenerator()),
        );
    }

    public function testTheSupportedPagersComeFromTheDecoratedView(): void
    {
        $cursorPager = new CursorPagerfanta(new CallbackCursorAdapter(static fn (): CursorSlice => new CursorSlice([])));

        $this->assertTrue(new OptionableView(new DefaultView(), [])->supports($this->pagerfanta));
        $this->assertFalse(new OptionableView(new DefaultView(), [])->supports($cursorPager));
        $this->assertTrue(new OptionableView(new SequentialView(new DefaultTemplate()), [])->supports($cursorPager));
    }
}
