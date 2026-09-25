<?php declare(strict_types=1);

namespace Pagerfanta\Cursor;

use Pagerfanta\Exception\InvalidCursorException;

/**
 * Converts cursors to and from the string representation used at the URL boundary.
 */
interface CursorEncoderInterface
{
    public function encode(Cursor $cursor): string;

    /**
     * @throws InvalidCursorException if the string cannot be decoded to a valid cursor
     */
    public function decode(string $encoded): Cursor;
}
