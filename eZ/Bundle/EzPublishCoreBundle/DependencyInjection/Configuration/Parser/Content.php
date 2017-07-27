<?php

/**
 * File containing the Content class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Configuration parser handling content related config.
 */
class Content extends AbstractParser
{
    const DEFAULT_TTL = [
        '1XX' => 60,
        '2XX' => 60,
        '3XX' => 60,
        '4XX' => 60,
        '5XX' => 60,
    ];

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('content')
                ->info('Content related configuration')
                ->children()
                    ->booleanNode('view_cache')->defaultValue(true)->end()
                    ->booleanNode('ttl_cache')->defaultValue(true)->end()
                    ->append($this->getDefaultTtlNodeDefinition())
                    ->arrayNode('tree_root')
                        ->canBeUnset()
                        ->children()
                            ->integerNode('location_id')
                                ->info("Root locationId for routing and link generation.\nUseful for multisite apps with one repository.")
                                ->isRequired()
                            ->end()
                            ->arrayNode('excluded_uri_prefixes')
                                ->info("URI prefixes that are allowed to be outside the content tree\n(useful for content sharing between multiple sites).\nPrefixes are not case sensitive")
                                ->example(array('/media/images', '/products'))
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (!empty($scopeSettings['content'])) {
            $contextualizer->setContextualParameter('content.view_cache', $currentScope, $scopeSettings['content']['view_cache']);
            $contextualizer->setContextualParameter('content.ttl_cache', $currentScope, $scopeSettings['content']['ttl_cache']);
            $contextualizer->setContextualParameter('content.default_ttl', $currentScope, $scopeSettings['content']['default_ttl']);

            if (isset($scopeSettings['content']['tree_root'])) {
                $contextualizer->setContextualParameter(
                    'content.tree_root.location_id',
                    $currentScope,
                    $scopeSettings['content']['tree_root']['location_id']
                );
                if (isset($scopeSettings['content']['tree_root']['excluded_uri_prefixes'])) {
                    $contextualizer->setContextualParameter(
                        'content.tree_root.excluded_uri_prefixes',
                        $currentScope,
                        $scopeSettings['content']['tree_root']['excluded_uri_prefixes']
                    );
                }
            }
        }
    }

    protected function getDefaultTtlNodeDefinition()
    {
        $node = new ArrayNodeDefinition('default_ttl');
        $node
            ->useAttributeAsKey('code')
            ->prototype('scalar')
                ->info('Default value for TTL cache, in seconds')
            ->end()
            ->defaultValue(self::DEFAULT_TTL)
            ->beforeNormalization()
                ->always(function($value) {
                    if (is_int($value)) {
                        return [
                            '1XX' => $value,
                            '2XX' => $value,
                            '3XX' => $value,
                            '4XX' => $value,
                            '5XX' => $value
                        ];
                    }

                    return $value += self::DEFAULT_TTL;
                })
            ->end()
            ->validate()
                ->ifTrue(function($map) {
                    foreach (array_keys($map) as $statusCode) {
                        // Key is wildcard or valid status code ?
                        if (preg_match('/^([X12345]XX)|([12345]\\d{2})$/', $statusCode) === 0) {
                            return true;
                        }
                    }

                    return false;
                })
                ->thenInvalid('map contains one or more invalid keys %s')
            ->end()
        ;

        return $node;
    }
}
