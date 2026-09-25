<?php declare(strict_types=1);

namespace Pagerfanta\Tests\Cursor;

use Pagerfanta\Cursor\Base64JsonCursorEncoder;
use Pagerfanta\Cursor\Cursor;
use Pagerfanta\Cursor\Direction;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Base64JsonCursorEncoderTest extends TestCase
{
    private Base64JsonCursorEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new Base64JsonCursorEncoder();
    }

    /**
     * @return \Generator<string, array{0: Cursor}>
     */
    public static function dataCursors(): \Generator
    {
        yield 'single field, next' => [new Cursor(['_id' => '507f1f77bcf86cd799439011'])];
        yield 'single field, previous' => [new Cursor(['p.id' => 42], Direction::Previous)];
        yield 'multiple fields of each scalar type' => [new Cursor(['p.createdAt' => '2026-09-25 12:00:00', 'p.rating' => 4.5, 'p.score' => 1.0, 'p.published' => false, 'p.deletedAt' => null, 'p.id' => 42])];
        yield 'unicode and slashes' => [new Cursor(['a.name' => 'Zoë/Ω?&=+'])];
    }

    #[DataProvider('dataCursors')]
    public function testACursorSurvivesARoundTrip(Cursor $cursor): void
    {
        $this->assertEquals($cursor, $this->encoder->decode($this->encoder->encode($cursor)));
    }

    #[DataProvider('dataCursors')]
    public function testTheEncodedCursorIsUrlSafe(Cursor $cursor): void
    {
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $this->encoder->encode($cursor));
    }

    public function testAFloatWithoutAFractionKeepsItsType(): void
    {
        $decoded = $this->encoder->decode($this->encoder->encode(new Cursor(['p.score' => 1.0])));

        $this->assertEqualsWithDelta(1.0, $decoded->fields['p.score'], PHP_FLOAT_EPSILON);
    }

    /**
     * @return \Generator<string, array{0: Cursor}>
     */
    public static function dataUnencodableCursors(): \Generator
    {
        yield 'invalid UTF-8' => [new Cursor(['p.name' => "\xB1\x31"])];
        yield 'NAN' => [new Cursor(['p.rating' => \NAN])];
        yield 'INF' => [new Cursor(['p.rating' => \INF])];
    }

    #[DataProvider('dataUnencodableCursors')]
    public function testACursorWhichCannotBeEncodedIsRejected(Cursor $cursor): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->encoder->encode($cursor);
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function dataInvalidCursors(): \Generator
    {
        $encode = static fn (string $json): string => rtrim(strtr(base64_encode($json), '+/', '-_'), '=');

        yield 'empty string' => [''];
        yield 'not Base64' => ['not base64!'];
        yield 'not JSON' => [$encode('not json')];
        yield 'JSON scalar' => [$encode('42')];
        yield 'JSON list' => [$encode('[{"p.id":1},"n"]')];
        yield 'missing direction' => [$encode('{"f":{"p.id":1}}')];
        yield 'missing fields' => [$encode('{"d":"n"}')];
        yield 'extra keys' => [$encode('{"f":{"p.id":1},"d":"n","x":1}')];
        yield 'fields not an object' => [$encode('{"f":"p.id","d":"n"}')];
        yield 'empty fields' => [$encode('{"f":{},"d":"n"}')];
        yield 'fields as a list' => [$encode('{"f":[1,2],"d":"n"}')];
        yield 'numeric field key' => [$encode('{"f":{"0":1},"d":"n"}')];
        yield 'nested field value' => [$encode('{"f":{"p.id":[1]},"d":"n"}')];
        yield 'excessive nesting' => [$encode('{"f":{"p.id":[[1]]},"d":"n"}')];
        yield 'unknown direction' => [$encode('{"f":{"p.id":1},"d":"x"}')];
        yield 'direction not a string' => [$encode('{"f":{"p.id":1},"d":true}')];
        yield 'tampered payload' => [substr((new Base64JsonCursorEncoder())->encode(new Cursor(['p.id' => 1])), 0, -3)];
    }

    #[DataProvider('dataInvalidCursors')]
    public function testAnInvalidCursorIsRejected(string $encoded): void
    {
        $this->expectException(InvalidCursorException::class);

        $this->encoder->decode($encoded);
    }
}
