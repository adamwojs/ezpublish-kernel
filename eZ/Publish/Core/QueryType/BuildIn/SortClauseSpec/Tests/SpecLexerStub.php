<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Tests;

use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SpecLexerInterface;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token;

final class SpecLexerStub implements SpecLexerInterface
{
    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\Token[] */
    private $tokens;

    /** @var int */
    private $position;

    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
        $this->position = -1;
    }

    public function getCurrent(): ?Token
    {
        return $this->tokens[$this->position];
    }

    public function consume(): void
    {
        $this->position++;
    }

    public function hasNext(): bool
    {
        return $this->position + 1 < count($this->tokens) - 1;
    }

    public function isNextToken(int ...$types): bool
    {
        return in_array($this->tokens[$this->position + 1]->getType(), $types);
    }

    public function tokenize(string $input): void
    {
    }
}
