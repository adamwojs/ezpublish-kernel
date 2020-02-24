<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\MapLocationDistance;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SpecParser;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token;

final class MapDistanceParser implements SortClauseParser
{
    private const SUPPORTED_CLAUSE_NAME = 'map_distance';

    public function parse(SpecParser $parser, string $name): SortClause
    {
        $args = [];
        $args[] = $parser->match(Token::TYPE_ID)->getValue();
        $parser->match(Token::TYPE_DOT);
        $args[] = $parser->match(Token::TYPE_ID)->getValue();
        $args[] = $parser->match(Token::TYPE_FLOAT)->getValueAsFloat();
        $args[] = $parser->match(Token::TYPE_FLOAT)->getValueAsFloat();
        $args[] = $parser->sortDirection();

        return new MapLocationDistance(...$args);
    }

    public function supports(string $name): bool
    {
        return $name === self::SUPPORTED_CLAUSE_NAME;
    }
}
