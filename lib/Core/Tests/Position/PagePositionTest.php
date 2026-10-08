<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Position;

use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Position\PagePosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PagePositionTest extends TestCase
{
    public function testThePageIsExposed(): void
    {
        $this->assertSame(3, (new PagePosition(3))->page);
    }

    /**
     * @return \Generator<string, array{0: int}>
     */
    public static function dataLessThan1(): \Generator
    {
        yield 'zero' => [0];
        yield 'negative number' => [-1];
    }

    #[DataProvider('dataLessThan1')]
    public function testThePageMustBeAtLeast1(int $page): void
    {
        $this->expectException(LessThan1CurrentPageException::class);

        // @phpstan-ignore-next-line argument.type
        new PagePosition($page);
    }
}
