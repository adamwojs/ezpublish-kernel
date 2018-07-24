<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldType;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\AbstractFieldTypeParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Publish\Core\FieldType\ImageAsset\Type as ImageAssetFieldType;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class ImageAsset extends AbstractFieldTypeParser
{
    /**
     * {@inheritdoc}
     */
    public function getFieldTypeIdentifier()
    {
        return ImageAssetFieldType::FIELD_TYPE_IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldTypeSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('content_type_identifier')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('content_field_identifier')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('name_field_identifier')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('parent_location_id')
                ->isRequired()
                ->cannotBeEmpty()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        $fieldTypeIdentifier = $this->getFieldTypeIdentifier();

        if (isset($scopeSettings['fieldtypes'][$fieldTypeIdentifier])) {
            foreach ($scopeSettings['fieldtypes'][$fieldTypeIdentifier] as $key => $value) {
                $contextualizer->setContextualParameter(
                    "fieldtypes.{$fieldTypeIdentifier}.{$key}",
                    $currentScope,
                    $scopeSettings['fieldtypes'][$fieldTypeIdentifier][$key]
                );
            }
        }
    }
}
