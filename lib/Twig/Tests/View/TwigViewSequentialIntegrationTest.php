<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\Twig\Extension\PagerfantaExtension;
use Pagerfanta\Twig\Extension\PagerfantaRuntime;
use Pagerfanta\Twig\View\TwigView;
use Pagerfanta\View\ViewFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\BlockChain;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * Integration tests for rendering pagers with previous and next links using the Twig templates.
 */
final class TwigViewSequentialIntegrationTest extends TestCase
{
    private const MESSAGES_TEMPLATE = <<<TWIG
        {%- block previous_page_message -%}
            Back
        {%- endblock previous_page_message -%}

        {%- block next_page_message -%}
            Forward
        {%- endblock next_page_message -%}
        TWIG;

    private Environment $twig;

    protected function setUp(): void
    {
        $filesystemLoader = new FilesystemLoader();
        $filesystemLoader->addPath(__DIR__.'/../../templates', 'Pagerfanta');

        // Strict variables ensure the sequential rendering does not rely on the variables only given for numbered rendering
        $this->twig = new Environment(
            new ChainLoader([
                new ArrayLoader([
                    'integration.html.twig' => '{{ pagerfanta(pager, options) }}',
                    'position_url.html.twig' => '{{ pagerfanta_position_url(pager.nextPosition) }}',
                    'messages.html.twig' => self::MESSAGES_TEMPLATE,
                ]),
                $filesystemLoader,
            ]),
            ['strict_variables' => true],
        );
        $this->twig->addExtension(new PagerfantaExtension());
        $this->twig->addRuntimeLoader(new FactoryRuntimeLoader([
            PagerfantaRuntime::class => function (): PagerfantaRuntime {
                $viewFactory = new ViewFactory();
                $viewFactory->set('twig', new TwigView($this->twig));

                return new PagerfantaRuntime('twig', $viewFactory, $this->createRouteGeneratorFactory());
            },
        ]));
    }

    /**
     * Creates a cursor pager on a page after the first, linking to the previous and next pages as allowed.
     *
     * @return CursorPagerfanta<int>
     */
    private function createCursorPager(bool $supportsBackwardNavigation = true, bool $hasPrevious = true): CursorPagerfanta
    {
        $adapter = new CallbackCursorAdapter(
            static fn (?Cursor $cursor, int $limit): CursorSlice => new CursorSlice(
                [2],
                $hasPrevious ? new Cursor(['id' => 2], Direction::Previous) : null,
                new Cursor(['id' => 2]),
            ),
            $supportsBackwardNavigation,
        );

        return new CursorPagerfanta($adapter, 1, new CursorPosition(new Cursor(['id' => 1])));
    }

