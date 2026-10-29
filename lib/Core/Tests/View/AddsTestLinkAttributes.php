<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;

/**
 * Adds attributes describing each link to a template, for testing the link attributes of the templates.
 */
trait AddsTestLinkAttributes
{
    /**
     * @param 'previous'|'page'|'next' $relation
     *
     * @return array<string, string|int|bool|null>
     */
    protected function linkAttributes(string $relation, Position $position): array
    {
        return [
            'data-relation' => $relation,
            'data-page' => $position instanceof PagePosition ? $position->page : null,
            'data-cursor' => $position instanceof CursorPosition ? \sprintf('%s:%s', $position->cursor->direction->name, $position->cursor->fields['id']) : null,
            'data-live' => true,
            'data-hidden' => false,
        ];
    }
}
