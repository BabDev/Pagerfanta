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
     * @var MockObject|AdapterInterface
     */
    private $adapter;

    /**
     * @var Pagerfanta
     */
    private $pagerfanta;

    /**
     * @var ViewInterface
     */
    private $view;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->pagerfanta = new Pagerfanta($this->adapter);

        $this->view = $this->createView();
    }

    abstract protected function createView(): ViewInterface;

    protected function setNbPages($nbPages): void
    {
        $nbResults = $this->calculateNbResults($nbPages);

        $this->adapter
            ->expects($this->any())
            ->method('getNbResults')
            ->willReturn($nbResults);
    }

    private function calculateNbResults($nbPages)
    {
        return $nbPages * $this->pagerfanta->getMaxPerPage();
    }

    protected function setCurrentPage($currentPage): void
    {
        $this->pagerfanta->setCurrentPage($currentPage);
    }

    protected function renderView($options)
    {
        $routeGenerator = $this->createRouteGenerator();

        return $this->view->render($this->pagerfanta, $routeGenerator, $options);
    }

    protected function createRouteGenerator(): \Closure
    {
        return function ($page) { return '|'.$page.'|'; };
    }

    protected function assertRenderedView($expected, $result): void
    {
        $this->assertSame($this->filterExpectedView($expected), $result);
    }

    protected function filterExpectedView($expected)
    {
        return $expected;
    }

    protected function removeWhitespacesBetweenTags($string)
    {
        return preg_replace('/>\s+</', '><', $string);
    }
}
