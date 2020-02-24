<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser;
use eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SpecParser;

final class ParserDispatcher implements SortClauseParser
{
    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec\SortClauseParser[] */
    private $parsers;

    public function __construct(iterable $parsers = [])
    {
        $this->parsers = $parsers;
    }

    public function parse(SpecParser $parser, string $name): SortClause
    {
        $sortClauseParser = $this->findParser($name);
        if ($sortClauseParser instanceof SortClauseParser) {
            return $sortClauseParser->parse($parser, $name);
        }

        throw new NotFoundException(SortClauseParser::class, $name);
    }

    public function supports(string $name): bool
    {
        return $this->findParser($name) instanceof SortClauseParser;
    }

    private function findParser(string $name): ?SortClauseParser
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($name)) {
                return $parser;
            }
        }

        return null;
    }
}
