<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\OptionableView;
use Pagerfanta\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OptionableViewTest extends TestCase
{
    private const RENDERED_VIEW = 'rendered';

    /**
     * @var MockObject&PagerfantaInterface<mixed>
     */
    private $pagerfanta;

    /**
     * @var callable
     *
     * @phpstan-var \Closure(int $page): string
     */
    private $routeGenerator;

    protected function setUp(): void
    {
        $this->pagerfanta = $this->createMock(PagerfantaInterface::class);
        $this->routeGenerator = $this->createRouteGenerator();
    }

    /**
     * @phpstan-return \Closure(int $page): string
     */
    private function createRouteGenerator(): \Closure
    {
        return static fn (int $page) => '';
    }

    public function testRenderShouldDelegateToTheView(): void
    {
        $defaultOptions = ['foo' => 'bar', 'bar' => 'ups'];

        self::assertSame(
            self::RENDERED_VIEW,
            (new OptionableView($this->createViewMock($defaultOptions), $defaultOptions))->render($this->pagerfanta, $this->routeGenerator)
        );
    }

    public function testRenderShouldMergeOptions(): void
    {
        $defaultOptions = ['foo' => 'bar'];
        $options = ['ups' => 'da'];

        self::assertSame(
            self::RENDERED_VIEW,
            (new OptionableView($this->createViewMock(array_merge($defaultOptions, $options)), $defaultOptions))->render($this->pagerfanta, $this->routeGenerator, $options)
        );
    }

    /**
     * @return MockObject&ViewInterface
     */
    private function createViewMock(array $expectedOptions)
    {
        /** @var MockObject&ViewInterface $view */
        $view = $this->createMock(ViewInterface::class);
        $view->expects(self::once())
            ->method('render')
            ->with($this->pagerfanta, $this->routeGenerator, $expectedOptions)
            ->willReturn(self::RENDERED_VIEW);

        return $view;
    }
}
