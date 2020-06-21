<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Pagerfanta;
use Pagerfanta\View\OptionableView;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionableViewTest extends TestCase
{
    private const RENDERED_VIEW = 'rendered';

    /**
     * @var MockObject|Pagerfanta
     */
    private $pagerfanta;

    /**
     * @var callable
     */
    private $routeGenerator;

    protected function setUp(): void
    {
        $this->pagerfanta = $this->createMock(Pagerfanta::class);
        $this->routeGenerator = $this->createRouteGenerator();
    }

    private function createRouteGenerator(): \Closure
    {
        return static function (int $page): string { return ''; };
    }

    public function testRenderShouldDelegateToTheView(): void
    {
        $defaultOptions = ['foo' => 'bar', 'bar' => 'ups'];

        $this->assertSame(
            self::RENDERED_VIEW,
            (new OptionableView($this->createViewMock($defaultOptions), $defaultOptions))->render($this->pagerfanta, $this->routeGenerator)
        );
    }

    public function testRenderShouldMergeOptions(): void
    {
        $defaultOptions = ['foo' => 'bar'];
        $options = ['ups' => 'da'];

        $this->assertSame(
            self::RENDERED_VIEW,
            (new OptionableView($this->createViewMock(array_merge($defaultOptions, $options)), $defaultOptions))->render($this->pagerfanta, $this->routeGenerator, $options)
        );
    }

    /**
     * @return MockObject|ViewInterface
     */
    private function createViewMock(array $expectedOptions)
    {
        /** @var MockObject|ViewInterface $view */
        $view = $this->createMock(ViewInterface::class);

        $view->expects($this->once())
            ->method('render')
            ->with($this->pagerfanta, $this->routeGenerator, $expectedOptions)
            ->willReturn(self::RENDERED_VIEW);

        return $view;
    }
}
