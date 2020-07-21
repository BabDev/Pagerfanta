<?php

namespace Pagerfanta\View\Template;

class SemanticUiTemplate extends Template
{
    /**
     * @var string[]
     */
    protected static $defaultOptions = [
        'prev_message' => '&larr; Previous',
        'next_message' => 'Next &rarr;',
        'dots_message' => '&hellip;',
        'active_suffix' => '',
        'css_container_class' => 'ui stackable fluid pagination menu',
        'css_item_class' => 'item',
        'css_prev_class' => 'prev',
        'css_next_class' => 'next',
        'css_disabled_class' => 'disabled',
        'css_dots_class' => 'disabled',
        'css_active_class' => 'active',
    ];

    public function container(): string
    {
        return sprintf('<div class="%s">%%pages%%</div>',
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
        return $this->pageWithTextAndClass($page, $text, '');
    }

    /**
     * @param int    $page
     * @param string $text
     * @param string $class
     */
    private function pageWithTextAndClass($page, $text, $class): string
    {
        return $this->link($class, $this->generateRoute($page), $text);
    }

    public function previousDisabled(): string
    {
        return $this->div($this->previousDisabledClass(), $this->option('prev_message'));
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
        return $this->pageWithTextAndClass($page, $this->option('prev_message'), $this->option('css_prev_class'));
    }

    public function nextDisabled(): string
    {
        return $this->div($this->nextDisabledClass(), $this->option('next_message'));
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
        return $this->pageWithTextAndClass($page, $this->option('next_message'), $this->option('css_next_class'));
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

        return $this->div($this->option('css_active_class'), $text);
    }

    public function separator(): string
    {
        return $this->div($this->option('css_dots_class'), $this->option('dots_message'));
    }

    /**
     * @param int|string $text
     */
    private function link(string $class, string $href, $text): string
    {
        return sprintf('<a class="%s %s" href="%s">%s</a>', $this->option('css_item_class'), $class, $href, $text);
    }

    /**
     * @param int|string $text
     */
    private function div(string $class, $text): string
    {
        return sprintf('<div class="%s %s">%s</div>', $this->option('css_item_class'), $class, $text);
    }
}
