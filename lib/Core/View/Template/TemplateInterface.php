<?php

namespace Pagerfanta\View\Template;

/**
 * @method void setRouteGenerator(callable $routeGenerator)
 * @method void setOptions(array $options)
 */
interface TemplateInterface
{
    /**
     * Renders the container for the pagination.
     *
     * The %pages% placeholder will be replaced by the rendering of pages
     *
     * @return string
     */
    public function container();

    /**
     * Renders a given page.
     *
     * @param int $page
     *
     * @return string
     */
    public function page($page);

    /**
     * Renders a given page with a specified text.
     *
     * @param int         $page
     * @param string      $text
     * @param string|null $rel  An optional relation for the text
     *
     * @return string
     */
    public function pageWithText($page, $text/*, ?string $rel = null */);

    /**
     * Renders the disabled state of the previous page.
     *
     * @return string
     */
    public function previousDisabled();

    /**
     * Renders the enabled state of the previous page.
     *
     * @param int $page
     *
     * @return string
     */
    public function previousEnabled($page);

    /**
     * Renders the disabled state of the next page.
     *
     * @return string
     */
    public function nextDisabled();

    /**
     * Renders the enabled state of the next page.
     *
     * @param int $page
     *
     * @return string
     */
    public function nextEnabled($page);

    /**
     * Renders the first page.
     *
     * @return string
     */
    public function first();

    /**
     * Renders the last page.
     *
     * @param int $page
     *
     * @return string
     */
    public function last($page);

    /**
     * Renders the current page.
     *
     * @param int $page
     *
     * @return string
     */
    public function current($page);

    /**
     * Renders the separator between pages.
     *
     * @return string
     */
    public function separator();
}
