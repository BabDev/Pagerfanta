<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pagerfanta\Adapter;

/**
 * NullAdapter.
 *
 * @author Benjamin Dulau <benjamin.dulau@anonymation.com>
 */
class NullAdapter implements AdapterInterface
{
    private $nbResults;

    /**
     * Constructor.
     *
     * @param integer $nbResults Total item count.
     */
    public function __construct($nbResults = 0)
    {
        $this->nbResults = (int) $nbResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * The following methods are derived from code of the Zend Framework
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     *
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        if ($offset >= $this->nbResults) {
            return array();
        }

        $nullArrayLength = $this->calculateNullArrayLength($offset, $length);

        return $this->createNullArray($nullArrayLength);
    }

    private function calculateNullArrayLength($offset, $length)
    {
        $remainCount = $this->remainCount($offset);
        if ($length > $remainCount) {
            return $remainCount;
        }

        return $length;
    }

    private function remainCount($offset)
    {
        return $this->nbResults - $offset;
    }

    private function createNullArray($length)
    {
        return array_fill(0, $length, null);
    }
}
