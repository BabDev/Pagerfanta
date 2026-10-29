<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Tests\View;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackCursorAdapter;
use Pagerfanta\Adapter\CursorSlice;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\CursorPagerfanta;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use Pagerfanta\Twig\View\TwigView;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\BlockChain;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

/**
 * Integration tests for adding attributes to the links rendered by the Twig templates.
 */
final class TwigViewLinkAttributesIntegrationTest extends TestCase
{
    private const LINK_ATTRIBUTES_BLOCK = <<<'TWIG'
        {%- block link_attributes %} data-relation="{{ relation }}"
            {%- if page is not null %} data-page="{{ page }}"{% endif -%}
            {%- if position is not null and page is null %} data-cursor="{{ position.cursor.direction.name }}:{{ position.cursor.fields.id }}"{% endif -%}
        {% endblock link_attributes -%}
        TWIG;

    private const THEMES = [
        'default.html.twig',
        'foundation6.html.twig',
        'semantic_ui.html.twig',
        'tailwind.html.twig',
        'twitter_bootstrap.html.twig',
        'twitter_bootstrap3.html.twig',
        'twitter_bootstrap4.html.twig',
        'twitter_bootstrap5.html.twig',
    ];

    private Environment $twig;

    protected function setUp(): void
    {
        $filesystemLoader = new FilesystemLoader();
        $filesystemLoader->addPath(__DIR__.'/../../templates', 'Pagerfanta');

        $templates = ['link_attributes.html.twig' => self::LINK_ATTRIBUTES_BLOCK];

        foreach (self::THEMES as $theme) {
            $templates['extends/'.$theme] = \sprintf("{%%- extends '@Pagerfanta/%s' -%%}\n%s", $theme, self::LINK_ATTRIBUTES_BLOCK);
        }

        // Strict variables ensure the link variables are defined for every link
        $this->twig = new Environment(
            new ChainLoader([new ArrayLoader($templates), $filesystemLoader]),
            ['strict_variables' => true],
        );
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function dataThemes(): \Generator
    {
        foreach (self::THEMES as $theme) {
            yield $theme => [$theme];
        }
    }

    #[DataProvider('dataThemes')]
    public function testTheAttributesAreRenderedOnTheNumberedLinksOfATemplateExtendingTheTheme(string $theme): void
    {
        $rendered = (new TwigView($this->twig))->render($this->createOffsetPager(), $this->createPositionRouteGenerator(), ['template' => 'extends/'.$theme]);

        $this->assertNumberedLinksHaveTheAttributes($rendered);
    }

    #[DataProvider('dataThemes')]
    public function testTheAttributesAreRenderedOnTheNumberedLinksWithAChainOfTemplates(string $theme): void
    {
        $this->skipIfBlockChainIsNotSupported();

        $rendered = (new TwigView($this->twig))->render($this->createOffsetPager(), $this->createPositionRouteGenerator(), ['template' => ['link_attributes.html.twig', '@Pagerfanta/'.$theme]]);

        $this->assertNumberedLinksHaveTheAttributes($rendered);
    }

    #[DataProvider('dataThemes')]
    public function testTheAttributesAreRenderedOnTheLinksOfACursorPager(string $theme): void
    {
        $this->skipIfBlockChainIsNotSupported();

        $rendered = (new TwigView($this->twig))->render($this->createCursorPager(), $this->createPositionRouteGenerator(), ['template' => ['link_attributes.html.twig', '@Pagerfanta/'.$theme]]);

        $this->assertStringContainsString('href="/posts?before=1" rel="prev"', $rendered);
        $this->assertStringContainsString(' data-relation="previous" data-cursor="Previous:1">', $rendered);
        $this->assertStringContainsString(' data-relation="next" data-cursor="Next:3">', $rendered);
        $this->assertSame(2, substr_count($rendered, ' data-relation='));
        $this->assertStringNotContainsString('data-page', $rendered);
    }

    #[DataProvider('dataThemes')]
    public function testTheAttributesAreRenderedOnTheLinksOfAnOffsetPagerRenderedSequentially(string $theme): void
    {
        $rendered = (new TwigView($this->twig))->render($this->createOffsetPager(), $this->createPositionRouteGenerator(), ['template' => 'extends/'.$theme, 'sequential' => true]);

        $this->assertStringContainsString(' data-relation="previous" data-page="1">', $rendered);
        $this->assertStringContainsString(' data-relation="next" data-page="3">', $rendered);
        $this->assertSame(2, substr_count($rendered, ' data-relation='));
        $this->assertStringNotContainsString('data-cursor', $rendered);
    }

    public function testTheAttributeValuesAreEscaped(): void
    {
        $this->twig->setLoader(new ChainLoader([
            new ArrayLoader([
                'escaped.html.twig' => <<<'TWIG'
                    {%- extends '@Pagerfanta/default.html.twig' -%}
                    {%- block link_attributes %} data-value="{{ options.value }}"{% endblock link_attributes -%}
                    TWIG,
            ]),
            $this->twig->getLoader(),
        ]));

        $this->assertStringContainsString(
            ' data-value="&quot;&lt;&amp;&gt;">',
            (new TwigView($this->twig))->render($this->createOffsetPager(), $this->createPositionRouteGenerator(), ['template' => 'escaped.html.twig', 'value' => '"<&>']),
        );
    }

    private function assertNumberedLinksHaveTheAttributes(string $rendered): void
    {
        $this->assertStringContainsString('href="/posts?page=1" rel="prev"', $rendered);
        $this->assertStringContainsString(' data-relation="previous" data-page="1">', $rendered);
        $this->assertStringContainsString(' data-relation="page" data-page="1">', $rendered);
        $this->assertStringContainsString(' data-relation="page" data-page="3">', $rendered);
        $this->assertStringContainsString(' data-relation="next" data-page="3">', $rendered);
        $this->assertSame(substr_count($rendered, 'href='), substr_count($rendered, ' data-relation='), 'Every link has the attributes');
        $this->assertStringNotContainsString('data-cursor', $rendered);
    }

    /**
     * @return Pagerfanta<int>
     */
    private function createOffsetPager(): Pagerfanta
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(new ArrayAdapter(range(1, 30)), 2, 10);
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
            $position instanceof CursorPosition => '/posts?'.(Direction::Next === $position->cursor->direction ? 'after' : 'before').'='.$position->cursor->fields['id'],
            $position instanceof PagePosition => '/posts?page='.$position->page,
            default => throw new \UnexpectedValueException('Unexpected position'),
        });
    }

    private function skipIfBlockChainIsNotSupported(): void
    {
        if (!class_exists(BlockChain::class)) {
            $this->markTestSkipped('This test requires twig/twig 3.29 or later.');
        }
    }
}
