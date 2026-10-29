<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

use Pagerfanta\Position\PagePosition;
use Pagerfanta\Position\Position;

/**
 * A link rendered by a template, given to the methods rendering the link markup and resolving the link attributes.
 */
final readonly class Link
{
    /**
     * @param LinkRelation $relation The relation of the link to the current page
     * @param Position     $position The position the link points to, numbered page links are given a {@see PagePosition}
     * @param string       $href     The URL of the link
     * @param string       $text     The text of the link, which may contain markup
     * @param string|null  $rel      The "rel" attribute of the link, if any
     */
    public function __construct(
        public LinkRelation $relation,
        public Position $position,
        public string $href,
        public string $text,
        public ?string $rel = null,
    ) {}
}
