<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

final class SpecParserFactory
{
    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser */
    private $sortClauseParser;

    public function __construct(SortClauseParser $sortClauseParser)
    {
        $this->sortClauseParser = $sortClauseParser;
    }

    public function create(): SpecParserInterface
    {
        return new SpecParser($this->sortClauseParser);
    }
}
