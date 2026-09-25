<?php declare(strict_types=1);

namespace Pagerfanta\Tests\RouteGenerator;

use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageNumberRouteGenerator;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use PHPUnit\Framework\TestCase;

final class PageNumberRouteGeneratorTest extends TestCase
{
    private function createPositionRouteGenerator(): PositionRouteGeneratorDecorator
    {
        return new PositionRouteGeneratorDecorator(static fn (Position $position): string => $position instanceof PagePosition ? '/posts?page='.$position->page : '/posts');
    }

    public function testAPositionRouteGeneratorIsGivenAPagePosition(): void
    {
        $generator = new PageNumberRouteGenerator($this->createPositionRouteGenerator());

        $this->assertSame('/posts?page=2', $generator(2));
        $this->assertSame('/posts?page=3', $generator->route(3));
    }

    public function testAPositionRouteGeneratorCannotBeGivenAPageLessThan1(): void
    {
        $this->expectException(LessThan1CurrentPageException::class);

        (new PageNumberRouteGenerator($this->createPositionRouteGenerator()))->route(0);
    }

    public function testAPageNumberRouteGeneratorIsGivenThePageAsIs(): void
    {
        $generator = new PageNumberRouteGenerator(static fn (int $page): string => '/posts?page='.$page);

        $this->assertSame('/posts?page=2', $generator(2));
        $this->assertSame('/posts?page=0', $generator->route(0));
    }
}
