<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

interface SpecLexerInterface
{
    public function getCurrent(): ?Token;

    public function consume(): void;

    public function hasNext(): bool;

    public function isNextToken(int ...$types): bool;

    public function tokenize(string $input): void;
}
