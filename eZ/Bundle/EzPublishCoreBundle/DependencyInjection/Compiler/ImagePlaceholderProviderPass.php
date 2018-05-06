<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImagePlaceholderProviderPass implements CompilerPassInterface
{
    const PROVIDER_SERVICE_ID = 'ezpublish.image_alias.imagine.placeholder_provider';

    public function process(ContainerBuilder $container)
    {
        $provider = $container->getParameter('ezpublish.image_placeholder.provider');
        if ($provider !== null) {
            if ($container->hasDefinition($provider)) {
                $container->setAlias(self::PROVIDER_SERVICE_ID, new Alias($provider));
            } else {
                $container->setDefinition(self::PROVIDER_SERVICE_ID, $this->createProviderDefinition($provider));
            }

            $definition = $container->findDefinition(self::PROVIDER_SERVICE_ID);
            // TODO: Replace with addMethodCall:
            // $providerDefinition->addMethodCall('setOptions', [$providerConfig]);
            $definition->setArgument(1, $container->getParameter('ezpublish.image_placeholder.config'));
        }
    }

    private function createProviderDefinition($providerType)
    {
        return new ChildDefinition($this->getBaseDefinitionId($providerType));
    }

    private function getBaseDefinitionId($providerType)
    {
        return 'ezpublish.image_alias.imagine.placeholder_provider.' . $providerType . '.base';
    }
}
