<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Random;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecParser;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseArgsParserInterface;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token;

/**
 * Parser for \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Random sort clause.
 */
final class RandomArgsParser implements SortClauseArgsParserInterface
{
    private const SUPPORTED_CLAUSE_NAME = 'random';

    public function parse(SortSpecParser $parser, string $name): SortClause
    {
        $seed = null;
        if ($parser->isNextToken(Token::TYPE_INT)) {
            $seed = $parser->match(Token::TYPE_INT)->getValueAsInt();
        }

        $sortDirection = $parser->parseSortDirection();

        return new Random($seed, $sortDirection);
    }

    public function supports(string $name): bool
    {
        return $name === self::SUPPORTED_CLAUSE_NAME;
    }
}
