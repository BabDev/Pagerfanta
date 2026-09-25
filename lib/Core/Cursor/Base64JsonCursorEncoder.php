<?php declare(strict_types=1);

namespace Pagerfanta\Cursor;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\InvalidCursorException;

/**
 * Encodes cursors as URL-safe Base64 JSON strings.
 *
 * Encoded cursors are not signed, a client can decode and alter them to paginate from any position allowed by the underlying
 * query. Applications which need to prevent tampering should decorate this encoder with one which signs the payload.
 */
final class Base64JsonCursorEncoder implements CursorEncoderInterface
{
    private const DIRECTION_NEXT = 'n';
    private const DIRECTION_PREVIOUS = 'p';

    /**
     * @throws InvalidArgumentException if the cursor cannot be encoded to JSON
     */
    public function encode(Cursor $cursor): string
    {
        $payload = [
            'f' => $cursor->fields,
            'd' => match ($cursor->direction) {
                Direction::Next => self::DIRECTION_NEXT,
                Direction::Previous => self::DIRECTION_PREVIOUS,
            },
        ];

        try {
            $json = json_encode($payload, \JSON_THROW_ON_ERROR | \JSON_PRESERVE_ZERO_FRACTION | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $exception) {
            throw new InvalidArgumentException(\sprintf('The cursor could not be encoded: %s', $exception->getMessage()), 0, $exception);
        }

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    /**
     * @throws InvalidCursorException if the string cannot be decoded to a valid cursor
     */
    public function decode(string $encoded): Cursor
    {
        $json = base64_decode(strtr($encoded, '-_', '+/'), true);

        if (false === $json || '' === $json) {
            throw new InvalidCursorException('The cursor is not a valid Base64 string.');
        }

        try {
            $payload = json_decode($json, true, 3, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidCursorException('The cursor is not valid JSON.', 0, $exception);
        }

        if (!\is_array($payload) || 2 !== \count($payload) || !isset($payload['f'], $payload['d']) || !\is_array($payload['f'])) {
            throw new InvalidCursorException('The cursor payload is malformed.');
        }

        $direction = match ($payload['d']) {
            self::DIRECTION_NEXT => Direction::Next,
            self::DIRECTION_PREVIOUS => Direction::Previous,
            default => throw new InvalidCursorException('The cursor direction is not valid.'),
        };

        try {
            // @phpstan-ignore-next-line argument.type
            return new Cursor($payload['f'], $direction);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidCursorException(\sprintf('The cursor fields are not valid: %s', $exception->getMessage()), 0, $exception);
        }
    }
}
