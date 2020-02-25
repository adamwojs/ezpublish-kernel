<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\Exception\UnsupportedSortClauseException;

final class SortClauseArgsParserDispatcher implements SortClauseArgsParserInterface
{
    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseArgsParserInterface[] */
    private $parsers;

    public function __construct(iterable $parsers = [])
    {
        $this->parsers = $parsers;
    }

    public function parse(SortSpecParser $parser, string $name): SortClause
    {
        $sortClauseParser = $this->findParser($name);
        if ($sortClauseParser instanceof SortClauseArgsParserInterface) {
            return $sortClauseParser->parse($parser, $name);
        }

        throw new UnsupportedSortClauseException($name);
    }

    public function supports(string $name): bool
    {
        return $this->findParser($name) instanceof SortClauseArgsParserInterface;
    }

    private function findParser(string $name): ?SortClauseArgsParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($name)) {
                return $parser;
            }
        }

        return null;
    }
}
