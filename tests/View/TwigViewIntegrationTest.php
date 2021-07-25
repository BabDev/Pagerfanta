<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\Twig\Extension\PagerfantaExtension;
use Pagerfanta\Twig\Extension\PagerfantaRuntime;
use Pagerfanta\Twig\View\TwigView;
use Pagerfanta\View\ViewFactory;
use Pagerfanta\View\ViewFactoryInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

/**
 * Integration tests which simulates a real Twig environment to validate templates are correctly generated.
 */
final class TwigViewIntegrationTest extends TestCase
{
    /**
     * @var ViewFactoryInterface
     */
    public $viewFactory;

    /**
     * @var RouteGeneratorFactoryInterface
     */
    public $routeGeneratorFactory;

    /**
     * @var Environment
     */
    public $twig;

    protected function setUp(): void
    {
        $filesystemLoader = new FilesystemLoader();
        $filesystemLoader->addPath(__DIR__.'/../../lib/Twig/templates', 'Pagerfanta');

        $this->twig = new Environment(new ChainLoader([new ArrayLoader(['integration.html.twig' => '{{ pagerfanta(pager, options) }}']), $filesystemLoader]));
        $this->twig->addExtension(new PagerfantaExtension());
        $this->twig->addRuntimeLoader($this->createRuntimeLoader());

        $this->routeGeneratorFactory = $this->createRouteGeneratorFactory();
    }

    /**
     * @return Pagerfanta<int>
     */
    private function createPagerfanta(): Pagerfanta
    {
        return new Pagerfanta(new FixedAdapter(100, range(1, 100)));
    }

    public function dataPagerfantaRenderer(): \Generator
    {
        yield 'default template at page 1' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/default.html.twig'],
            '<nav>
    <span class="disabled">Previous</span>
    <span class="current" aria-current="page">1</span>
    <a href="/pagerfanta-view?page=2">2</a>
    <a href="/pagerfanta-view?page=3">3</a>
    <a href="/pagerfanta-view?page=4">4</a>
    <a href="/pagerfanta-view?page=5">5</a>
    <span class="dots">...</span>
    <a href="/pagerfanta-view?page=10">10</a>
    <a href="/pagerfanta-view?page=2" rel="next">Next</a>
</nav>',
        ];

        yield 'default template at page 1 with translated labels' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/default.html.twig', 'prev_message' => 'Previous Page', 'next_message' => 'Next Page'],
            '<nav>
    <span class="disabled">Previous Page</span>
    <span class="current" aria-current="page">1</span>
    <a href="/pagerfanta-view?page=2">2</a>
    <a href="/pagerfanta-view?page=3">3</a>
    <a href="/pagerfanta-view?page=4">4</a>
    <a href="/pagerfanta-view?page=5">5</a>
    <span class="dots">...</span>
    <a href="/pagerfanta-view?page=10">10</a>
    <a href="/pagerfanta-view?page=2" rel="next">Next Page</a>
</nav>',
        ];

        yield 'default template at page 5 with first page omitted' => [
            5,
            ['omitFirstPage' => true, 'template' => '@Pagerfanta/default.html.twig'],
            '<nav>
    <a href="/pagerfanta-view?page=4" rel="prev">Previous</a>
    <a href="/pagerfanta-view">1</a>
    <a href="/pagerfanta-view?page=2">2</a>
    <a href="/pagerfanta-view?page=3">3</a>
    <a href="/pagerfanta-view?page=4">4</a>
    <span class="current" aria-current="page">5</span>
    <a href="/pagerfanta-view?page=6">6</a>
    <a href="/pagerfanta-view?page=7">7</a>
    <span class="dots">...</span>
    <a href="/pagerfanta-view?page=10">10</a>
    <a href="/pagerfanta-view?page=6" rel="next">Next</a>
</nav>',
        ];

        yield 'Semantic UI template at page 1' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/semantic_ui.html.twig'],
            '<div class="ui pagination menu">
    <div class="disabled item">Previous</div>
    <div class="active item" aria-current="page">1</div>
    <a class="item" href="/pagerfanta-view?page=2">2</a>
    <a class="item" href="/pagerfanta-view?page=3">3</a>
    <a class="item" href="/pagerfanta-view?page=4">4</a>
    <a class="item" href="/pagerfanta-view?page=5">5</a>
    <div class="disabled item">&hellip;</div>
    <a class="item" href="/pagerfanta-view?page=10">10</a>
    <a class="item" href="/pagerfanta-view?page=2" rel="next">Next</a>
