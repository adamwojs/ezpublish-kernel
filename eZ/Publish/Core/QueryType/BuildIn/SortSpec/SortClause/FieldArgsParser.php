<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecParser;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseArgsParserInterface;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token;

/**
 * Parser for \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field sort clause.
 */
final class FieldArgsParser implements SortClauseArgsParserInterface
{
    private const SUPPORTED_CLAUSE_NAME = 'field';

    public function parse(SortSpecParser $parser, string $name): SortClause
    {
        $args = [];
        $args[] = $parser->match(Token::TYPE_ID)->getValue();
        $parser->match(Token::TYPE_DOT);
        $args[] = $parser->match(Token::TYPE_ID)->getValue();
        $args[] = $parser->parseSortDirection();

        return new Field(...$args);
    }

    public function supports(string $name): bool
    {
        return $name === self::SUPPORTED_CLAUSE_NAME;
    }
}
