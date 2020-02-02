<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\Tests;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\QueryType\BuildIn\ChildrenQueryType;
use eZ\Publish\Core\QueryType\QueryType;

final class ChildrenQueryTypeTest extends AbstractQueryTypeTest
{
    private const EXAMPLE_LOCATION_ID = 54;

    public function dataProviderForGetQuery(): iterable
    {
        yield 'basic' => [
            [
                'location' => self::EXAMPLE_LOCATION_ID,
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new ParentLocationId(self::EXAMPLE_LOCATION_ID),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by visibility' => [
            [
                'location' => self::EXAMPLE_LOCATION_ID,
                'filter' => [
                    'visible_only' => false,
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new ParentLocationId(self::EXAMPLE_LOCATION_ID),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'filter by content type' => [
            [
                'location' => self::EXAMPLE_LOCATION_ID,
                'filter' => [
                    'content_type' => [
                        'article',
                        'blog_post',
                        'folder',
                    ],
                ],
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new ParentLocationId(self::EXAMPLE_LOCATION_ID),
                    new Visibility(Visibility::VISIBLE),
                    new ContentTypeIdentifier([
                        'article',
                        'blog_post',
                        'folder',
                    ]),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
            ]),
        ];

        yield 'limit and offset' => [
            [
                'location' => self::EXAMPLE_LOCATION_ID,
                'limit' => 10,
                'offset' => 100,
            ],
            new Query([
                'filter' => new LogicalAnd([
                    new ParentLocationId(self::EXAMPLE_LOCATION_ID),
                    new Visibility(Visibility::VISIBLE),
                    new Subtree(self::ROOT_LOCATION_PATH_STRING),
                ]),
                'limit' => 10,
                'offset' => 100,
            ]),
        ];
    }

    protected function createQueryType(
        Repository $repository,
        ConfigResolverInterface $configResolver
    ): QueryType {
        return new ChildrenQueryType($repository, $configResolver);
    }

    protected function getExpectedName(): string
    {
        return 'eZ:Children';
    }

    protected function getExpectedSupportedParameters(): array
    {
        return ['filter', 'offset', 'limit', 'location'];
    }
}
