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

    public function testTheRouteGeneratorIsGivenAPagePosition(): void
    {
        $generator = new PageNumberRouteGenerator($this->createPositionRouteGenerator());

        $this->assertSame('/posts?page=2', $generator(2));
        $this->assertSame('/posts?page=3', $generator->route(3));
    }

    public function testAPageLessThan1CannotBeRouted(): void
    {
        $this->expectException(LessThan1CurrentPageException::class);

        (new PageNumberRouteGenerator($this->createPositionRouteGenerator()))->route(0);
    }
}
