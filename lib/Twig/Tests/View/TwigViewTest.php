<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Tests\View;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Twig\View\TwigView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Template;
use Twig\TemplateWrapper;

final class TwigViewTest extends TestCase
{
    private MockObject&Environment $twig;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
    }

    public function testRendersWithATemplateSpecifiedInTheOptions(): void
    {
        $options = ['template' => 'test.html.twig'];

        /** @var MockObject&Template $template */
        $template = $this->createMock(Template::class);

        // As of Twig 3.9, the internal wrapper implementation changed to accommodate the new yield strategy
        if (method_exists(Template::class, 'yieldBlock')) {
            $template->expects(self::once())
                ->method('renderBlock')
                ->willReturn('Twig template');
        } else {
            $template->expects(self::once())
                ->method('displayBlock')
                ->willReturnCallback(
                    static function (): void {
                        echo 'Twig template';
                    }
                );
        }

        $this->twig->expects(self::once())
            ->method('load')
            ->with($options['template'])
            ->willReturn(new TemplateWrapper($this->twig, $template));

        // As of Twig 3.14, mergeGlobals is deprecated and not called when rendering
        if (!method_exists(Environment::class, 'resetGlobals')) {
            $this->twig->expects(self::once())
                ->method('mergeGlobals')
                ->willReturn([]);
        }

        self::assertSame('Twig template', (new TwigView($this->twig, 'constructor.html.twig'))->render(
            $this->createPagerfanta(),
            $this->createRouteGenerator(),
            $options
        ));
    }

    public function testRendersWithATemplateSpecifiedInTheConstructorWhenNotSetInTheOptions(): void
    {
        /** @var MockObject&Template $template */
        $template = $this->createMock(Template::class);

        // As of Twig 3.9, the internal wrapper implementation changed to accommodate the new yield strategy
        if (method_exists(Template::class, 'yieldBlock')) {
            $template->expects(self::once())
                ->method('renderBlock')
                ->willReturn('Twig template');
        } else {
            $template->expects(self::once())
                ->method('displayBlock')
                ->willReturnCallback(
                    static function (): void {
                        echo 'Twig template';
                    }
                );
        }

        $this->twig->expects(self::once())
            ->method('load')
            ->with('constructor.html.twig')
            ->willReturn(new TemplateWrapper($this->twig, $template));

        // As of Twig 3.14, mergeGlobals is deprecated and not called when rendering
        if (!method_exists(Environment::class, 'resetGlobals')) {
            $this->twig->expects(self::once())
                ->method('mergeGlobals')
                ->willReturn([]);
        }

        self::assertSame('Twig template', (new TwigView($this->twig, 'constructor.html.twig'))->render(
            $this->createPagerfanta(),
            $this->createRouteGenerator()
        ));
    }

    public function testRendersWithTheDefaultTemplateWhenNotSetInConstructorOrOptions(): void
    {
        /** @var MockObject&Template $template */
        $template = $this->createMock(Template::class);

        // As of Twig 3.9, the internal wrapper implementation changed to accommodate the new yield strategy
        if (method_exists(Template::class, 'yieldBlock')) {
            $template->expects(self::once())
                ->method('renderBlock')
                ->willReturn('Twig template');
        } else {
            $template->expects(self::once())
                ->method('displayBlock')
                ->willReturnCallback(
                    static function (): void {
                        echo 'Twig template';
                    }
                );
        }

        $this->twig->expects(self::once())
            ->method('load')
            ->with(TwigView::DEFAULT_TEMPLATE)
            ->willReturn(new TemplateWrapper($this->twig, $template));

        // As of Twig 3.14, mergeGlobals is deprecated and not called when rendering
        if (!method_exists(Environment::class, 'resetGlobals')) {
            $this->twig->expects(self::once())
                ->method('mergeGlobals')
                ->willReturn([]);
        }

        self::assertSame('Twig template', (new TwigView($this->twig))->render($this->createPagerfanta(), $this->createRouteGenerator()));
    }

    /**
     * @return Pagerfanta<int>
     *
     * @phpstan-return Pagerfanta<int<1, 100>>
     */
    private function createPagerfanta(): Pagerfanta
    {
        return new Pagerfanta(new FixedAdapter(100, range(1, 100)));
    }

    private function createRouteGenerator(): callable
    {
        return static fn (int $page) => '';
    }
}
