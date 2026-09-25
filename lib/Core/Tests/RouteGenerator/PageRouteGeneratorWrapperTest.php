<?php declare(strict_types=1);

namespace Pagerfanta\Tests\RouteGenerator;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;
use Pagerfanta\RouteGenerator\PageRouteGeneratorWrapper;
use Pagerfanta\RouteGenerator\PositionRouteGeneratorInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use PHPUnit\Framework\TestCase;

final class PageRouteGeneratorWrapperTest extends TestCase
{
    public function testAPagePositionIsPassedToTheDecoratedGeneratorAsAPageNumber(): void
    {
        $generator = new PageRouteGeneratorWrapper(static fn (int $page): string => '/posts?page='.$page);

        $this->assertSame('/posts?page=3', $generator(new PagePosition(3)));
    }

    public function testACursorPositionIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $generator = new PageRouteGeneratorWrapper(static fn (int $page): string => '/posts?page='.$page);
        $generator(new CursorPosition(new Cursor(['id' => 1])));
    }

    public function testAPositionRouteGeneratorIsNotWrapped(): void
    {
        $generator = new class implements PositionRouteGeneratorInterface {
            public function __invoke(Position $position): string
            {
                return '/posts';
            }
        };

        $this->assertSame($generator, PageRouteGeneratorWrapper::wrap($generator));
    }

    public function testAPageRouteGeneratorIsWrapped(): void
    {
        $generator = new class implements RouteGeneratorInterface {
            public function __invoke(int $page): string
            {
                return '/posts/page/'.$page;
            }
        };

        $wrapped = PageRouteGeneratorWrapper::wrap($generator);

        $this->assertInstanceOf(PageRouteGeneratorWrapper::class, $wrapped);
        $this->assertSame('/posts/page/2', $wrapped(new PagePosition(2)));
    }

    public function testACallableIsWrappedAsAPageRouteGenerator(): void
    {
        $wrapped = PageRouteGeneratorWrapper::wrap(static fn (int $page): string => '/posts?page='.$page);

        $this->assertSame('/posts?page=5', $wrapped(new PagePosition(5)));
    }
}