</div>',
        ];

        yield 'Semantic UI template at page 5 with first page omitted' => [
            5,
            ['omitFirstPage' => true, 'template' => '@Pagerfanta/semantic_ui.html.twig'],
            '<div class="ui pagination menu">
    <a class="item" href="/pagerfanta-view?page=4" rel="prev">Previous</a>
    <a class="item" href="/pagerfanta-view">1</a>
    <a class="item" href="/pagerfanta-view?page=2">2</a>
    <a class="item" href="/pagerfanta-view?page=3">3</a>
    <a class="item" href="/pagerfanta-view?page=4">4</a>
    <div class="active item" aria-current="page">5</div>
    <a class="item" href="/pagerfanta-view?page=6">6</a>
    <a class="item" href="/pagerfanta-view?page=7">7</a>
    <div class="disabled item">&hellip;</div>
    <a class="item" href="/pagerfanta-view?page=10">10</a>
    <a class="item" href="/pagerfanta-view?page=6" rel="next">Next</a>
</div>',
        ];

        yield 'Twitter Bootstrap template at page 1' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/twitter_bootstrap.html.twig'],
            '<div class="pagination">
    <ul>
        <li class="disabled"><span>Previous</span></li>
        <li class="active" aria-current="page"><span>1</span></li>
        <li><a href="/pagerfanta-view?page=2">2</a></li>
        <li><a href="/pagerfanta-view?page=3">3</a></li>
        <li><a href="/pagerfanta-view?page=4">4</a></li>
        <li><a href="/pagerfanta-view?page=5">5</a></li>
        <li class="disabled"><span>&hellip;</span></li>
        <li><a href="/pagerfanta-view?page=10">10</a></li>
        <li><a href="/pagerfanta-view?page=2" rel="next">Next</a></li>
    </ul>
</div>',
        ];

        yield 'Twitter Bootstrap template at page 5 with first page omitted' => [
            5,
            ['omitFirstPage' => true, 'template' => '@Pagerfanta/twitter_bootstrap.html.twig'],
            '<div class="pagination">
    <ul>
        <li><a href="/pagerfanta-view?page=4" rel="prev">Previous</a></li>
        <li><a href="/pagerfanta-view">1</a></li>
        <li><a href="/pagerfanta-view?page=2">2</a></li>
        <li><a href="/pagerfanta-view?page=3">3</a></li>
        <li><a href="/pagerfanta-view?page=4">4</a></li>
        <li class="active" aria-current="page"><span>5</span></li>
        <li><a href="/pagerfanta-view?page=6">6</a></li>
        <li><a href="/pagerfanta-view?page=7">7</a></li>
        <li class="disabled"><span>&hellip;</span></li>
        <li><a href="/pagerfanta-view?page=10">10</a></li>
        <li><a href="/pagerfanta-view?page=6" rel="next">Next</a></li>
    </ul>
</div>',
        ];

        yield 'Twitter Bootstrap 3 template at page 1' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/twitter_bootstrap3.html.twig'],
            '<ul class="pagination">
    <li class="disabled"><span>Previous</span></li>
    <li class="active" aria-current="page"><span>1</span></li>
    <li><a href="/pagerfanta-view?page=2">2</a></li>
    <li><a href="/pagerfanta-view?page=3">3</a></li>
    <li><a href="/pagerfanta-view?page=4">4</a></li>
    <li><a href="/pagerfanta-view?page=5">5</a></li>
    <li class="disabled"><span>&hellip;</span></li>
    <li><a href="/pagerfanta-view?page=10">10</a></li>
    <li><a href="/pagerfanta-view?page=2" rel="next">Next</a></li>
