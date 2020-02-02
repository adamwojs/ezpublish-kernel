<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\MapLocationDistance;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GeoLocationQueryType extends AbstractQueryType
{
    public static function getName(): string
    {
        return 'eZ:GeoLocation';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('content_type');
        $resolver->setAllowedTypes('content_type', ['string', ContentType::class]);
        $resolver->setNormalizer('content_type', static function (Options $options, $value) {
            if ($value instanceof ContentType) {
                $value = $value->identifier;
            }

            return $value;
        });

        $resolver->setRequired('field');
        $resolver->setAllowedTypes('field', ['string', Field::class]);
        $resolver->setNormalizer('field', static function (Options $options, $value) {
            if ($value instanceof Field) {
                $value = $value->fieldDefIdentifier;
            }

            return $value;
        });

        $resolver->setRequired('distance');
        $resolver->setAllowedTypes('distance', ['float', 'array']);

        $resolver->setRequired('latitude');
        $resolver->setAllowedTypes('latitude', ['float']);

        $resolver->setRequired('longitude');
        $resolver->setAllowedTypes('longitude', ['float']);

        $resolver->setDefaults([
            'operator' => Operator::LTE,
        ]);
    }

    protected function getQueryFilter(array $parameters): Criterion
    {
        return new LogicalAnd([
            new ContentTypeIdentifier($parameters['content_type']),
            new MapLocationDistance(
                $parameters['field'],
                $parameters['operator'],
                $parameters['distance'],
                $parameters['latitude'],
                $parameters['longitude']
            ),
        ]);
    }
}
