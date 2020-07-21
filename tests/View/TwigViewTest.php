<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Twig\View\TwigView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Template;
use Twig\TemplateWrapper;

final class TwigViewTest extends TestCase
{
    /**
     * @var MockObject|Environment
     */
    private $twig;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
    }

    public function testRendersWithATemplateSpecifiedInTheOptions(): void
    {
        $options = ['template' => 'test.html.twig'];

        $template = $this->createMock(Template::class);
        $template->expects($this->once())
            ->method('displayBlock')
            ->willReturnCallback(
                static function (): void {
                    echo 'Twig template';
                }
            );

        $this->twig->expects($this->once())
            ->method('load')
            ->with($options['template'])
            ->willReturn(new TemplateWrapper($this->twig, $template));

        $this->twig->expects($this->once())
            ->method('mergeGlobals')
            ->willReturn([]);

        $this->assertSame(
            'Twig template',
            (new TwigView($this->twig, 'constructor.html.twig'))->render(
                $this->createPagerfanta(),
                $this->createRouteGenerator(),
                $options
            )
        );
    }

    public function testRendersWithATemplateSpecifiedInTheConstructorWhenNotSetInTheOptions(): void
    {
        $template = $this->createMock(Template::class);
        $template->expects($this->once())
            ->method('displayBlock')
            ->willReturnCallback(
                static function (): void {
                    echo 'Twig template';
                }
            );

        $this->twig->expects($this->once())
            ->method('load')
            ->with('constructor.html.twig')
            ->willReturn(new TemplateWrapper($this->twig, $template));

        $this->twig->expects($this->once())
            ->method('mergeGlobals')
            ->willReturn([]);

        $this->assertSame(
            'Twig template',
            (new TwigView($this->twig, 'constructor.html.twig'))->render(
                $this->createPagerfanta(),
                $this->createRouteGenerator()
            )
        );
    }

    public function testRendersWithTheDefaultTemplateWhenNotSetInConstructorOrOptions(): void
    {
        $template = $this->createMock(Template::class);
        $template->expects($this->once())
            ->method('displayBlock')
            ->willReturnCallback(
                static function (): void {
                    echo 'Twig template';
                }
            );

        $this->twig->expects($this->once())
            ->method('load')
            ->with(TwigView::DEFAULT_TEMPLATE)
            ->willReturn(new TemplateWrapper($this->twig, $template));

        $this->twig->expects($this->once())
            ->method('mergeGlobals')
            ->willReturn([]);

        $this->assertSame(
            'Twig template',
            (new TwigView($this->twig))->render($this->createPagerfanta(), $this->createRouteGenerator())
        );
    }

    public function testRejectsANonCallableRouteGenerator(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $template = $this->createMock(Template::class);
        $template->expects($this->never())
            ->method('displayBlock');

        $this->twig->expects($this->once())
            ->method('load')
            ->with(TwigView::DEFAULT_TEMPLATE)
            ->willReturn(new TemplateWrapper($this->twig, $template));

        (new TwigView($this->twig))->render($this->createPagerfanta(), new \stdClass());
    }

    private function createPagerfanta(): Pagerfanta
    {
        return new Pagerfanta(new FixedAdapter(100, range(1, 100)));
    }

    private function createRouteGenerator(): callable
    {
        return static function (int $page): string { return ''; };
    }
}
