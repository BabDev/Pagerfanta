<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\View\Template;

/**
 * @author Pablo Díez <pablodip@gmail.com>
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
     * @param int    $page
     * @param string $text
     *
     * @return string
     */
    public function pageWithText($page, $text);

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
