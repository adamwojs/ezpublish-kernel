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
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token;

final class RandomParser implements SortClauseParser
{
    private const SUPPORTED_CLAUSE_NAME = 'random';

    public function parse(SpecParser $parser, string $name): SortClause
    {
        $args = [];

        // TODO: Missing access to isNextToken
        if ($this->lexer->isNextToken(Token::TYPE_INT)) {
            $args[] = $parser->match(Token::TYPE_INT)->getValueAsInt();
        } else {
            $args[] = null;
        }

        $args[] = $parser->sortDirection();

        return new SortClause\Random(...$args);
    }

    public function supports(string $name): bool
    {
        return $name === self::SUPPORTED_CLAUSE_NAME;
    }
}
