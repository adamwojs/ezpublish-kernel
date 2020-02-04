<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use RuntimeException;

class SortClauseFactory
{
    private const NAMESPACE = 'eZ\Publish\API\Repository\Values\Content\Query\SortClause\\';

    public function create(string $class, ?string $direction, ?array $data = []): SortClause
    {
        if (substr($class, 0, 1) !== '\\') {
            // Build-in sort clause
            $class = self::NAMESPACE . $class;
        }

        if (class_exists($class)) {
            /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause $clause */
            $clause = new $class(...$data);
            $clause->direction = $direction;

            return $clause;
        }

        throw new RuntimeException(sprintf('Non-existing sort clause: %s', $class));
    }
}
