<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Tests\SpecLexerStub;
use eZ\Publish\Core\Search\Tests\TestCase;

final class SpecParserTest extends TestCase
{
    private const EXAMPLE_SORT_CLAUSE_ID = 'id';

    /**
     * @dataProvider dataProviderForSortDirection
     */
    public function testSortDirection(array $input, string $expectedDirection): void
    {
        $lexer = new SpecLexerStub($input);
        $parser = new SpecParser($this->createMock(SortClauseParser::class), $lexer);

        $this->assertEquals($expectedDirection, $parser->sortDirection());
    }

    public function dataProviderForSortDirection(): iterable
    {
        yield 'asc' => [
            [
                new Token(Token::TYPE_ASC),
                new Token(Token::TYPE_EOF),
            ],
            Query::SORT_ASC
        ];

        yield 'desc' => [
            [
                new Token(Token::TYPE_DESC),
                new Token(Token::TYPE_EOF),
            ],
            Query::SORT_DESC
        ];

        yield 'default' => [
            [
                new Token(Token::TYPE_EOF),
            ],
            Query::SORT_ASC
        ];
    }

    public function testSortClauseList(): void
    {
        $lexer = new SpecLexerStub([
            new Token(Token::TYPE_ID, self::EXAMPLE_SORT_CLAUSE_ID),
            new Token(Token::TYPE_COMMA),
            new Token(Token::TYPE_ID, self::EXAMPLE_SORT_CLAUSE_ID),
            new Token(Token::TYPE_EOF),
        ]);

        $sortClauseParser = $this->createMock(SortClauseParser::class);
        $parser = new SpecParser($sortClauseParser, $lexer);

        $sortClauseA = $this->createMock(SortClause::class);
        $sortClauseB = $this->createMock(SortClause::class);

        $sortClause = $this->createMock(SortClause::class);
        $sortClauseParser
            ->method('parse')
            ->with($parser, self::EXAMPLE_SORT_CLAUSE_ID)
            ->willReturnOnConsecutiveCalls($sortClauseA, $sortClauseB);

        $this->assertEquals(
            [$sortClauseA, $sortClauseB],
            $parser->sortClauseList()
        );
    }

    public function testSortClause(): void
    {
        $lexer = new SpecLexerStub([
            new Token(Token::TYPE_ID, self::EXAMPLE_SORT_CLAUSE_ID),
            new Token(Token::TYPE_EOF),
        ]);

        $sortClauseParser = $this->createMock(SortClauseParser::class);
        $parser = new SpecParser($sortClauseParser, $lexer);

        $sortClause = $this->createMock(SortClause::class);
        $sortClauseParser
            ->expects($this->once())
            ->method('parse')
            ->with($parser, self::EXAMPLE_SORT_CLAUSE_ID)
            ->willReturn($sortClause);

        $this->assertEquals($sortClause, $parser->sortClause());
    }

    public function testMatch(): void
    {
        $token = new Token(Token::TYPE_ID, self::EXAMPLE_SORT_CLAUSE_ID);

        $lexer = $this->createMock(SpecLexerInterface::class);
        $lexer
            ->expects($this->once())
            ->method('isNextToken')
            ->with(Token::TYPE_ID)
            ->willReturn(true);

        $lexer->expects($this->once())->method('consume');
        $lexer->expects($this->once())->method('getCurrent')->willReturn($token);

        $parser = new SpecParser($this->createMock(SortClauseParser::class), $lexer);

        $this->assertEquals($token, $parser->match(Token::TYPE_ID));
    }

    public function testMatchAny(): void
    {
        $token = new Token(Token::TYPE_ASC);

        $lexer = $this->createMock(SpecLexerInterface::class);
        $lexer
            ->expects($this->once())
            ->method('isNextToken')
            ->with(Token::TYPE_ASC, Token::TYPE_DESC)
            ->willReturn(true);

        $lexer->expects($this->once())->method('consume');
        $lexer->expects($this->once())->method('getCurrent')->willReturn($token);

        $parser = new SpecParser($this->createMock(SortClauseParser::class), $lexer);

        $this->assertEquals($token, $parser->matchAny(Token::TYPE_ASC, Token::TYPE_DESC));
    }
}
