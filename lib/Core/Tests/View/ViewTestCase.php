<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class ViewTestCase extends TestCase
{
    /**
     * @var MockObject&AdapterInterface<mixed>
     */
    private MockObject&AdapterInterface $adapter;

    /**
     * @var Pagerfanta<mixed>
     */
    private Pagerfanta $pagerfanta;

    private ViewInterface $view;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->pagerfanta = new Pagerfanta($this->adapter);

        $this->view = $this->createView();
    }

    abstract protected function createView(): ViewInterface;

    protected function setNbPages(int $nbPages): void
    {
        $nbResults = $this->calculateNbResults($nbPages);

        $this->adapter->method('getNbResults')
            ->willReturn($nbResults);
    }

    private function calculateNbResults(int $nbPages): int
    {
        return $nbPages * $this->pagerfanta->getMaxPerPage();
    }

    /**
     * @phpstan-param positive-int $currentPage
     */
    protected function setCurrentPage(int $currentPage): void
    {
        $this->pagerfanta->setCurrentPage($currentPage);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function renderView(array $options): string
    {
        $routeGenerator = $this->createRouteGenerator();

        return $this->view->render($this->pagerfanta, $routeGenerator, $options);
    }

    /**
     * @phpstan-return \Closure(int $page): string
     */
    protected function createRouteGenerator(): \Closure
    {
        return static fn (int $page) => '|'.$page.'|';
    }

    protected function assertRenderedView(string $expected, string $result): void
    {
        self::assertSame($this->filterExpectedView($expected), $result);
    }

    protected function filterExpectedView(string $expected): string
    {
        return $expected;
    }

    protected function removeWhitespacesBetweenTags(string $string): string
    {
        return preg_replace('/>\s+</', '><', $string) ?? '';
    }
}
