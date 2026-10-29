<?php declare(strict_types=1);

namespace Pagerfanta\Tests\View;

use Pagerfanta\Position\CursorPosition;
use Pagerfanta\Position\PagePosition;
use Pagerfanta\View\Template\Link;

/**
 * Adds attributes describing each link to a template, for testing the link attributes of the templates.
 */
trait AddsTestLinkAttributes
{
    /**
     * @return array<string, string|int|bool|null>
     */
    protected function linkAttributes(Link $link): array
    {
        return [
            'data-relation' => $link->relation->value,
            'data-page' => $link->position instanceof PagePosition ? $link->position->page : null,
            'data-cursor' => $link->position instanceof CursorPosition ? \sprintf('%s:%s', $link->position->cursor->direction->name, $link->position->cursor->fields['id']) : null,
            'data-live' => true,
            'data-hidden' => false,
        ];
    }
}
