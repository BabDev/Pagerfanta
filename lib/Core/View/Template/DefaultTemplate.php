<?php

namespace Pagerfanta\View\Template;

class DefaultTemplate extends Template
{
    /**
     * @var string[]
     */
    protected static $defaultOptions = [
        'prev_message' => 'Previous',
        'next_message' => 'Next',
        'css_disabled_class' => 'disabled',
        'css_dots_class' => 'dots',
        'css_current_class' => 'current',
        'dots_text' => '...',
        'container_template' => '<nav>%pages%</nav>',
        'page_template' => '<a href="%href%"%rel%>%text%</a>',
        'span_template' => '<span class="%class%">%text%</span>',
        'rel_previous' => 'prev',
        'rel_next' => 'next',
    ];

    public function container(): string
    {
        return $this->option('container_template');
    }

    /**
     * @param int $page
     */
    public function page($page): string
    {
        return $this->pageWithText($page, (string) $page);
    }

    /**
     * @param int         $page
     * @param string      $text
     * @param string|null $rel
     */
    public function pageWithText($page, $text, $rel = null): string
    {
        $href = $this->generateRoute($page);
        $replace = $rel ? [$href, $text, ' rel="'.$rel.'"'] : [$href, $text, ''];

        return str_replace(['%href%', '%text%', '%rel%'], $replace, $this->option('page_template'));
    }

    public function previousDisabled(): string
    {
        return $this->generateSpan($this->option('css_disabled_class'), $this->option('prev_message'));
    }

    /**
     * @param int $page
     */
    public function previousEnabled($page): string
    {
        return $this->pageWithText($page, $this->option('prev_message'), $this->option('rel_previous'));
    }

    public function nextDisabled(): string
    {
        return $this->generateSpan($this->option('css_disabled_class'), $this->option('next_message'));
    }

    /**
     * @param int $page
     */
    public function nextEnabled($page): string
    {
        return $this->pageWithText($page, $this->option('next_message'), $this->option('rel_next'));
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
        return $this->generateSpan($this->option('css_current_class'), $page);
    }

    public function separator(): string
    {
        return $this->generateSpan($this->option('css_dots_class'), $this->option('dots_text'));
    }

    /**
     * @param int|string $page
     */
    private function generateSpan(string $class, $page): string
    {
        return str_replace(['%class%', '%text%'], [$class, $page], $this->option('span_template'));
    }
}
