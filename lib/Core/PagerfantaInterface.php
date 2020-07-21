<?php

namespace Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;

trigger_deprecation('pagerfanta/pagerfanta', '2.2', 'The "%s" interface is deprecated and will be removed in 3.0. Use the "%s" class instead.', PagerfantaInterface::class, Pagerfanta::class);

/**
 * @method AdapterInterface getAdapter()
 * @method Pagerfanta       setAllowOutOfRangePages(bool $allowOutOfRangePages)
 * @method bool             getAllowOutOfRangePages()
 * @method Pagerfanta       setNormalizeOutOfRangePages(bool $normalizeOutOfRangePages)
 * @method bool             getNormalizeOutOfRangePages()
 * @method Pagerfanta       setMaxPerPage(int $maxPerPage)
 * @method int              getMaxPerPage()
 * @method Pagerfanta       setCurrentPage(int $currentPage)
 * @method int              getCurrentPage()
 * @method iterable         getCurrentPageResults()
 * @method int              getCurrentPageOffsetStart()
 * @method int              getCurrentPageOffsetEnd()
 * @method int              getNbResults()
 * @method int              getNbPages()
 * @method bool             haveToPaginate()
 * @method bool             hasPreviousPage()
 * @method int              getPreviousPage()
 * @method bool             hasNextPage()
 * @method int              getNextPage()
 * @method int              getPageNumberForItemAtPosition(int $position)
 *
 * @deprecated to be removed in 3.0, use the `Pagerfanta\Pagerfanta` class instead
 */
interface PagerfantaInterface
{
}
