<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseArgsParserInterface;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecLexer;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecParser;

/**
 * @internal
 */
final class SortClausesFactory implements SortClausesFactoryInterface
{
    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseArgsParserInterface */
    private $sortClauseArgsParser;

    public function __construct(SortClauseArgsParserInterface $sortClauseArgsParser)
    {
        $this->sortClauseArgsParser = $sortClauseArgsParser;
    }

    /**
     * @throws \eZ\Publish\Core\QueryType\BuildIn\SortSpec\Exception\SyntaxErrorException
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function createFromSpecification(string $specification): array
    {
        $lexer = new SortSpecLexer();
        $lexer->tokenize($specification);

        $parser = new SortSpecParser($this->sortClauseArgsParser, $lexer);

        return $parser->parseSortClausesList();
    }
}
