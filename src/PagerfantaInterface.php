<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta;

trigger_deprecation('pagerfanta/pagerfanta', '1.0', 'The "%s" interface is deprecated and will be removed in 3.0. Use the "%s" class instead.', PagerfantaInterface::class, Pagerfanta::class);

/**
 * @deprecated
 */
interface PagerfantaInterface
{
}
