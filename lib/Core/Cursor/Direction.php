<?php declare(strict_types=1);

namespace Pagerfanta\Cursor;

/**
 * The direction to paginate in relative to a cursor.
 */
enum Direction
{
    case Next;
    case Previous;
}
