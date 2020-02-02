<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RelatedToContentQueryType extends AbstractQueryType
{
    public static function getName(): string
    {
        return 'eZ:RelatedTo';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['content']);
        $resolver->setAllowedTypes('content', [Content::class, 'int']);
        $resolver->setNormalizer('content', function (Options $options, $value) {
            if (is_int($value)) {
                $value = $this->repository->getContentService()->loadContent($value);
            }

            return $value;
        });
    }

    protected function getQueryFilter(array $parameters): Criterion
    {
        // TODO: Build proper query
        return new Criterion\MatchNone();
    }
}
