<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

final class SpecLexer implements SpecLexerInterface
{
    private const K_ASC = 'asc';
    private const K_DESC = 'desc';

    private const ID_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';
    private const FLOAT_PATTERN = '/^-?[0-9]*(\.[0-9]+)?$/';
    private const INT_PATTERN = '/^-?[0-9]+$/';

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token[] */
    private $tokens;

    /** @var int */
    private $position = 0;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token|null */
    private $current;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token|null */
    private $next;

    public function getCurrent(): ?Token
    {
        return $this->current;
    }

    public function getAll(): iterable
    {
        return $this->tokens;
    }

    public function consume(): void
    {
        $this->current = $this->next;
        $this->next = $this->tokens[++$this->position] ?? null;
    }

    public function hasNext(): bool
    {
        return !$this->current->is(Token::TYPE_EOF);
    }

    public function isNextToken(int ...$types): bool
    {
        if ($this->next !== null) {
            foreach ($types as $type) {
                if ($this->next->is($type)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function tokenize(string $input): void
    {
        $this->reset();
        $this->tokens = [];

        $values = preg_split('/\s+/', $input);
        foreach ($values as $value) {
            if ($value === '') {
                continue;
            }

            $this->tokens[] = new Token($this->getTokenType($value), $value);
        }
        $this->tokens[] = new Token(Token::TYPE_EOF, '');

        $this->current = $this->tokens[0] ?? null;
        $this->next = $this->tokens[1] ?? null;
        $this->position = 1;
    }

    private function reset(): void
    {
        $this->position = 0;
        $this->next = null;
        $this->current = null;
    }

    private function getTokenType(string $value): int
    {
        switch ($value) {
            case self::K_ASC:
                return Token::TYPE_ASC;
            case self::K_DESC:
                return Token::TYPE_DESC;
            case '.':
                return Token::TYPE_DOT;
            case ',':
                return Token::TYPE_COMMA;
        }

        if (preg_match(self::INT_PATTERN, $value)) {
            return Token::TYPE_INT;
        }

        if (preg_match(self::FLOAT_PATTERN, $value)) {
            return Token::TYPE_FLOAT;
        }

        if (preg_match(self::ID_PATTERN, $value)) {
            return Token::TYPE_ID;
        }

        return Token::TYPE_NONE;
    }
}
