<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Tests\Extension;

use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\Twig\Tests\CapturesDeprecations;
use Pagerfanta\Twig\Extension\PagerfantaRuntime;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\SequentialView;
use Pagerfanta\View\Template\DefaultTemplate;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\Attributes\Group;
use Pagerfanta\View\ViewFactory;
use PHPUnit\Framework\TestCase;

final class PagerfantaRuntimeTest extends TestCase
{
    use CapturesDeprecations;

    private PagerfantaRuntime $extension;

    protected function setUp(): void
    {
        $viewFactory = new ViewFactory();
        $viewFactory->set('default', new DefaultView());

        $routeGeneratorFactory = $this->createRouteGeneratorFactory();

        $this->extension = new PagerfantaRuntime(
            'default',
            $viewFactory,
            $routeGeneratorFactory
        );
    }

    private function createRouteGeneratorFactory(): RouteGeneratorFactoryInterface
    {
        return new class implements RouteGeneratorFactoryInterface {
            /**
             * @param array<string, mixed> $options
             */
            public function create(array $options = []): RouteGeneratorInterface
            {
                return new class($options) implements RouteGeneratorInterface {
                    /**
                     * @param array<string, mixed> $options
                     */
                    public function __construct(
                        private readonly array $options,
                    ) {}

                    public function __invoke(int $page): string
                    {
                        $omitFirstPage = $this->options['omitFirstPage'] ?? false;

                        if ($page > 1 || (1 === $page && !$omitFirstPage)) {
                            return '/my-page?page='.$page;
                        }

                        return '/my-page';
                    }
                };
            }
        };
    }

    /**
     * @return Pagerfanta<int<1, 100>>
     */
    private function createPagerfanta(): Pagerfanta
    {
        return new Pagerfanta(new FixedAdapter(100, range(1, 100)));
    }

    public function testTheDefaultPagerfantaViewIsRendered(): void
    {
        $this->assertViewOutputMatches(
            $this->extension->renderPagerfanta($this->createPagerfanta()),
            '<nav class="pagination">
    <span class="pagination__item pagination__item--previous-page pagination__item--disabled">Previous</span>
    <span class="pagination__item pagination__item--current-page">1</span>
    <a class="pagination__item" href="/my-page?page=2">2</a>
    <a class="pagination__item" href="/my-page?page=3">3</a>
    <a class="pagination__item" href="/my-page?page=4">4</a>
    <a class="pagination__item" href="/my-page?page=5">5</a>
    <span class="pagination__item pagination__item--separator">&hellip;</span>
    <a class="pagination__item" href="/my-page?page=10">10</a>
    <a class="pagination__item pagination__item--next-page" href="/my-page?page=2" rel="next">Next</a>
</nav>'
        );
    }

    public function testTheDefaultPagerfantaViewIsRenderedFromALaterPageWithFirstPageOmitted(): void
    {
        $pagerfanta = $this->createPagerfanta();
        $pagerfanta->setCurrentPage(5);

        $this->assertViewOutputMatches(
            $this->extension->renderPagerfanta($pagerfanta, null, ['omitFirstPage' => true]),
            '<nav class="pagination">
    <a class="pagination__item pagination__item--previous-page" href="/my-page?page=4" rel="prev">Previous</a>
    <a class="pagination__item" href="/my-page">1</a>
    <a class="pagination__item" href="/my-page?page=2">2</a>
    <a class="pagination__item" href="/my-page?page=3">3</a>
    <a class="pagination__item" href="/my-page?page=4">4</a>
    <span class="pagination__item pagination__item--current-page">5</span>
    <a class="pagination__item" href="/my-page?page=6">6</a>
    <a class="pagination__item" href="/my-page?page=7">7</a>
    <span class="pagination__item pagination__item--separator">&hellip;</span>
    <a class="pagination__item" href="/my-page?page=10">10</a>
    <a class="pagination__item pagination__item--next-page" href="/my-page?page=6" rel="next">Next</a>
</nav>'
        );
    }

