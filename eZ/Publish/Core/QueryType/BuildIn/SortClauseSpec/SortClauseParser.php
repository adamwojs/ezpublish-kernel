<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

interface SortClauseParser
{
    public function parse(SpecParser $parser, string $name): SortClause;

    public function supports(string $name): bool;
}
