<?php declare(strict_types=1);

namespace Pagerfanta\Doctrine\ORM;

use Pagerfanta\Adapter\CountableAdapterInterface;

/**
 * Adapter which calculates cursor based pagination from a Doctrine ORM Query or QueryBuilder,
 * and which can count the total number of results.
 *
 * @template T
 *
 * @extends CursorQueryAdapter<T>
 */
class CountableCursorQueryAdapter extends CursorQueryAdapter implements CountableAdapterInterface
{
    /**
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        return $this->countResults();
    }
}
