<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

class Foundation6Template extends Template
{
    protected function getDefaultOptions(): array
    {
        return [
            'prev_message' => 'Previous',
            'next_message' => 'Next',
            'dots_message' => '',
            'active_suffix' => '',
            'css_active_class' => 'current',
            'css_container_class' => 'pagination',
            'css_disabled_class' => 'disabled',
            'css_dots_class' => 'ellipsis',
            'css_item_class' => '',
            'css_prev_class' => 'pagination-previous',
            'css_next_class' => 'pagination-next',
            'container_template' => '<nav aria-label="Pagination"><ul class="%s">%%pages%%</ul></nav>',
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
        return $this->linkLi($class, $this->generateRoute($page), $text, $rel);
    }

    public function previousDisabled(): string
    {
        return $this->li($this->previousDisabledClass(), $this->option('prev_message'));
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
        return $this->li($this->nextDisabledClass(), $this->option('next_message'));
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

        return $this->li($this->option('css_active_class'), $text);
    }

    public function separator(): string
    {
        $liClass = sprintf(' class="%s"', trim($this->option('css_item_class').' '.$this->option('css_dots_class')));

        return sprintf('<li aria-hidden="true"%s>%s</li>', $liClass, $this->option('dots_message'));
    }

    /**
     * @param int|string $text
     */
    protected function li(string $class, $text): string
    {
        $liClass = sprintf(' class="%s"', trim($this->option('css_item_class').' '.$class));

        return sprintf('<li%s>%s</li>', $liClass, $text);
    }

    /**
     * @param int|string $text
     */
    protected function linkLi(string $class, string $href, $text, ?string $rel = null): string
    {
        $class = trim($this->option('css_item_class').' '.$class);
        $liClass = !empty($class) ? sprintf(' class="%s"', $class) : '';
        $itemRel = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf('<li%s><a href="%s"%s>%s</a></li>', $liClass, $href, $itemRel, $text);
    }
}
