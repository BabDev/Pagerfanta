<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Twig;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Pagerfanta\Twig\Extension\PagerfantaRuntime;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\ViewFactory;
use Pagerfanta\View\ViewFactoryInterface;
use PHPUnit\Framework\TestCase;

final class PagerfantaRuntimeTest extends TestCase
{
    /**
     * @var ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var RouteGeneratorFactoryInterface
     */
    private $routeGeneratorFactory;

    /**
     * @var PagerfantaRuntime
     */
    private $extension;

    protected function setUp(): void
    {
        $this->viewFactory = new ViewFactory();
        $this->viewFactory->set('default', new DefaultView());

        $this->routeGeneratorFactory = $this->createRouteGeneratorFactory();

        $this->extension = new PagerfantaRuntime(
            'default',
            $this->viewFactory,
            $this->routeGeneratorFactory
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
                            return '/my-page?page='.$page;
                        }

                        return '/my-page';
                    }
                };
            }
        };
    }

    private function createPagerfanta(): Pagerfanta
    {
        return new Pagerfanta(new FixedAdapter(100, range(1, 100)));
    }

    public function testTheDefaultPagerfantaViewIsRendered(): void
    {
        $this->assertViewOutputMatches(
            $this->extension->renderPagerfanta($this->createPagerfanta()),
            '<nav>
    <span class="disabled">Previous</span>
    <span class="current">1</span>
    <a href="/my-page?page=2">2</a>
    <a href="/my-page?page=3">3</a>
    <a href="/my-page?page=4">4</a>
    <a href="/my-page?page=5">5</a>
    <span class="dots">...</span>
    <a href="/my-page?page=10">10</a>
    <a href="/my-page?page=2" rel="next">Next</a>
</nav>'
        );
    }

    public function testTheDefaultPagerfantaViewIsRenderedFromALaterPageWithFirstPageOmitted(): void
    {
        $pagerfanta = $this->createPagerfanta();
        $pagerfanta->setCurrentPage(5);

        $this->assertViewOutputMatches(
            $this->extension->renderPagerfanta($pagerfanta, null, ['omitFirstPage' => true]),
            '<nav>
    <a href="/my-page?page=4" rel="prev">Previous</a>
    <a href="/my-page">1</a>
    <a href="/my-page?page=2">2</a>
    <a href="/my-page?page=3">3</a>
    <a href="/my-page?page=4">4</a>
    <span class="current">5</span>
    <a href="/my-page?page=6">6</a>
    <a href="/my-page?page=7">7</a>
    <span class="dots">...</span>
    <a href="/my-page?page=10">10</a>
    <a href="/my-page?page=6" rel="next">Next</a>
</nav>'
        );
    }

    public function testTheDefaultPagerfantaViewIsRenderedWhenConvertingTheViewNameFromAnArray(): void
    {
        $pagerfanta = $this->createPagerfanta();
        $pagerfanta->setCurrentPage(5);

        $this->assertViewOutputMatches(
            $this->extension->renderPagerfanta($pagerfanta, ['omitFirstPage' => true]),
            '<nav>
    <a href="/my-page?page=4" rel="prev">Previous</a>
    <a href="/my-page">1</a>
    <a href="/my-page?page=2">2</a>
    <a href="/my-page?page=3">3</a>
    <a href="/my-page?page=4">4</a>
    <span class="current">5</span>
    <a href="/my-page?page=6">6</a>
    <a href="/my-page?page=7">7</a>
    <span class="dots">...</span>
    <a href="/my-page?page=10">10</a>
    <a href="/my-page?page=6" rel="next">Next</a>
</nav>'
        );
    }

    public function testTheDefaultPagerfantaViewIsNotRenderedWhenAnInvalidTypeIsGivenForTheViewNameArgument(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The $viewName argument of %s::renderPagerfanta() must be an array, a string, or a null value; a object was given.',
                PagerfantaRuntime::class
            )
        );

        $this->extension->renderPagerfanta($this->createPagerfanta(), new \stdClass());
    }

    public function testAPageUrlCanBeGenerated(): void
    {
        $this->assertSame(
            '/my-page?page=1',
            $this->extension->getPageUrl($this->createPagerfanta(), 1)
        );
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
        return preg_replace('/>\s+</', '><', $string);
    }
}
