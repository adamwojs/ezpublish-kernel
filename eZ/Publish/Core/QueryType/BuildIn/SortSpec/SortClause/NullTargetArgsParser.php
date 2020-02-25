<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecParser;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseArgsParserInterface;

/**
 * Parser for sort clauses which expect only sort direction in constructor parameter.
 */
final class NullTargetArgsParser implements SortClauseArgsParserInterface
{
    /** @var array<string,string> */
    private $classMap;

    public function __construct(array $classMap)
    {
        $this->classMap = $classMap;
    }

    public function parse(SortSpecParser $parser, string $name): SortClause
    {
        $class = $this->classMap[$name];

        return new $class($parser->parseSortDirection());
    }

    public function supports(string $name): bool
    {
        return isset($this->classMap[$name]);
    }
}
