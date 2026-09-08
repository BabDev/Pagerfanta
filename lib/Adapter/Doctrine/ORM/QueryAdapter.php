<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\OffsetPaginator;
use Doctrine\ORM\Tools\Pagination\Window;
use Doctrine\ORM\Tools\Pagination\WindowPage;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine ORM Query or QueryBuilder.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class QueryAdapter implements AdapterInterface
{
    /**
     * @var OffsetPaginator<T>
     */
    private readonly OffsetPaginator $paginator;

    /**
     * @var WindowPage<T>|null
     */
    private ?WindowPage $page = null;

    /**
     * @param bool      $fetchJoinCollection Whether the query joins a collection (true by default)
     * @param bool|null $useOutputWalkers    Flag indicating whether output walkers are used in the paginator
     */
    public function __construct(
        private readonly Query|QueryBuilder $query,
        bool $fetchJoinCollection = true,
        ?bool $useOutputWalkers = null,
    ) {
        $this->paginator = new OffsetPaginator($fetchJoinCollection, $useOutputWalkers);
    }

    /**
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        /*
         * A slice may not have been fetched yet, so there isn't always a window to reuse.
         * In those cases, we probe with a minimum-sized window to get the total count,
         * with the page later replaced by getSlice().
         */
        $this->page ??= $this->paginator->paginate($this->query, new Window(0, 1));

        return $this->page->getTotalCount();
    }

    /**
     * @param int<0, max> $offset
     * @param int<0, max> $length
     *
     * @return \Traversable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $this->page = $this->paginator->paginate($this->query, new Window($offset, $length));

        return $this->page->getIterator();
    }
}
