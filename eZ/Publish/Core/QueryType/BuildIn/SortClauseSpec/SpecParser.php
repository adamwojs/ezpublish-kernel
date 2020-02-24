<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Exception\SyntaxErrorException;

final class SpecParser implements SpecParserInterface
{
    private const DEFAULT_SORT_DIRECTION = Query::SORT_ASC;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SpecLexerInterface */
    private $lexer;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser */
    private $sortClauseParser;

    public function __construct(SortClauseParser $sortClauseParser, SpecLexerInterface $lexer = null)
    {
        if ($lexer === null) {
            $lexer = new SpecLexer();
        }

        $this->sortClauseParser = $sortClauseParser;
        $this->lexer = $lexer;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function parse(string $input): array
    {
        $this->lexer->tokenize($input);
        // Delegate to grammar start rule
        return $this->sortClauseList();
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function sortClauseList(): array
    {
        $sortClauses = [];
        while ($this->lexer->hasNext()) {
            $sortClauses[] = $this->sortClause();
            if ($this->lexer->isNextToken(Token::TYPE_COMMA)) {
                $this->match(Token::TYPE_COMMA);
            }
        }

        return $sortClauses;
    }

    public function sortClause(): SortClause
    {
        $name = $this->match(Token::TYPE_ID)->getValue();

        return $this->sortClauseParser->parse($this, $name);
    }

    public function sortDirection(): string
    {
        if ($this->lexer->isNextToken(Token::TYPE_ASC, Token::TYPE_DESC)) {
            $token = $this->matchAny(Token::TYPE_ASC, Token::TYPE_DESC);

            switch ($token->getType()) {
                case Token::TYPE_ASC:
                    return Query::SORT_ASC;
                case Token::TYPE_DESC:
                    return Query::SORT_DESC;
            }
        }

        return self::DEFAULT_SORT_DIRECTION;
    }

    public function match(int $type): Token
    {
        return $this->matchAny($type);
    }

    public function matchAny(int ...$types): Token
    {
        if ($this->lexer->isNextToken(...$types)) {
            $this->lexer->consume();

            return $this->lexer->getCurrent();
        }

        // TODO: Improve exception message
        throw new SyntaxErrorException('Unexpected token');
    }
}
