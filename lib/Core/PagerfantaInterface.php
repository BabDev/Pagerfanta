<?php

namespace Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;

/**
 * @method AdapterInterface    getAdapter()
 * @method PagerfantaInterface setAllowOutOfRangePages(bool $allowOutOfRangePages)
 * @method bool                getAllowOutOfRangePages()
 * @method PagerfantaInterface setNormalizeOutOfRangePages(bool $normalizeOutOfRangePages)
 * @method bool                getNormalizeOutOfRangePages()
 * @method PagerfantaInterface setMaxPerPage(int $maxPerPage)
 * @method int                 getMaxPerPage()
 * @method PagerfantaInterface setCurrentPage(int $currentPage)
 * @method int                 getCurrentPage()
 * @method iterable            getCurrentPageResults()
 * @method int                 getCurrentPageOffsetStart()
 * @method int                 getCurrentPageOffsetEnd()
 * @method int                 getNbResults()
 * @method int                 getNbPages()
 * @method PagerfantaInterface setMaxNbPages(int $maxNbPages)
 * @method PagerfantaInterface resetMaxNbPages()
 * @method bool                haveToPaginate()
 * @method bool                hasPreviousPage()
 * @method int                 getPreviousPage()
 * @method bool                hasNextPage()
 * @method int                 getNextPage()
 * @method int                 getPageNumberForItemAtPosition(int $position)
 */
interface PagerfantaInterface
{
}
