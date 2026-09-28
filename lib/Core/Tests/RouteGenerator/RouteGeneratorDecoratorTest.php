<?php declare(strict_types=1);

namespace Pagerfanta\Tests\RouteGenerator;

use Pagerfanta\RouteGenerator\RouteGeneratorDecorator;
use Pagerfanta\Tests\CapturesDeprecations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('legacy')]
final class RouteGeneratorDecoratorTest extends TestCase
{
    use CapturesDeprecations;

    public function testTheDecoratorIsDeprecated(): void
    {
        $deprecations = $this->captureDeprecations(function (): void {
            $generator = new RouteGeneratorDecorator(static fn (int $page): string => '/posts?page='.$page);

            $this->assertSame('/posts?page=2', $generator(2));
            $this->assertSame('/posts?page=3', $generator->route(3));
        });

        $this->assertSame(['Since pagerfanta/core 4.10: The "Pagerfanta\\RouteGenerator\\RouteGeneratorDecorator" class is deprecated, use "Pagerfanta\\RouteGenerator\\PositionRouteGeneratorDecorator" instead.'], $deprecations);
    }
}
