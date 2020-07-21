<?php

namespace Pagerfanta\View\Template;

class TwitterBootstrapTemplate extends Template
{
    /**
     * @var string[]
     */
    protected static $defaultOptions = [
        'prev_message' => '&larr; Previous',
        'next_message' => 'Next &rarr;',
        'dots_message' => '&hellip;',
        'active_suffix' => '',
        'css_container_class' => 'pagination',
        'css_prev_class' => 'prev',
        'css_next_class' => 'next',
        'css_disabled_class' => 'disabled',
        'css_dots_class' => 'disabled',
        'css_active_class' => 'active',
        'rel_previous' => 'prev',
        'rel_next' => 'next',
    ];

    public function container(): string
    {
        return sprintf('<div class="%s"><ul>%%pages%%</ul></div>',
            $this->option('css_container_class')
        );
    }

    /**
     * @param int $page
     */
    public function page($page): string
    {
        return $this->pageWithText($page, (string) $page);
    }

    /**
     * @param int    $page
     * @param string $text
     */
    public function pageWithText($page, $text, ?string $rel = null): string
    {
        return $this->pageWithTextAndClass($page, $text, '', $rel);
    }

    /**
     * @param int    $page
     * @param string $text
     * @param string $class
     */
    private function pageWithTextAndClass($page, $text, $class, ?string $rel = null): string
    {
        return $this->linkLi($class, $this->generateRoute($page), $text, $rel);
    }

    public function previousDisabled(): string
    {
        return $this->spanLi($this->previousDisabledClass(), $this->option('prev_message'));
    }

    private function previousDisabledClass(): string
    {
        return $this->option('css_prev_class').' '.$this->option('css_disabled_class');
    }

    /**
     * @param int $page
     */
    public function previousEnabled($page): string
    {
        return $this->pageWithTextAndClass($page, $this->option('prev_message'), $this->option('css_prev_class'), $this->option('rel_previous'));
    }

    public function nextDisabled()
    {
        return $this->spanLi($this->nextDisabledClass(), $this->option('next_message'));
    }

    private function nextDisabledClass(): string
    {
        return $this->option('css_next_class').' '.$this->option('css_disabled_class');
    }

    /**
     * @param int $page
     */
    public function nextEnabled($page): string
    {
        return $this->pageWithTextAndClass($page, $this->option('next_message'), $this->option('css_next_class'), $this->option('rel_next'));
    }

    public function first(): string
    {
        return $this->page(1);
    }

    /**
     * @param int $page
     */
    public function last($page): string
    {
        return $this->page($page);
    }

    /**
     * @param int $page
     */
    public function current($page): string
    {
        $text = trim($page.' '.$this->option('active_suffix'));

        return $this->spanLi($this->option('css_active_class'), $text);
    }

    public function separator(): string
    {
        return $this->spanLi($this->option('css_dots_class'), $this->option('dots_message'));
    }

    /**
     * @param string      $class
     * @param string      $href
     * @param int|string  $text
     * @param string|null $rel
     */
    protected function linkLi($class, $href, $text, $rel = null): string
    {
        $liClass = $class ? sprintf(' class="%s"', $class) : '';
        $rel = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf('<li%s><a href="%s"%s>%s</a></li>', $liClass, $href, $rel, $text);
    }

    /**
     * @param string     $class
     * @param int|string $text
     */
    protected function spanLi($class, $text): string
    {
        $liClass = $class ? sprintf(' class="%s"', $class) : '';

        return sprintf('<li%s><span>%s</span></li>', $liClass, $text);
    }
}
