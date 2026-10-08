<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

/**
 * A template which can render numbered page links, as well as the previous and next links of a pager.
 */
interface TemplateInterface extends SequentialTemplateInterface
{
    /**
     * Renders a given page.
     */
    public function page(int $page): string;

    /**
     * Renders a given page with a specified text.
     */
    public function pageWithText(int $page, string $text, ?string $rel = null): string;

    /**
     * Renders the first page.
     */
    public function first(): string;

    /**
     * Renders the last page.
     */
    public function last(int $page): string;

    /**
     * Renders the current page.
     */
    public function current(int $page): string;

    /**
     * Renders the separator between pages.
     */
    public function separator(): string;
}
