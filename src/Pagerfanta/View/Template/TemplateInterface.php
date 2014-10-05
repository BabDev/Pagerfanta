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
    public function container();

    public function page($page);

    public function pageWithText($page, $text);

    public function previousDisabled();

    public function previousEnabled($page);

    public function nextDisabled();

    public function nextEnabled($page);

    public function first();

    public function last($page);

    public function current($page);

    public function separator();
}
