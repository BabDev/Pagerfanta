# Templates

Pagerfanta defines `Pagerfanta\View\Template\TemplateInterface` which is an abstraction layer for building the markup for different sections of a pagination list.

The interface requires several methods to be implemented:

- `container`: Generates the wrapping container for the pagination list
- `page`: Generates the markup for a single page in the pagination list 
- `pageWithText`: Generates the markup for a single page with the specified text label 
- `previousDisabled`: Generates the markup for the previous page button in the disabled state 
- `previousEnabled`: Generates the markup for the previous page button in the enabled state 
- `nextDisabled`: Generates the markup for the next page button in the disabled state 
- `nextEnabled`: Generates the markup for the next page button in the enabled state 
- `first`: Generates the markup for the first page button 
- `last`: Generates the markup for the last page button 
- `current`: Generates the markup for the current page button 
- `separator`: Generates the markup for a separator button, used to represent a break in a list of pages (i.e. 1, 2, ..., 6, 7) 

```php
<?php

namespace Pagerfanta\View\Template;

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
     * @param string|null $rel An optional relation for the item
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
```
