<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

class SemanticUiTemplate extends Template
{
    protected function getDefaultOptions(): array
    {
        return [
            'prev_message' => '&larr; Previous',
            'next_message' => 'Next &rarr;',
            'dots_message' => '&hellip;',
            'active_suffix' => '',
            'css_active_class' => 'active',
            'css_container_class' => 'ui stackable fluid pagination menu',
            'css_disabled_class' => 'disabled',
            'css_dots_class' => 'disabled',
            'css_item_class' => 'item',
            'css_prev_class' => 'prev',
            'css_next_class' => 'next',
            'container_template' => '<div class="%s">%%pages%%</div>',
            'rel_previous' => 'prev',
            'rel_next' => 'next',
        ];
    }

    public function container(): string
    {
        return sprintf(
            $this->option('container_template'),
            $this->option('css_container_class')
        );
    }

    public function page(int $page): string
    {
        return $this->pageWithText($page, (string) $page);
    }

    public function pageWithText(int $page, string $text, ?string $rel = null): string
    {
        return $this->pageWithTextAndClass($page, $text, '', $rel);
    }

    private function pageWithTextAndClass(int $page, string $text, string $class, ?string $rel = null): string
    {
        return $this->link($class, $this->generateRoute($page), $text, $rel);
    }

    public function previousDisabled(): string
    {
        return $this->div($this->previousDisabledClass(), $this->option('prev_message'));
    }

    private function previousDisabledClass(): string
    {
        return $this->option('css_prev_class').' '.$this->option('css_disabled_class');
    }

    public function previousEnabled(int $page): string
    {
        return $this->pageWithTextAndClass($page, $this->option('prev_message'), $this->option('css_prev_class'), $this->option('rel_previous'));
    }

    public function nextDisabled(): string
    {
        return $this->div($this->nextDisabledClass(), $this->option('next_message'));
    }

    private function nextDisabledClass(): string
    {
        return $this->option('css_next_class').' '.$this->option('css_disabled_class');
    }

    public function nextEnabled(int $page): string
    {
        return $this->pageWithTextAndClass($page, $this->option('next_message'), $this->option('css_next_class'), $this->option('rel_next'));
    }

    public function first(): string
    {
        return $this->page(1);
    }

    public function last(int $page): string
    {
        return $this->page($page);
    }

    public function current(int $page): string
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
    private function link(string $class, string $href, $text, ?string $rel = null): string
    {
        $rel = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf('<a class="%s %s" href="%s"%s>%s</a>', $this->option('css_item_class'), $class, $href, $rel, $text);
    }

    /**
     * @param int|string $text
     */
    private function div(string $class, $text): string
    {
        return sprintf('<div class="%s %s">%s</div>', $this->option('css_item_class'), $class, $text);
    }
}
