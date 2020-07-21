<?php

namespace Pagerfanta\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PagerfantaExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('pagerfanta', [PagerfantaRuntime::class, 'renderPagerfanta'], ['is_safe' => ['html']]),
            new TwigFunction('pagerfanta_page_url', [PagerfantaRuntime::class, 'getPageUrl']),
        ];
    }
}
