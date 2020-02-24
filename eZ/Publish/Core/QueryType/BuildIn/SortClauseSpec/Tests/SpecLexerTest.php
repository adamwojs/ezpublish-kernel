<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

use PHPUnit\Framework\TestCase;

final class SpecLexerTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTokenize
     */
    public function testTokenize(string $input, iterable $expectedTokens): void
    {
        $lexer = new SpecLexer();
        $lexer->tokenize($input);

        $this->assertEquals($expectedTokens, $lexer->getAll());
    }

    public function dataProviderForTokenize(): iterable
    {
        yield 'keyword: asc' => [
            'asc',
            [
                new Token(Token::TYPE_ASC, 'asc'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'keyword: desc' => [
            'desc',
            [
                new Token(Token::TYPE_DESC, 'desc'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'id: simple' => [
            'foo',
            [
                new Token(Token::TYPE_ID, 'foo'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'id: full alphabet' => [
            'fO0_bA9',
            [
                new Token(Token::TYPE_ID, 'fO0_bA9'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'int: < 0' => [
            '-10',
            [
                new Token(Token::TYPE_INT, '-10'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'int: 0' => [
            '0',
            [
                new Token(Token::TYPE_INT, '0'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'int: > 0' => [
            '100',
            [
                new Token(Token::TYPE_INT, '100'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: 0.0' => [
            '0.0',
            [
                new Token(Token::TYPE_FLOAT, '0.0'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: 0.0 < x < 1.0' => [
            '0.5',
            [
                new Token(Token::TYPE_FLOAT, '0.5'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: -1.0 < x < 0.0' => [
            '-0.25',
            [
                new Token(Token::TYPE_FLOAT, '-0.25'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: > 1.0' => [
            '40.67',
            [
                new Token(Token::TYPE_FLOAT, '40.67'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: < -1.0' => [
            '-25.00',
            [
                new Token(Token::TYPE_FLOAT, '-25.00'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'dot' => [
            '.',
            [
                new Token(Token::TYPE_DOT, '.'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'comma' => [
            ',',
            [
                new Token(Token::TYPE_COMMA, ','),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'unknown' => [
            '???',
            [
                new Token(Token::TYPE_NONE, '???'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'empty input' => [
            '',
            [new Token(Token::TYPE_EOF, '')],
        ];

        yield 'sequence' => [
            'asc desc id 0 0.0 . , ???',
            [
                new Token(Token::TYPE_ASC, 'asc'),
                new Token(Token::TYPE_DESC, 'desc'),
                new Token(Token::TYPE_ID, 'id'),
                new Token(Token::TYPE_INT, '0'),
                new Token(Token::TYPE_FLOAT, '0.0'),
                new Token(Token::TYPE_DOT, '.'),
                new Token(Token::TYPE_COMMA, ','),
                new Token(Token::TYPE_NONE, '???'),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];
    }

    public function testConsume(): void
    {
        $lexer = new SpecLexer();
        $lexer->tokenize('foo bar baz');

        $this->assertEquals(
            new Token(Token::TYPE_ID, 'foo'),
            $lexer->getCurrent()
        );
        $this->assertTrue($lexer->hasNext());
        $this->assertTrue($lexer->isNextToken(Token::TYPE_ID));

        $lexer->consume();

        $this->assertEquals(
            new Token(Token::TYPE_ID, 'bar'),
            $lexer->getCurrent()
        );
        $this->assertTrue($lexer->hasNext());
        $this->assertTrue($lexer->isNextToken(Token::TYPE_ID));

        $lexer->consume();

        $this->assertEquals(
            new Token(Token::TYPE_ID, 'baz'),
            $lexer->getCurrent()
        );
        $this->assertTrue($lexer->hasNext());
        $this->assertTrue($lexer->isNextToken(Token::TYPE_EOF));

        $lexer->consume();

        $this->assertEquals(
            new Token(Token::TYPE_EOF, ''),
            $lexer->getCurrent()
        );
        $this->assertFalse($lexer->hasNext());
    }
}
