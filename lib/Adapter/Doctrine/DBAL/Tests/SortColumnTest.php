<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\DBAL\Tests;

use Pagerfanta\Doctrine\DBAL\SortColumn;
use Pagerfanta\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SortColumnTest extends TestCase
{
    public function testTheColumnDefaultsToAscendingOrder(): void
    {
        $column = new SortColumn('p.id');

        $this->assertSame('p.id', $column->expression);
        $this->assertSame('ASC', $column->order);
    }

    public function testTheOrderIsNormalized(): void
    {
        $this->assertSame('DESC', (new SortColumn('p.id', 'desc'))->order);
    }

    public function testTheResultKeyDefaultsToTheColumnName(): void
    {
        $this->assertSame('created_at', (new SortColumn('p.created_at'))->resultKey);
        $this->assertSame('created_at', (new SortColumn('created_at'))->resultKey);
    }

    public function testTheResultKeyCanBeSet(): void
    {
        $this->assertSame('post_created_at', (new SortColumn('p.created_at', 'ASC', 'post_created_at'))->resultKey);
    }

    public function testTheOrderMustBeValid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SortColumn('p.id', 'SIDEWAYS');
    }

    public function testTheExpressionMustNotBeEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore-next-line argument.type
        new SortColumn('');
    }

    public function testTheResultKeyMustNotBeEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SortColumn('p.');
    }
}
