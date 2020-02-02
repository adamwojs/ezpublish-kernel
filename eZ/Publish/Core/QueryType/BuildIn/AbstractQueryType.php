<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractQueryType extends OptionsResolverBasedQueryType
{
    public const DEFAULT_LIMIT = 25;

    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    public function __construct(Repository $repository, ConfigResolverInterface $configResolver)
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filter' => static function (OptionsResolver $resolver): void {
                $resolver->setDefaults([
                    'content_type' => [],
                    'visible_only' => true,
                ]);

                $resolver->setAllowedTypes('content_type', 'array');
                $resolver->setAllowedTypes('visible_only', 'bool');
            },
            'offset' => 0,
            'limit' => self::DEFAULT_LIMIT,
        ]);

        $resolver->setAllowedTypes('offset', 'int');
        $resolver->setAllowedTypes('limit', 'int');
    }

    abstract protected function getQueryFilter(array $parameters): Criterion;

    protected function doGetQuery(array $parameters): Query
    {
        $criteria = [
            $this->getQueryFilter($parameters),
        ];

        if ($parameters['filter']['visible_only']) {
            $criteria[] = new Visibility(Visibility::VISIBLE);
        }

        if (!empty($parameters['filter']['content_type'])) {
            $criteria[] = new ContentTypeIdentifier($parameters['filter']['content_type']);
        }

        // Limit results to current SiteAccess tree root
        $criteria[] = new Subtree($this->getRootLocationPathString());

        $query = new Query();
        $query->filter = new LogicalAnd($criteria);
        $query->limit = $parameters['limit'];
        $query->offset = $parameters['offset'];

        return $query;
    }

    private function getRootLocationPathString(): string
    {
        $rootLocation = $this->repository->getLocationService()->loadLocation(
            $this->configResolver->getParameter('content.tree_root.location_id')
        );

        return $rootLocation->pathString;
    }
}
