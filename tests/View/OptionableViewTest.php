<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Pagerfanta;
use Pagerfanta\View\OptionableView;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionableViewTest extends TestCase
{
    private $pagerfanta;
    private $routeGenerator;

    protected function setUp(): void
    {
        $this->pagerfanta = $this->createMock(Pagerfanta::class);
        $this->routeGenerator = $this->createRouteGenerator();
    }

    private function createRouteGenerator(): \Closure
    {
        return function (int $page): string { return ''; };
    }

    public function testRenderShouldDelegateToTheView(): void
    {
        $defaultOptions = ['foo' => 'bar', 'bar' => 'ups'];

        $view = $this->createViewMock($defaultOptions);
        $optionable = new OptionableView($view, $defaultOptions);

        $returned = $optionable->render($this->pagerfanta, $this->routeGenerator);
        $this->assertSame('rendered', $returned);
    }

    public function testRenderShouldMergeOptions(): void
    {
        $defaultOptions = ['foo' => 'bar'];
        $options = ['ups' => 'da'];
        $expectedOptions = array_merge($defaultOptions, $options);

        $view = $this->createViewMock($expectedOptions);
        $optionable = new OptionableView($view, $defaultOptions);

        $returned = $optionable->render($this->pagerfanta, $this->routeGenerator, $options);
        $this->assertSame('rendered', $returned);
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
            ->willReturn('rendered');

        return $view;
    }
}
