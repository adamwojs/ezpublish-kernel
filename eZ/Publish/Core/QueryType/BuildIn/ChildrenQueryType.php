<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChildrenQueryType extends AbstractQueryType
{
    public static function getName(): string
    {
        return 'eZ:Children';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['location']);
        $resolver->setAllowedTypes('location', [Location::class, 'int']);
        $resolver->setNormalizer('location', static function (Options $options, $value) {
            if ($value instanceof Location) {
                return $value->id;
            }

            return $value;
        });
    }

    protected function getQueryFilter(array $parameters): Criterion
    {
        return new ParentLocationId($parameters['location']);
    }
}