    public function testTheDefaultPagerfantaViewIsRenderedWhenConvertingTheViewNameFromAnArray(): void
    {
        $pagerfanta = $this->createPagerfanta();
        $pagerfanta->setCurrentPage(5);

        $this->assertViewOutputMatches(
            $this->extension->renderPagerfanta($pagerfanta, ['omitFirstPage' => true]),
            '<nav class="pagination">
    <a class="pagination__item pagination__item--previous-page" href="/my-page?page=4" rel="prev">Previous</a>
    <a class="pagination__item" href="/my-page">1</a>
    <a class="pagination__item" href="/my-page?page=2">2</a>
    <a class="pagination__item" href="/my-page?page=3">3</a>
    <a class="pagination__item" href="/my-page?page=4">4</a>
    <span class="pagination__item pagination__item--current-page">5</span>
    <a class="pagination__item" href="/my-page?page=6">6</a>
    <a class="pagination__item" href="/my-page?page=7">7</a>
    <span class="pagination__item pagination__item--separator">&hellip;</span>
    <a class="pagination__item" href="/my-page?page=10">10</a>
    <a class="pagination__item pagination__item--next-page" href="/my-page?page=6" rel="next">Next</a>
</nav>'
        );
    }

    public function testAPageUrlCanBeGenerated(): void
    {
        $this->assertSame('/my-page?page=1', $this->extension->getPageUrl($this->createPagerfanta(), 1));
    }

    public function testAPageUrlCannotBeGeneratedIfThePageIsOutOfBounds(): void
    {
        $this->expectException(OutOfRangeCurrentPageException::class);
        $this->expectExceptionMessage("Page '1000' is out of bounds");

        $this->extension->getPageUrl($this->createPagerfanta(), 1000);
    }

    private function assertViewOutputMatches(string $view, string $expected): void
    {
        $this->assertSame($this->removeWhitespacesBetweenTags($expected), $view);
    }

    private function removeWhitespacesBetweenTags(string $string): string
    {
        return preg_replace('/>\s+</', '><', $string) ?? '';
    }

    /**
     * @return CursorPagerfanta<int>
     */
    private function createCursorPager(): CursorPagerfanta
    {
        return new CursorPagerfanta(new CallbackCursorAdapter(static fn (?Cursor $cursor, int $limit): CursorSlice => new CursorSlice([1], null, new Cursor(['id' => 1]))));
    }

    private function createPositionRouteGeneratorFactory(): RouteGeneratorFactoryInterface&PositionRouteGeneratorFactoryInterface
    {
        return new class implements RouteGeneratorFactoryInterface, PositionRouteGeneratorFactoryInterface {
            /**
             * @param array<string, mixed> $options
             */
            public function create(array $options = []): RouteGeneratorInterface
            {
                return new class implements RouteGeneratorInterface {
                    public function __invoke(int $page): string
                    {
                        return '/my-page?page='.$page;
                    }
                };
            }

            /**
             * @param array<string, mixed> $options
             */
            public function createPositionRouteGenerator(array $options = []): PositionRouteGeneratorInterface
            {
                return new PositionRouteGeneratorDecorator(static fn (Position $position): string => $position instanceof CursorPosition ? '/my-page?after='.$position->cursor->fields['id'] : '/my-page?page='.($position instanceof PagePosition ? $position->page : 1));
            }
        };
    }

    private function createViewFactory(): ViewFactory
    {
        $viewFactory = new ViewFactory();
        $viewFactory->set('default', new DefaultView());
        $viewFactory->set('sequential', new SequentialView(new DefaultTemplate()));

        return $viewFactory;
    }

    public function testAPagerWhichTheDefaultViewCannotRenderIsRejectedWithoutADefaultSequentialView(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configure a default sequential view');

        (new PagerfantaRuntime('default', $this->createViewFactory(), $this->createPositionRouteGeneratorFactory()))->renderPagerfanta($this->createCursorPager());
    }

    public function testAPagerWhichTheDefaultViewCannotRenderIsRenderedWithTheDefaultSequentialView(): void
    {
        // The callback adapter is forward-only by default, so there is no previous link
        $this->assertSame(
            '<nav class="pagination"><a class="pagination__item pagination__item--next-page" href="/my-page?after=1" rel="next">Next</a></nav>',
            (new PagerfantaRuntime('default', $this->createViewFactory(), $this->createPositionRouteGeneratorFactory(), 'sequential'))->renderPagerfanta($this->createCursorPager()),
        );
    }

    public function testTheDefaultViewIsUsedForAPagerItCanRenderWhenADefaultSequentialViewIsSet(): void
    {
        $this->assertStringContainsString(
            '<span class="pagination__item pagination__item--current-page">1</span>',
            (new PagerfantaRuntime('default', $this->createViewFactory(), $this->createPositionRouteGeneratorFactory(), 'sequential'))->renderPagerfanta($this->createPagerfanta()),
        );
    }

