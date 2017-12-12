<?php

namespace eZ\Publish\API\Repository\Values\URL;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;

class SearchResult extends ValueObject implements \IteratorAggregate
{
    /**
     * @var int
     */
    public $totalCount = 0;

    /**
     * @var \eZ\Publish\API\Repository\Values\URL\URL[]
     */
    public $items = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}