</ul>',
        ];

        yield 'Twitter Bootstrap 3 template at page 5 with first page omitted' => [
            5,
            ['omitFirstPage' => true, 'template' => '@Pagerfanta/twitter_bootstrap3.html.twig'],
            '<ul class="pagination">
    <li><a href="/pagerfanta-view?page=4" rel="prev">Previous</a></li>
    <li><a href="/pagerfanta-view">1</a></li>
    <li><a href="/pagerfanta-view?page=2">2</a></li>
    <li><a href="/pagerfanta-view?page=3">3</a></li>
    <li><a href="/pagerfanta-view?page=4">4</a></li>
    <li class="active" aria-current="page"><span>5</span></li>
    <li><a href="/pagerfanta-view?page=6">6</a></li>
    <li><a href="/pagerfanta-view?page=7">7</a></li>
    <li class="disabled"><span>&hellip;</span></li>
    <li><a href="/pagerfanta-view?page=10">10</a></li>
    <li><a href="/pagerfanta-view?page=6" rel="next">Next</a></li>
</ul>',
        ];

        yield 'Twitter Bootstrap 4 template at page 1' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/twitter_bootstrap4.html.twig'],
            '<ul class="pagination">
    <li class="page-item disabled"><span class="page-link">Previous</span></li>
    <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=2">2</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=3">3</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=4">4</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=5">5</a></li>
    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=10">10</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=2" rel="next">Next</a></li>
</ul>',
        ];

        yield 'Twitter Bootstrap 4 template at page 5 with first page omitted' => [
            5,
            ['omitFirstPage' => true, 'template' => '@Pagerfanta/twitter_bootstrap4.html.twig'],
            '<ul class="pagination">
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=4" rel="prev">Previous</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view">1</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=2">2</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=3">3</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=4">4</a></li>
    <li class="page-item active" aria-current="page"><span class="page-link">5</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=6">6</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=7">7</a></li>
    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=10">10</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=6" rel="next">Next</a></li>
</ul>',
        ];

        yield 'Twitter Bootstrap 5 template at page 1' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/twitter_bootstrap5.html.twig'],
            '<ul class="pagination">
    <li class="page-item disabled"><span class="page-link">Previous</span></li>
    <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=2">2</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=3">3</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=4">4</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=5">5</a></li>
    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=10">10</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=2" rel="next">Next</a></li>
