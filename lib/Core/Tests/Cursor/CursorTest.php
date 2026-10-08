<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Cursor;

use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CursorTest extends TestCase
{
    public function testTheCursorDefaultsToTheNextDirection(): void
    {
        $this->assertSame(Direction::Next, (new Cursor(['_id' => 'abc']))->direction);
    }

    public function testTheCursorSupportsMultipleFields(): void
    {
        $fields = ['p.createdAt' => '2026-09-25 12:00:00', 'p.rating' => 4.5, 'p.published' => true, 'p.deletedAt' => null, 'p.id' => 42];

        $cursor = new Cursor($fields, Direction::Previous);

        $this->assertSame($fields, $cursor->fields);
        $this->assertSame(Direction::Previous, $cursor->direction);
    }

    public function testTheCursorRequiresAtLeastOneField(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore-next-line argument.type
        new Cursor([]);
    }

    public function testTheCursorFieldsMustBeKeyedByString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore-next-line argument.type
        new Cursor([42]);
    }

    /**
     * @return \Generator<string, array{0: mixed}>
     */
    public static function dataNonScalarValues(): \Generator
    {
        yield 'array' => [[1]];
        yield 'object' => [new \stdClass()];
        yield 'date' => [new \DateTimeImmutable()];
    }

    #[DataProvider('dataNonScalarValues')]
    public function testTheCursorFieldsMustBeScalarOrNull(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cursor(['p.id' => $value]);
    }
}