    /**
     * @return Pagerfanta<int>
     */
    private function createOffsetPager(): Pagerfanta
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(new ArrayAdapter(range(1, 30)), 2, 10);
    }

    public static function generateUrl(Position $position): string
    {
        return match (true) {
            $position instanceof CursorPosition => '/posts?'.('Next' === $position->cursor->direction->name ? 'after' : 'before').'='.$position->cursor->fields['id'],
            $position instanceof PagePosition => '/posts?page='.$position->page,
            default => throw new \UnexpectedValueException('Unexpected position'),
        };
    }

    private function createPositionRouteGenerator(): PositionRouteGeneratorInterface
    {
        return new PositionRouteGeneratorDecorator(self::generateUrl(...));
    }

    private function createRouteGeneratorFactory(): RouteGeneratorFactoryInterface&PositionRouteGeneratorFactoryInterface
    {
        return new class implements RouteGeneratorFactoryInterface, PositionRouteGeneratorFactoryInterface {
            /**
             * @param array<string, mixed> $options
             */
            public function create(array $options = []): RouteGeneratorInterface
            {
                return new RouteGeneratorDecorator(static fn (int $page): string => '/posts?page='.$page);
            }

            /**
             * @param array<string, mixed> $options
             */
            public function createPositionRouteGenerator(array $options = []): PositionRouteGeneratorInterface
            {
                return new PositionRouteGeneratorDecorator(TwigViewSequentialIntegrationTest::generateUrl(...));
            }
        };
    }

    /**
     * @return \Generator<string, array{0: string, 1: string}>
     */
    public static function dataThemes(): \Generator
    {
        yield 'default' => [
            '@Pagerfanta/default.html.twig',
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="/posts?before=2" rel="prev">Previous</a><a class="pagination__item pagination__item--next-page" href="/posts?after=2" rel="next">Next</a></nav>',
        ];

        yield 'foundation 6' => [
            '@Pagerfanta/foundation6.html.twig',
            '<nav aria-label="Pagination"><ul class="pagination"><li class="pagination-previous"><a href="/posts?before=2" rel="prev">Previous</a></li><li class="pagination-next"><a href="/posts?after=2" rel="next">Next</a></li></ul></nav>',
        ];

        yield 'semantic ui' => [
            '@Pagerfanta/semantic_ui.html.twig',
            '<div class="ui pagination menu"><a class="item" href="/posts?before=2" rel="prev">Previous</a><a class="item" href="/posts?after=2" rel="next">Next</a></div>',
        ];

        yield 'tailwind' => [
            '@Pagerfanta/tailwind.html.twig',
            '<nav role="navigation" class="flex items-center justify-between"><div class="flex-1 flex items-center justify-between"><div><span class="relative z-0 inline-flex shadow-sm"><a href="/posts?before=2" rel="prev" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">Previous</a><a href="/posts?after=2" rel="next" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">Next</a></span></div></div></nav>',
        ];

        yield 'twitter bootstrap' => [
            '@Pagerfanta/twitter_bootstrap.html.twig',
            '<div class="pagination"><ul><li><a href="/posts?before=2" rel="prev">Previous</a></li><li><a href="/posts?after=2" rel="next">Next</a></li></ul></div>',
        ];

        yield 'twitter bootstrap 3' => [
            '@Pagerfanta/twitter_bootstrap3.html.twig',
            '<ul class="pagination"><li><a href="/posts?before=2" rel="prev">Previous</a></li><li><a href="/posts?after=2" rel="next">Next</a></li></ul>',
        ];

        yield 'twitter bootstrap 4' => [
            '@Pagerfanta/twitter_bootstrap4.html.twig',
            '<ul class="pagination"><li class="page-item"><a class="page-link" href="/posts?before=2" rel="prev">Previous</a></li><li class="page-item"><a class="page-link" href="/posts?after=2" rel="next">Next</a></li></ul>',
        ];

        yield 'twitter bootstrap 5' => [
            '@Pagerfanta/twitter_bootstrap5.html.twig',
            '<ul class="pagination"><li class="page-item"><a class="page-link" href="/posts?before=2" rel="prev">Previous</a></li><li class="page-item"><a class="page-link" href="/posts?after=2" rel="next">Next</a></li></ul>',
        ];
    }

    #[DataProvider('dataThemes')]
    public function testACursorPagerIsRenderedWithEachTheme(string $template, string $expected): void
    {
        $this->assertViewOutputMatches($expected, (new TwigView($this->twig))->render($this->createCursorPager(), $this->createPositionRouteGenerator(), ['template' => $template]));
    }

    public function testAForwardOnlyCursorPagerIsRenderedWithoutAPreviousLink(): void
    {
        $this->assertViewOutputMatches(
            '<nav class="pagination"><a class="pagination__item pagination__item--next-page" href="/posts?after=2" rel="next">Next</a></nav>',
            (new TwigView($this->twig))->render($this->createCursorPager(false), $this->createPositionRouteGenerator()),
        );
    }

    public function testTheFirstCursorPageRendersADisabledPreviousLink(): void
    {
        $this->assertViewOutputMatches(
            '<nav class="pagination"><span class="pagination__item pagination__item--previous-page pagination__item--disabled">Previous</span><a class="pagination__item pagination__item--next-page" href="/posts?after=2" rel="next">Next</a></nav>',
            (new TwigView($this->twig))->render($this->createCursorPager(true, false), $this->createPositionRouteGenerator()),
        );
    }

    public function testAnOffsetPagerCanBeRenderedSequentially(): void
    {
        $this->assertViewOutputMatches(
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="/posts?page=1" rel="prev">Previous</a><a class="pagination__item pagination__item--next-page" href="/posts?page=3" rel="next">Next</a></nav>',
            (new TwigView($this->twig))->render($this->createOffsetPager(), static fn (int $page): string => '/posts?page='.$page, ['sequential' => true]),
        );
    }

    public function testAnOffsetPagerIsRenderedWithNumberedLinksFromAPositionRouteGenerator(): void
    {
        $view = new TwigView($this->twig);

        $this->assertSame(
            $view->render($this->createOffsetPager(), static fn (int $page): string => '/posts?page='.$page),
            $view->render($this->createOffsetPager(), $this->createPositionRouteGenerator()),
        );
    }

    public function testTheMessagesCanBeCustomizedWithAChainOfTemplates(): void
    {
        if (!class_exists(BlockChain::class)) {
            $this->markTestSkipped('This test requires twig/twig 3.29 or later.');
        }

        $this->assertViewOutputMatches(
            '<ul class="pagination"><li class="page-item"><a class="page-link" href="/posts?before=2" rel="prev">Back</a></li><li class="page-item"><a class="page-link" href="/posts?after=2" rel="next">Forward</a></li></ul>',
            (new TwigView($this->twig))->render($this->createCursorPager(), $this->createPositionRouteGenerator(), ['template' => ['messages.html.twig', '@Pagerfanta/twitter_bootstrap5.html.twig']]),
        );
    }

    public function testACursorPagerIsRenderedWithTheTwigFunction(): void
    {
        $this->assertViewOutputMatches(
            '<nav class="pagination"><a class="pagination__item pagination__item--previous-page" href="/posts?before=2" rel="prev">Previous</a><a class="pagination__item pagination__item--next-page" href="/posts?after=2" rel="next">Next</a></nav>',
            $this->twig->render('integration.html.twig', ['pager' => $this->createCursorPager(), 'options' => []]),
        );
    }

    public function testAPositionUrlIsGeneratedWithTheTwigFunction(): void
    {
        $this->assertSame('/posts?after=2', $this->twig->render('position_url.html.twig', ['pager' => $this->createCursorPager()]));
    }

    private function assertViewOutputMatches(string $expected, string $view): void
    {
        $this->assertSame($expected, preg_replace('/>\s+</', '><', $view));
    }
}