    public function testANamedViewWhichCannotRenderThePagerIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PagerfantaRuntime('sequential', $this->createViewFactory(), $this->createPositionRouteGeneratorFactory(), 'sequential'))->renderPagerfanta($this->createCursorPager(), 'default');
    }

    public function testAPagerViewIsGivenAPositionRouteGenerator(): void
    {
        $this->assertSame(
            '<nav class="pagination"><span class="pagination__item pagination__item--previous-page pagination__item--disabled">Previous</span><a class="pagination__item pagination__item--next-page" href="/my-page?page=2" rel="next">Next</a></nav>',
            (new PagerfantaRuntime('sequential', $this->createViewFactory(), $this->createPositionRouteGeneratorFactory()))->renderPagerfanta($this->createPagerfanta()),
        );
    }

    public function testAPagerViewIsGivenAnAdaptedPageRouteGeneratorWhenTheFactoryDoesNotSupportPositions(): void
    {
        $this->assertSame(
            '<nav class="pagination"><span class="pagination__item pagination__item--previous-page pagination__item--disabled">Previous</span><a class="pagination__item pagination__item--next-page" href="/my-page?page=2" rel="next">Next</a></nav>',
            (new PagerfantaRuntime('sequential', $this->createViewFactory(), $this->createRouteGeneratorFactory()))->renderPagerfanta($this->createPagerfanta()),
        );
    }

    public function testAPositionUrlCanBeGenerated(): void
    {
        $runtime = new PagerfantaRuntime('default', $this->createViewFactory(), $this->createPositionRouteGeneratorFactory());

        $this->assertSame('/my-page?after=3', $runtime->getPositionUrl(new CursorPosition(new Cursor(['id' => 3]))));
        $this->assertSame('/my-page?page=3', $runtime->getPositionUrl(new PagePosition(3)));
    }

    public function testAPagePositionUrlCanBeGeneratedWhenTheFactoryDoesNotSupportPositions(): void
    {
        $this->assertSame('/my-page?page=3', $this->extension->getPositionUrl(new PagePosition(3)));
    }

    public function testACursorPositionUrlCannotBeGeneratedWhenTheFactoryDoesNotSupportPositions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->extension->getPositionUrl(new CursorPosition(new Cursor(['id' => 3])));
    }

    #[Group('legacy')]
    public function testARouteGeneratorFactoryWithoutPositionSupportIsDeprecated(): void
    {
        $deprecations = $this->captureDeprecations(fn () => new PagerfantaRuntime('default', $this->createViewFactory(), $this->createRouteGeneratorFactory()));

        $this->assertSame(['Since pagerfanta/twig 4.10: Using a route generator factory which does not implement "Pagerfanta\\RouteGenerator\\PositionRouteGeneratorFactoryInterface" with "Pagerfanta\\Twig\\Extension\\PagerfantaRuntime" is deprecated.'], $deprecations);
    }

    public function testRenderingWithAPositionRouteGeneratorFactoryIsNotDeprecated(): void
    {
        $this->assertSame([], $this->captureDeprecations(function (): void {
            $runtime = new PagerfantaRuntime('default', $this->createViewFactory(), $this->createPositionRouteGeneratorFactory(), 'sequential');

            $runtime->renderPagerfanta($this->createPagerfanta());
            $runtime->renderPagerfanta($this->createCursorPager());
            $runtime->getPageUrl($this->createPagerfanta(), 2);
        }));
    }

    public function testAViewWhichOnlyAcceptsPageNumbersIsGivenAPageNumberRouteGenerator(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->method('render')
            ->willReturnCallback(function (PagerfantaInterface $pagerfanta, callable $routeGenerator): string {
                $this->assertNotInstanceOf(PositionRouteGeneratorInterface::class, $routeGenerator);

                return $routeGenerator(2);
            });

        $viewFactory = new ViewFactory();
        $viewFactory->set('legacy', $view);

        $this->assertSame('/my-page?page=2', (new PagerfantaRuntime('legacy', $viewFactory, $this->createPositionRouteGeneratorFactory()))->renderPagerfanta($this->createPagerfanta()));
    }

    public function testAPageUrlCanBeGeneratedWithAFactoryOnlySupportingPositions(): void
    {
        $factory = new class implements PositionRouteGeneratorFactoryInterface {
            /**
             * @param array<string, mixed> $options
             */
            public function createPositionRouteGenerator(array $options = []): PositionRouteGeneratorInterface
            {
                return new PositionRouteGeneratorDecorator(static fn (Position $position): string => '/my-page?page='.($position instanceof PagePosition ? $position->page : 0));
            }
        };

        $this->assertSame('/my-page?page=3', (new PagerfantaRuntime('default', $this->createViewFactory(), $factory))->getPageUrl($this->createPagerfanta(), 3));
    }
}
