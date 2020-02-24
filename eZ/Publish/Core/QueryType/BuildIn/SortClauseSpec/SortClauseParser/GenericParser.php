<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SpecParser;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;

final class GenericParser implements SortClauseParser
{
    /** @var array */
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function parse(SpecParser $parser, string $name): SortClause
    {
        $class = $this->map[$name];

        return new $class($parser->sortDirection());
    }

    public function supports(string $name): bool
    {
        return isset($this->map[$name]);
    }
}
