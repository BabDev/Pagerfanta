<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\OptionableView;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionableViewTest extends TestCase
{
    private $pagerfanta;
    private $routeGenerator;
    private $rendered;

    protected function setUp(): void
    {
        $this->pagerfanta = $this->createMock(PagerfantaInterface::class);
        $this->routeGenerator = $this->createRouteGenerator();
    }

    private function createRouteGenerator(): \Closure
    {
        return function (): void {};
    }

    public function testRenderShouldDelegateToTheView(): void
    {
        $defaultOptions = ['foo' => 'bar', 'bar' => 'ups'];

        $view = $this->createViewMock($defaultOptions);
        $optionable = new OptionableView($view, $defaultOptions);

        $returned = $optionable->render($this->pagerfanta, $this->routeGenerator);
        $this->assertSame($this->rendered, $returned);
    }

    public function testRenderShouldMergeOptions(): void
    {
        $defaultOptions = ['foo' => 'bar'];
        $options = ['ups' => 'da'];
        $expectedOptions = array_merge($defaultOptions, $options);

        $view = $this->createViewMock($expectedOptions);
        $optionable = new OptionableView($view, $defaultOptions);

        $returned = $optionable->render($this->pagerfanta, $this->routeGenerator, $options);
        $this->assertSame($this->rendered, $returned);
    }

    /**
     * @return MockObject|ViewInterface
     */
    private function createViewMock($expectedOptions)
    {
        $view = $this->createMock(ViewInterface::class);

        $view
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo($this->pagerfanta),
                $this->equalTo($this->routeGenerator),
                $this->equalTo($expectedOptions)
            )
            ->willReturn($this->rendered);

        return $view;
    }
}
