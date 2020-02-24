<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SpecParser;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token;

final class FieldParser implements SortClauseParser
{
    private const SUPPORTED_CLAUSE_NAME = 'field';

    public function parse(SpecParser $parser, string $name): SortClause
    {
        $args = [];
        $args[] = $parser->match(Token::TYPE_ID)->getValue();
        $parser->match(Token::TYPE_DOT);
        $args[] = $parser->match(Token::TYPE_ID)->getValue();
        $args[] = $parser->sortDirection();

        return new Field(...$args);
    }

    public function supports(string $name): bool
    {
        return $name === self::SUPPORTED_CLAUSE_NAME;
    }
}
