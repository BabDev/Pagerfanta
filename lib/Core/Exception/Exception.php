<?php

namespace Pagerfanta\Exception;

trigger_deprecation('pagerfanta/pagerfanta', '2.2', 'The "%s" interface is deprecated and will be removed in 3.0, exceptions should implement "%s" instead.', Exception::class, PagerfantaException::class);

/**
 * @deprecated to be removed in 3.0, exceptions should implement `Pagerfanta\Exception\PagerfantaException` instead
 */
interface Exception extends PagerfantaException
{
}