</ul>',
        ];

        yield 'Twitter Bootstrap 5 template at page 5 with first page omitted' => [
            5,
            ['omitFirstPage' => true, 'template' => '@Pagerfanta/twitter_bootstrap5.html.twig'],
            '<ul class="pagination">
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=4" rel="prev">Previous</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view">1</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=2">2</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=3">3</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=4">4</a></li>
    <li class="page-item active" aria-current="page"><span class="page-link">5</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=6">6</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=7">7</a></li>
    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=10">10</a></li>
    <li class="page-item"><a class="page-link" href="/pagerfanta-view?page=6" rel="next">Next</a></li>
</ul>',
        ];

        yield 'Tailwind CSS template at page 1' => [
            1,
            ['omitFirstPage' => false, 'template' => '@Pagerfanta/tailwind.html.twig'],
            '<nav role="navigation" class="flex items-center justify-between">
    <div class="flex-1 flex items-center justify-between">
        <div>
            <span class="relative z-0 inline-flex shadow-sm">
                <span aria-disabled="true">
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5">Previous</span>
                </span>
                <span aria-current="page">
                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">1</span>
                </span>
                <a href="/pagerfanta-view?page=2" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">2</a>
                <a href="/pagerfanta-view?page=3" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">3</a>
                <a href="/pagerfanta-view?page=4" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">4</a>
                <a href="/pagerfanta-view?page=5" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">5</a>
                <span aria-disabled="true">
                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">&hellip;</span>
                </span>
                <a href="/pagerfanta-view?page=10" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">10</a>
                <a href="/pagerfanta-view?page=2" rel="next" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">Next</a>
            </span>
        </div>
    </div>
</nav>',
        ];

        yield 'Tailwind CSS template at page 5 with first page omitted' => [
            5,
            ['omitFirstPage' => true, 'template' => '@Pagerfanta/tailwind.html.twig'],
            '<nav role="navigation" class="flex items-center justify-between">
    <div class="flex-1 flex items-center justify-between">
        <div>
            <span class="relative z-0 inline-flex shadow-sm">
                <a href="/pagerfanta-view?page=4" rel="prev" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">Previous</a>
                <a href="/pagerfanta-view" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">1</a>
                <a href="/pagerfanta-view?page=2" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">2</a>
                <a href="/pagerfanta-view?page=3" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">3</a>
                <a href="/pagerfanta-view?page=4" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">4</a>
                <span aria-current="page">
                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">5</span>
                </span>
                <a href="/pagerfanta-view?page=6" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">6</a>
                <a href="/pagerfanta-view?page=7" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">7</a>
                <span aria-disabled="true">
                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">&hellip;</span>
                </span>
                <a href="/pagerfanta-view?page=10" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">10</a>
                <a href="/pagerfanta-view?page=6" rel="next" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">Next</a>
            </span>
        </div>
    </div>
</nav>',
        ];
    }

    /**
     * @dataProvider dataPagerfantaRenderer
     */
    public function testPagerfantaRendering(int $page, array $options, string $testOutput): void
    {
        $pagerfanta = $this->createPagerfanta();
        $pagerfanta->setCurrentPage($page);

        $this->assertViewOutputMatches(
            $this->twig->render('integration.html.twig', ['pager' => $pagerfanta, 'options' => $options]),
            $testOutput
        );
    }

    public function testPagerfantaRenderingWithEmptyOptions(): void
    {
        $this->assertNotEmpty(
            (new TwigView($this->twig))->render(
                $this->createPagerfanta(),
                $this->createRouteGeneratorFactory()->create()
            )
        );
    }

    private function createRouteGeneratorFactory(): RouteGeneratorFactoryInterface
    {
        return new class() implements RouteGeneratorFactoryInterface {
            public function create(array $options = []): RouteGeneratorInterface
            {
                return new class($options) implements RouteGeneratorInterface {
                    /**
                     * @var array
                     */
                    private $options;

                    public function __construct(array $options)
                    {
                        $this->options = $options;
                    }

                    public function __invoke(int $page): string
                    {
                        $omitFirstPage = $this->options['omitFirstPage'] ?? false;

                        if ($page > 1 || (1 === $page && !$omitFirstPage)) {
                            return '/pagerfanta-view?page='.$page;
                        }

                        return '/pagerfanta-view';
                    }
                };
            }
        };
    }

    private function createRuntimeLoader(): RuntimeLoaderInterface
    {
        return new class($this) implements RuntimeLoaderInterface {
            /**
             * @var TwigViewIntegrationTest
             */
            private $testCase;

            public function __construct(TwigViewIntegrationTest $testCase)
            {
                $this->testCase = $testCase;
            }

            /**
             * @param string $class
             *
             * @return object|null
             */
            public function load($class)
            {
                switch ($class) {
                    case PagerfantaRuntime::class:
                        $viewFactory = new ViewFactory();
                        $viewFactory->set('twig', new TwigView($this->testCase->twig));

                        return new PagerfantaRuntime(
                            'twig',
                            $viewFactory,
                            $this->testCase->routeGeneratorFactory
                        );

                    default:
                        return null;
                }
            }
        };
    }

    private function assertViewOutputMatches(string $view, string $expected): void
    {
        $this->assertSame($this->removeWhitespacesBetweenTags($expected), $view);
    }

    private function removeWhitespacesBetweenTags(string $string): string
    {
        return preg_replace('/>\s+</', '><', $string);
    }
}
