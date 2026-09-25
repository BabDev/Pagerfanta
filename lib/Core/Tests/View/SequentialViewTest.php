<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\RuntimeException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\SequentialView;
use Pagerfanta\View\Template\DefaultTemplate;
use Pagerfanta\View\Template\Foundation6Template;
use Pagerfanta\View\Template\SemanticUiTemplate;
use Pagerfanta\View\Template\SequentialTemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Pagerfanta\View\Template\TwitterBootstrap4Template;
use Pagerfanta\View\Template\TwitterBootstrap5Template;
use Pagerfanta\View\Template\TwitterBootstrapTemplate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SequentialViewTest extends TestCase
{
    /**
     * The markup of each theme, as sprintf formats: the container (with the links), the enabled previous and next links (with the URL), and the disabled previous and next links.
     *
     * @return \Generator<string, array{0: \Closure(): SequentialTemplateInterface, 1: string, 2: string, 3: string, 4: string, 5: string}>
     */
    public static function dataThemes(): \Generator
    {
        yield 'default' => [
            static fn (): SequentialTemplateInterface => new DefaultTemplate(),
            '<nav class="pagination">%s</nav>',
            '<a class="pagination__item pagination__item--previous-page" href="%s" rel="prev">Previous</a>',
            '<a class="pagination__item pagination__item--next-page" href="%s" rel="next">Next</a>',
            '<span class="pagination__item pagination__item--previous-page pagination__item--disabled">Previous</span>',
            '<span class="pagination__item pagination__item--next-page pagination__item--disabled">Next</span>',
        ];

        yield 'twitter bootstrap' => [
            static fn (): SequentialTemplateInterface => new TwitterBootstrapTemplate(),
            '<div class="pagination"><ul>%s</ul></div>',
            '<li class=""><a href="%s" rel="prev">Previous</a></li>',
            '<li class=""><a href="%s" rel="next">Next</a></li>',
            '<li class="disabled"><span>Previous</span></li>',
            '<li class="disabled"><span>Next</span></li>',
        ];

        yield 'twitter bootstrap 3' => [
            static fn (): SequentialTemplateInterface => new TwitterBootstrap3Template(),
            '<ul class="pagination">%s</ul>',
            '<li class=""><a href="%s" rel="prev">Previous</a></li>',
            '<li class=""><a href="%s" rel="next">Next</a></li>',
            '<li class="disabled"><span>Previous</span></li>',
            '<li class="disabled"><span>Next</span></li>',
        ];

        yield 'twitter bootstrap 4' => [
            static fn (): SequentialTemplateInterface => new TwitterBootstrap4Template(),
            '<ul class="pagination">%s</ul>',
            '<li class="page-item"><a class="page-link" href="%s" rel="prev">Previous</a></li>',
            '<li class="page-item"><a class="page-link" href="%s" rel="next">Next</a></li>',
            '<li class="page-item  disabled"><span class="page-link">Previous</span></li>',
            '<li class="page-item  disabled"><span class="page-link">Next</span></li>',
        ];

        yield 'twitter bootstrap 5' => [
            static fn (): SequentialTemplateInterface => new TwitterBootstrap5Template(),
            '<ul class="pagination">%s</ul>',
            '<li class="page-item"><a class="page-link" href="%s" rel="prev">Previous</a></li>',
            '<li class="page-item"><a class="page-link" href="%s" rel="next">Next</a></li>',
            '<li class="page-item  disabled"><span class="page-link">Previous</span></li>',
            '<li class="page-item  disabled"><span class="page-link">Next</span></li>',
        ];

        yield 'foundation 6' => [
            static fn (): SequentialTemplateInterface => new Foundation6Template(),
            '<nav aria-label="Pagination"><ul class="pagination">%s</ul></nav>',
            '<li class="pagination-previous"><a href="%s" rel="prev">Previous</a></li>',
            '<li class="pagination-next"><a href="%s" rel="next">Next</a></li>',
            '<li class="pagination-previous disabled">Previous</li>',
            '<li class="pagination-next disabled">Next</li>',
        ];

        yield 'semantic ui' => [
            static fn (): SequentialTemplateInterface => new SemanticUiTemplate(),
            '<div class="ui pagination menu">%s</div>',
            '<a class="item " href="%s" rel="prev">Previous</a>',
            '<a class="item " href="%s" rel="next">Next</a>',
            '<div class="item  disabled">Previous</div>',
            '<div class="item  disabled">Next</div>',
        ];
    }

    /**
     * @return Pagerfanta<int>
     */
    private function createOffsetPager(int $nbResults, int $currentPage): Pagerfanta
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(new ArrayAdapter($nbResults > 0 ? range(1, $nbResults) : []), $currentPage, 10);
    }

    /**
     * Creates a cursor pager whose slice links to the items with IDs 1 (previous) and 3 (next), as enabled.
     *
     * @return CursorPagerfanta<int>
     */
    private function createCursorPager(bool $supportsBackwardNavigation, bool $hasPrevious = true, bool $hasNext = true): CursorPagerfanta
    {
        $adapter = new CallbackCursorAdapter(
            static fn (?Cursor $cursor, int $limit): CursorSlice => new CursorSlice(
                [2],
                $hasPrevious ? new Cursor(['id' => 1], Direction::Previous) : null,
                $hasNext ? new Cursor(['id' => 3]) : null,
            ),
            $supportsBackwardNavigation,
        );

        return new CursorPagerfanta($adapter, 1, new CursorPosition(new Cursor(['id' => 1])));
    }

    private function createPositionRouteGenerator(): PositionRouteGeneratorDecorator
    {
        return new PositionRouteGeneratorDecorator(static fn (Position $position): string => match (true) {
            $position instanceof CursorPosition => \sprintf('|%s:%s|', $position->cursor->direction->name, $position->cursor->fields['id']),
            $position instanceof PagePosition => \sprintf('|page:%d|', $position->page),
            default => throw new \UnexpectedValueException('Unexpected position'),
        });
    }

    /**
     * @param \Closure(): SequentialTemplateInterface $template
     */
    #[DataProvider('dataThemes')]
    public function testAnOffsetPagerIsRenderedWithAPageRouteGenerator(\Closure $template, string $container, string $previous, string $next): void
    {
        $this->assertSame(
            \sprintf($container, \sprintf($previous, '|1|').\sprintf($next, '|3|')),
            (new SequentialView($template()))->render($this->createOffsetPager(30, 2), static fn (int $page): string => '|'.$page.'|'),
        );
    }

    /**
     * @param \Closure(): SequentialTemplateInterface $template
     */
    #[DataProvider('dataThemes')]
    public function testASinglePageRendersDisabledLinks(\Closure $template, string $container, string $previous, string $next, string $previousDisabled, string $nextDisabled): void
    {
        $this->assertSame(
            \sprintf($container, $previousDisabled.$nextDisabled),
            (new SequentialView($template()))->render($this->createOffsetPager(5, 1), static fn (int $page): string => '|'.$page.'|'),
        );
    }

    /**
     * @param \Closure(): SequentialTemplateInterface $template
     */
    #[DataProvider('dataThemes')]
    public function testACursorPagerIsRenderedWithAPositionRouteGenerator(\Closure $template, string $container, string $previous, string $next): void
    {
        $this->assertSame(
            \sprintf($container, \sprintf($previous, '|Previous:1|').\sprintf($next, '|Next:3|')),
            (new SequentialView($template()))->render($this->createCursorPager(true), $this->createPositionRouteGenerator()),
        );
    }

    /**
     * @param \Closure(): SequentialTemplateInterface $template
     */
    #[DataProvider('dataThemes')]
    public function testAForwardOnlyCursorPagerIsRenderedWithoutAPreviousLink(\Closure $template, string $container, string $previous, string $next): void
    {
        $this->assertSame(
            \sprintf($container, \sprintf($next, '|Next:3|')),
            (new SequentialView($template()))->render($this->createCursorPager(false), $this->createPositionRouteGenerator()),
        );
    }

    /**
     * @param \Closure(): SequentialTemplateInterface $template
     */
    #[DataProvider('dataThemes')]
    public function testTheFirstAndLastCursorPagesRenderDisabledLinks(\Closure $template, string $container, string $previous, string $next, string $previousDisabled, string $nextDisabled): void
    {
        $view = new SequentialView($template());

        $this->assertSame(\sprintf($container, $previousDisabled.\sprintf($next, '|Next:3|')), $view->render($this->createCursorPager(true, false), $this->createPositionRouteGenerator()));
        $this->assertSame(\sprintf($container, \sprintf($previous, '|Previous:1|').$nextDisabled), $view->render($this->createCursorPager(true, true, false), $this->createPositionRouteGenerator()));
    }

    public function testThePositionRouteGeneratorIsUsedForAnOffsetPager(): void
    {
        $this->assertSame(
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="|page:1|" rel="prev">Previous</a><a class="pagination__item pagination__item--next-page" href="|page:3|" rel="next">Next</a></nav>',
            (new SequentialView(new DefaultTemplate()))->render($this->createOffsetPager(30, 2), $this->createPositionRouteGenerator()),
        );
    }

    public function testAPageRouteGeneratorCannotRenderACursorPager(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SequentialView(new DefaultTemplate()))->render($this->createCursorPager(true), static fn (int $page): string => '|'.$page.'|');
    }

    public function testAPagerOnlyImplementingTheLegacyInterfaceIsRenderedWithPagePositions(): void
    {
        $pager = $this->createMock(PagerfantaInterface::class);
        $pager->method('hasPreviousPage')->willReturn(true);
        $pager->method('getPreviousPage')->willReturn(4);
        $pager->method('hasNextPage')->willReturn(true);
        $pager->method('getNextPage')->willReturn(6);

        $this->assertSame(
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="|4|" rel="prev">Previous</a><a class="pagination__item pagination__item--next-page" href="|6|" rel="next">Next</a></nav>',
            (new SequentialView(new DefaultTemplate()))->render($pager, static fn (int $page): string => '|'.$page.'|'),
        );
    }

    public function testTheOptionsArePassedToTheTemplate(): void
    {
        $this->assertSame(
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="|1|" rel="prev">Newer</a><a class="pagination__item pagination__item--next-page" href="|3|" rel="next">Older</a></nav>',
            (new SequentialView(new DefaultTemplate()))->render($this->createOffsetPager(30, 2), static fn (int $page): string => '|'.$page.'|', ['prev_message' => 'Newer', 'next_message' => 'Older']),
        );
    }

    public function testATemplateSharedWithANumberedViewKeepsItsRouteGenerators(): void
    {
        $template = new DefaultTemplate();

        (new SequentialView($template))->render($this->createCursorPager(true), $this->createPositionRouteGenerator());

        $this->assertSame(
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="|1|" rel="prev">Previous</a><a class="pagination__item" href="|1|">1</a><span class="pagination__item pagination__item--current-page">2</span><a class="pagination__item" href="|3|">3</a><a class="pagination__item pagination__item--next-page" href="|3|" rel="next">Next</a></nav>',
            (new DefaultView($template))->render($this->createOffsetPager(30, 2), static fn (int $page): string => '|'.$page.'|'),
            'The numbered view uses its own page route generator',
        );
    }

    public function testTheViewSupportsEveryPagerAndHasAConfigurableName(): void
    {
        $this->assertTrue((new SequentialView(new DefaultTemplate()))->supports($this->createCursorPager(true)));
        $this->assertTrue((new SequentialView(new DefaultTemplate()))->supports($this->createOffsetPager(5, 1)));
        $this->assertSame('sequential', (new SequentialView(new DefaultTemplate()))->getName());
        $this->assertSame('twitter_bootstrap5_sequential', (new SequentialView(new TwitterBootstrap5Template(), 'twitter_bootstrap5_sequential'))->getName());
    }

    public function testATemplateCannotRenderAPositionLinkWithoutAPositionRouteGenerator(): void
    {
        $this->expectException(RuntimeException::class);

        (new DefaultTemplate())->nextEnabledForPosition(new PagePosition(2));
    }
}
