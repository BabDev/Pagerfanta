<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\SequentialView;
use Pagerfanta\View\Template\DefaultTemplate;
use Pagerfanta\View\Template\Foundation6Template;
use Pagerfanta\View\Template\SemanticUiTemplate;
use Pagerfanta\View\Template\SequentialTemplateInterface;
use Pagerfanta\View\Template\Template;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Pagerfanta\View\Template\TwitterBootstrap4Template;
use Pagerfanta\View\Template\TwitterBootstrap5Template;
use Pagerfanta\View\Template\TwitterBootstrapTemplate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TemplateLinkAttributesTest extends TestCase
{
    /**
     * @return \Generator<string, array{\Closure(): (Template&SequentialTemplateInterface)}>
     */
    public static function dataTemplates(): \Generator
    {
        yield 'default' => [static fn (): Template&SequentialTemplateInterface => new class extends DefaultTemplate {
            use AddsTestLinkAttributes;
        }];

        yield 'twitter bootstrap' => [static fn (): Template&SequentialTemplateInterface => new class extends TwitterBootstrapTemplate {
            use AddsTestLinkAttributes;
        }];

        yield 'twitter bootstrap 3' => [static fn (): Template&SequentialTemplateInterface => new class extends TwitterBootstrap3Template {
            use AddsTestLinkAttributes;
        }];

        yield 'twitter bootstrap 4' => [static fn (): Template&SequentialTemplateInterface => new class extends TwitterBootstrap4Template {
            use AddsTestLinkAttributes;
        }];

        yield 'twitter bootstrap 5' => [static fn (): Template&SequentialTemplateInterface => new class extends TwitterBootstrap5Template {
            use AddsTestLinkAttributes;
        }];

        yield 'semantic ui' => [static fn (): Template&SequentialTemplateInterface => new class extends SemanticUiTemplate {
            use AddsTestLinkAttributes;
        }];

        yield 'foundation 6' => [static fn (): Template&SequentialTemplateInterface => new class extends Foundation6Template {
            use AddsTestLinkAttributes;
        }];
    }

    /**
     * @param \Closure(): (Template&SequentialTemplateInterface) $template
     */
    #[DataProvider('dataTemplates')]
    public function testTheAttributesAreRenderedOnTheNumberedLinks(\Closure $template): void
    {
        $rendered = (new DefaultView($template()))->render($this->createPagerfanta(), $this->createPositionRouteGenerator());

        $this->assertStringContainsString('href="|page:4|" rel="prev" data-relation="previous" data-page="4" data-live>', $rendered);
        $this->assertStringContainsString('href="|page:1|" data-relation="page" data-page="1" data-live>', $rendered);
        $this->assertStringContainsString('href="|page:4|" data-relation="page" data-page="4" data-live>', $rendered);
        $this->assertStringContainsString('href="|page:10|" data-relation="page" data-page="10" data-live>', $rendered);
        $this->assertStringContainsString('href="|page:6|" rel="next" data-relation="next" data-page="6" data-live>', $rendered);
        $this->assertSame(substr_count($rendered, 'href='), substr_count($rendered, ' data-live>'), 'Every link has the attributes');
        $this->assertStringNotContainsString('data-hidden', $rendered);
        $this->assertStringNotContainsString('data-cursor', $rendered);
    }

    /**
     * @param \Closure(): (Template&SequentialTemplateInterface) $template
     */
    #[DataProvider('dataTemplates')]
    public function testTheAttributesAreRenderedOnTheSequentialLinks(\Closure $template): void
    {
        $rendered = (new SequentialView($template()))->render($this->createCursorPager(), $this->createPositionRouteGenerator());

        $this->assertStringContainsString('href="|Previous:1|" rel="prev" data-relation="previous" data-cursor="Previous:1" data-live>', $rendered);
        $this->assertStringContainsString('href="|Next:3|" rel="next" data-relation="next" data-cursor="Next:3" data-live>', $rendered);
        $this->assertStringNotContainsString('data-page', $rendered);
    }

    public function testTheAttributeValuesAreEscaped(): void
    {
        $template = $this->createTemplateWithAttributes(['data-value' => '"<&>\'']);

        $this->assertStringContainsString(
            'data-value="&quot;&lt;&amp;&gt;&#039;">',
            (new DefaultView($template))->render($this->createPagerfanta(), $this->createPositionRouteGenerator()),
        );
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function dataInvalidAttributeNames(): \Generator
    {
        yield 'empty' => [''];
        yield 'whitespace' => ['data value'];
        yield 'quote' => ['"onclick'];
        yield 'closing tag' => ['data-value>'];
        yield 'leading digit' => ['1data'];
        yield 'href' => ['href'];
        yield 'uppercase href' => ['HREF'];
        yield 'rel' => ['rel'];
        yield 'class' => ['class'];
    }

    #[DataProvider('dataInvalidAttributeNames')]
    public function testInvalidAttributeNamesAreRejected(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new DefaultView($this->createTemplateWithAttributes([$name => 'value'])))->render($this->createPagerfanta(), $this->createPositionRouteGenerator());
    }

    public function testTheOuterLinkIsKeptWhenALinkIsRenderedWhileRenderingAnotherLink(): void
    {
        $template = new class extends DefaultTemplate {
            use AddsTestLinkAttributes;

            public function previousEnabled(int $page): string
            {
                return $this->renderPageLink('previous', $page, fn (): string => $this->pageWithText($page, 'Back', 'prev'));
            }
        };

        $this->assertStringContainsString(
            'href="|page:4|" rel="prev" data-relation="previous" data-page="4" data-live>Back</a>',
            (new DefaultView($template))->render($this->createPagerfanta(), $this->createPositionRouteGenerator()),
        );
    }

    public function testTheAttributesAreNotRenderedOutsideOfALink(): void
    {
        $template = new class extends DefaultTemplate {
            use AddsTestLinkAttributes;

            public function renderAttributesOutsideOfALink(): string
            {
                return $this->renderLinkAttributes();
            }
        };

        $template->setRouteGenerator(static fn (int $page): string => '|'.$page.'|');

        $this->assertStringContainsString('data-live', $template->page(1));
        $this->assertSame('', $template->renderAttributesOutsideOfALink());
    }

    public function testALinkToAPageLessThan1IsRenderedWithoutTheAttributes(): void
    {
        $template = new class extends DefaultTemplate {
            use AddsTestLinkAttributes;
        };

        $template->setRouteGenerator(static fn (int $page): string => '|'.$page.'|');

        $this->assertSame('<a class="pagination__item" href="|0|">0</a>', $template->page(0));
    }

    public function testATemplateOverridingTheLinkMarkupIsRenderedWithoutTheAttributes(): void
    {
        $template = new class extends TwitterBootstrap4Template {
            use AddsTestLinkAttributes;

            protected function linkLi(string $class, string $href, $text, ?string $rel = null): string
            {
                return \sprintf('<li><a href="%s">%s</a></li>', $href, $text);
            }
        };

        $rendered = (new DefaultView($template))->render($this->createPagerfanta(), $this->createPositionRouteGenerator());

        $this->assertStringContainsString('<li><a href="|page:4|">Previous</a></li>', $rendered);
        $this->assertStringNotContainsString('data-', $rendered);
    }

    /**
     * @param array<string, string|int|bool|null> $attributes
     */
    private function createTemplateWithAttributes(array $attributes): Template
    {
        return new class($attributes) extends DefaultTemplate {
            /**
             * @param array<string, string|int|bool|null> $attributes
             */
            public function __construct(private readonly array $attributes)
            {
                parent::__construct();
            }

            protected function linkAttributes(string $relation, Position $position): array
            {
                return $this->attributes;
            }
        };
    }

    /**
     * @return Pagerfanta<int>
     */
    private function createPagerfanta(): Pagerfanta
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(new ArrayAdapter(range(1, 100)), 5, 10);
    }

    /**
     * @return CursorPagerfanta<int>
     */
    private function createCursorPager(): CursorPagerfanta
    {
        $adapter = new CallbackCursorAdapter(
            static fn (?Cursor $cursor, int $limit): CursorSlice => new CursorSlice(
                [2],
                new Cursor(['id' => 1], Direction::Previous),
                new Cursor(['id' => 3]),
            ),
            true,
        );

        return new CursorPagerfanta($adapter, 1, new CursorPosition(new Cursor(['id' => 1])));
    }

    private function createPositionRouteGenerator(): PositionRouteGeneratorDecorator
    {
        return new PositionRouteGeneratorDecorator(static fn (Position $position): string => match (true) {
            $position instanceof CursorPosition => \sprintf('|%s:%s|', $position->cursor->direction->name, $position->cursor->fields['id']),
            $position instanceof PagePosition => \sprintf('|page:%d|', $position->page),
            default => throw new \UnexpectedValueException('Unexpected position'),
        });
    }
}
