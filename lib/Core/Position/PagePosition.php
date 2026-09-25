<?php declare(strict_types=1);

namespace Pagerfanta\Position;

use Pagerfanta\Exception\LessThan1CurrentPageException;

/**
 * A position within an offset paginated list, identified by its page number.
 */
final class PagePosition implements Position
{
    /**
     * @param positive-int $page
     *
     * @throws LessThan1CurrentPageException if the page is less than 1
     */
    public function __construct(
        public readonly int $page,
    ) {
        if ($page < 1) {
            throw new LessThan1CurrentPageException();
        }
    }
}
