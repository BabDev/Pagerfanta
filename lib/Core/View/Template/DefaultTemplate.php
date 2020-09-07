<?php declare(strict_types=1);

namespace Pagerfanta\View\Template;

class DefaultTemplate extends Template
{
    /**
     * @var string[]
     */
    protected static array $defaultOptions = [
        'prev_message' => 'Previous',
        'next_message' => 'Next',
        'dots_message' => '&hellip;',
        'active_suffix' => '',
        'css_container_class' => '',
        'css_disabled_class' => 'disabled',
        'css_dots_class' => 'dots',
        'css_current_class' => 'current',
        'container_template' => '<nav class="%s">%%pages%%</nav>',
        'page_template' => '<a href="%href%"%rel%>%text%</a>',
        'span_template' => '<span class="%class%">%text%</span>',
        'rel_previous' => 'prev',
        'rel_next' => 'next',
    ];

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
        $href = $this->generateRoute($page);
        $replace = $rel ? [$href, $text, ' rel="'.$rel.'"'] : [$href, $text, ''];

        return str_replace(['%href%', '%text%', '%rel%'], $replace, $this->option('page_template'));
    }

    public function previousDisabled(): string
    {
        return $this->generateSpan($this->option('css_disabled_class'), $this->option('prev_message'));
    }

    public function previousEnabled(int $page): string
    {
        return $this->pageWithText($page, $this->option('prev_message'), $this->option('rel_previous'));
    }

    public function nextDisabled(): string
    {
        return $this->generateSpan($this->option('css_disabled_class'), $this->option('next_message'));
    }

    public function nextEnabled(int $page): string
    {
        return $this->pageWithText($page, $this->option('next_message'), $this->option('rel_next'));
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

        return $this->generateSpan($this->option('css_current_class'), $text);
    }

    public function separator(): string
    {
        return $this->generateSpan($this->option('css_dots_class'), $this->option('dots_message'));
    }

    /**
     * @param int|string $page
     */
    private function generateSpan(string $class, $page): string
    {
        return str_replace(['%class%', '%text%'], [$class, $page], $this->option('span_template'));
    }
}
