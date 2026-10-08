<?php declare(strict_types=1);

namespace Pagerfanta\Tests\RouteGenerator;

use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorDecorator;
use PHPUnit\Framework\TestCase;

final class PositionRouteGeneratorDecoratorTest extends TestCase
{
    public function testTheDecoratedCallableGeneratesTheRoute(): void
    {
        $generator = new PositionRouteGeneratorDecorator(static fn (Position $position): string => $position instanceof PagePosition ? '/posts?page='.$position->page : '/posts');

        $this->assertSame('/posts?page=2', $generator(new PagePosition(2)));
        $this->assertSame('/posts?page=3', $generator->route(new PagePosition(3)));
    }
}
