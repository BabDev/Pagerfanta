<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

/**
 * The relation of a link rendered by a template to the current page.
 *
 * The values match the relations given to the Twig templates.
 */
enum LinkRelation: string
{
    case Previous = 'previous';

    /**
     * A link to a page by its number, including the first and last page links.
     */
    case Page = 'page';

    case Next = 'next';
}
